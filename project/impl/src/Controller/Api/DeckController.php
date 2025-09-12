<?php
declare(strict_types=1);

/* This file contains code adapted from the Symfony documentation:
   - Serializer component://symfony.com/doc/current/serializer/.html#deserializing-an-object
   - Validator component: https://symfony.com/doc/current/validation.html
   Used for deserializing JSON data into an object and validating it.
*/

namespace App\Controller\Api;

use App\Entity\AppUser;
use App\Entity\Deck;
use App\Factory\DeckFactory;
use App\Service\DeckService;
use App\Service\ReviewService;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Resource\CollectionResource;
use App\Resource\DeckDetailResource;
use App\Resource\ReviewResource;
use App\Voter\DeckVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/decks')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DeckController extends AbstractController
{
    public function __construct(
        private DeckService     $deckService,
        private DeckFactory     $deckFactory,
        private DeckRepository  $deckRepository,
        private CardRepository  $cardRepository,
        private ReviewService $reviewService
    ){}

    /* Returns a JSON response with the user's personal decks for regular users,
       or all collections for admins. */
    #[Route(path: '', name: 'api_all_decks', methods: ['GET'])]
    public function all_decks(Request $request): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $decks = $this->deckRepository->findByUser($user);
        return $this->json(new CollectionResource(
            _self: $this->generateUrl('api_all_decks'),
            data: array_map(
                fn (Deck $deck) => $this->deckFactory->list($deck),
                $decks
            ),
        ));
    }

    // Returns a JSON response with details of a specific deck.
    #[Route(path: '/deck/{id}', name: 'api_deck', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show_deck(Deck $deck): Response
    {
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        return $this->json($this->deckFactory->show($deck));
    }

    /* Handles creating a new deck or updating an existing one.
       If editing an existing deck, checks if the user has permission to edit it.
       Deserializes the request content into a DeckDetailResource object, validates the deck,
       and stores it. Returns the details of the created or updated deck in a JSON response. */
    #[Route(path: '/my_decks', name: 'api_deck_create', methods: ['POST'])]
    #[Route(path: '/my_decks/{id}', name: 'api_deck_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function create(?Deck $deck, Request $request,
                           SerializerInterface $serializer, ValidatorInterface $validator): Response{
        if ($deck && !$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        /** @var AppUser $user */
        $user = $this->getUser();
        $resource = $serializer->deserialize(
            $request->getContent(),
            DeckDetailResource::class,
            'json'
        );
        if (! $deck){
            $deck = new Deck();
        }
        $this->deckFactory->create($resource, $deck, $user);
        $violations = $validator->validate($deck);
        if (count($violations) > 0) {
            return $this->json((string) $violations, 400);
        }
        $this->deckService->store($deck);
        return $this->json($this->deckFactory->show($deck));
    }

    /* Handles the duplication of a deck.
       Creates a new deck based on the existing one and
       returns the details of the newly created deck in a JSON response.*/
    #[Route(path: '/deck/{id}', name: 'api_deck_clone', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function duplicate(Deck $deck): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        /** @var AppUser $user */
        $user = $this->getUser();
        $new_deck = $this->deckService->clone($deck, $user);
        return $this->json($this->deckFactory->show($new_deck));
    }

    // Handles the removing of a deck. Checks if the user has permission to remove it.
    #[Route(path: '/my_decks/{id}', name: 'api_deck_remove', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function remove(Deck $deck): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $this->deckService->remove($deck);
        return $this->json(['message' => 'Odstraneno'], 200);
    }

    // Handles the publishing of a deck. Checks if the user has permission to publish it.
    #[Route(path: '/my_decks/deck/{id}', name: 'api_deck_publicate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function publicate(Deck $deck): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        if ($deck->getParent()){
            throw new \LogicException('Tato kolekce je kopií již existující');
        }
        if (!$deck->isPrivate()){
            throw new \LogicException('Tato kolekce již byla zveřejněna');
        }
        $this->deckService->publicate($deck);
        return $this->json(['message' => 'Publikovano'], 200);
    }

    /* Handles submitting a review for a deck.
       Ensures the user has permission to review it.
       Deserializes the request content into a ReviewResource object, validates the review,
       and stores it. Returns the updated deck details in a JSON response. */
    #[Route(path: '/deck/{id}/review', name: 'api_deck_review', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add_review(Deck $deck, Request $request,
                               SerializerInterface $serializer, ValidatorInterface $validator): Response{
        if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck))
            throw $this->createAccessDeniedException();
        if ($this->cardRepository->countOfStudied($deck) !== $deck->getCards()->count())
            throw new \LogicException('Nemůžete hodnotit kolekci, dokud neprojdete všechny její kartičky.');
        /** @var AppUser $user */
        $user = $this->getUser();
        $resource = $serializer->deserialize(
            $request->getContent(),
            ReviewResource::class,
            'json'
        );
        $review = $this->deckFactory->add_review($resource, $deck, $user);
        $violations = $validator->validate($review);
        if (count($violations) > 0) {
            return $this->json((string) $violations, 400);
        }
        $this->reviewService->store($review, $deck, $user);
        return $this->json($this->deckFactory->show($deck));
    }
}