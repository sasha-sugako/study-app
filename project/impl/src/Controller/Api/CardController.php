<?php
declare(strict_types=1);

/* This file contains code adapted from the Symfony documentation:
   - Serializer component://symfony.com/doc/current/serializer/.html#deserializing-an-object
   - Validator component: https://symfony.com/doc/current/validation.html
   Used for deserializing JSON data into an object and validating it.
*/

namespace App\Controller\Api;

use App\Entity\Card;
use App\Entity\Deck;
use App\Factory\CardFactory;
use App\Service\CardService;
use App\Repository\DeckRepository;
use App\Resource\CardDetailResource;
use App\Resource\CollectionResource;
use App\Resource\DeckChoiceResource;
use App\Voter\DeckVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/decks/deck')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CardController extends AbstractController
{
    public function __construct(
        private CardFactory         $cardFactory,
        private DeckRepository      $deckRepository,
        private CardService         $cardService,
        private SerializerInterface $serializer,
        private ValidatorInterface  $validator
    ){}

    // Returns a JSON response with details of a specific card.
    #[Route(path: '/{id_deck}/card/{id_card}', name: 'api_card', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ["GET"])]
    public function show_card(#[MapEntity(class: Deck::class, id: 'id_deck')]
                         Deck $deck,
                         #[MapEntity(class: Card::class, id: 'id_card')]
                         Card $card): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        return $this->json($this->cardFactory->show($card));
    }

    // Returns a JSON response with all cards of the specific deck.
    #[Route(path: '/{id}/cards', name: 'api_deck_cards', methods: ['GET'])]
    public function deck_cards(Deck $deck, Request  $request): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        return $this->json(new CollectionResource(
            _self: $this->generateUrl('api_deck', ['id' => $deck->getId()]),
            data: array_map(
                fn (Card $card) => $this->cardFactory->list($card),
                $deck->getCards()->toArray()
            ),
        ));
    }

    /* Handles creating a new card or editing an existing one.
       Ensures the user has permission to modify the deck.
       Deserializes the request content into a CardDetailResource object, validates the card,
       and stores it. Returns the details of the created or updated card in a JSON response. */
    #[Route(path: '/{id_deck}/card', name: 'api_card_create', requirements: ['id_deck' => '\d+'], methods: ['POST'])]
    #[Route(path: '/{id_deck}/card/{id_card}', name: 'api_card_edit', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ['PUT'])]
    public function create(#[MapEntity(class: Deck::class, id: 'id_deck')]
                           Deck $deck,
                           #[MapEntity(class: Card::class, id: 'id_card')]
                           ?Card $card, Request $request): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $resource = $this->serializer->deserialize(
            $request->getContent(),
            CardDetailResource::class,
            'json'
        );
        $card ??= new Card();
        $this->cardFactory->create($resource, $card);
        $violations = $this->validator->validate($card);
        if (count($violations) > 0) {
            return $this->json((string) $violations, 400);
        }
        $this->cardService->create($card, $deck);
        return $this->json($this->cardFactory->show($card));
    }

    /* Handles adding or updating front and/or back images for a specific card in a given deck.
       Ensures the user has permission to modify the deck. Accepts uploaded image files from the request,
       applies them to the card, and returns the updated card data as JSON. */
    #[Route(path: '/{id_deck}/card/{id_card}/image', name: 'api_card_image', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ['POST'])]
    public function addImage(#[MapEntity(class: Deck::class, id: 'id_deck')]
                             Deck $deck,
                             #[MapEntity(class: Card::class, id: 'id_card')]
                             Card $card, Request $request): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        if ($request->files->get('front_image')){
            $front_image = $request->files->get('front_image');
            $this->cardService->setImage($card, $front_image, 'front');
        }
        if ($request->files->get('back_image')){
            $back_image = $request->files->get('back_image');
            $this->cardService->setImage($card, $back_image, 'back');
        }
        $this->cardService->store($card);
        return $this->json($this->cardFactory->show($card));
    }

    /* Handles copying a specific card to another deck.
       Deserializes the target deck ID from the request JSON body, finds the target deck,
       and clones the card into it. Returns a success message upon completion. */
    #[Route(path: '/{id_deck}/card/{id_card}', name: 'api_card_copy', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ["POST"])]
    public function clone(#[MapEntity(class: Deck::class, id: 'id_deck')]
                          Deck $deck,
                          #[MapEntity(class: Card::class, id: 'id_card')]
                          Card $card, Request $request): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        $resource = $this->serializer->deserialize($request->getContent(),
            DeckChoiceResource::class,
            'json');
        $new_deck = $this->deckRepository->findOneById($resource->id);
        $this->cardService->clone($card, $new_deck);
        return $this->json(['message' => 'Zkopirovano'], 200);
    }

    // Handles the removing of a card. Ensures the user has permission to modify the deck.
    #[Route(path: '/{id_deck}/card/{id_card}', name: 'api_card_remove', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ["DELETE"])]
    public function remove(#[MapEntity(class: Deck::class, id: 'id_deck')]
                           Deck $deck,
                           #[MapEntity(class: Card::class, id: 'id_card')]
                           Card $card): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $this->cardService->remove($card);
        return $this->json(['message' => 'Odstraneno'], 200);
    }

    /* Handles the removal of the front image from a specific card.
       Checks if the user has permission to edit the deck. */
    #[Route(path: '/{id_deck}/card/{id_card}/front_image', name: 'api_card_remove_front_image', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ["DELETE"])]
    public function remove_front_image(#[MapEntity(class: Deck::class, id: 'id_deck')]
                           Deck $deck,
                           #[MapEntity(class: Card::class, id: 'id_card')]
                           Card $card): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $this->cardService->removeImage($card, 'front');
        $this->cardService->store($card);
        return $this->json(['message' => 'Odstraneno'], 200);
    }

    /* Handles the removal of the back image from a specific card.
       Checks if the user has permission to edit the deck. */
    #[Route(path: '/{id_deck}/card/{id_card}/back_image', name: 'api_card_remove_back_image', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'], methods: ["DELETE"])]
    public function remove_back_image(#[MapEntity(class: Deck::class, id: 'id_deck')]
                                       Deck $deck,
                                       #[MapEntity(class: Card::class, id: 'id_card')]
                                       Card $card): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $this->cardService->removeImage($card, 'back');
        $this->cardService->store($card);
        return $this->json(['message' => 'Odstraneno'], 200);
    }
}