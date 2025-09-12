<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Deck;
use App\Form\AcceptType;
use App\Form\CardType;
use App\Form\ChoiceDeckType;
use App\Service\CardService;
use App\Voter\DeckVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/decks/deck')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CardController extends AbstractController
{
    public function __construct(
        private CardService $cardService
    ){}

    // Displays the card of the deck.
    #[Route('/{id_deck}/card/{id_card}', name: 'card', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'])]
    public function show(#[MapEntity(class: Deck::class, id: 'id_deck')]
                         Deck $deck,
                         #[MapEntity(class: Card::class, id: 'id_card')]
                         Card $card): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        return $this->render('card/show.html.twig', [
            'card' => $card,
        ]);
    }

    /* Displays the form for creating a new card or editing an existing one.
       Checks if the user has permission to create or edit it.
       Handles image uploads and deletions for the front and back sides of the card.
       Saves the card and redirects to the deck view. */
    #[Route('/{id_deck}/card/create', name: 'card_create', requirements: ['id_deck' => '\d+'])]
    #[Route('/{id_deck}/card/{id_card}/edit', name: 'card_edit', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'])]
    public function form(#[MapEntity(class: Deck::class, id: 'id_deck')]
                         Deck $deck,
                         #[MapEntity(class: Card::class, id: 'id_card')]
                         ?Card $card, Request $request): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $card ??= new Card();
        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $front_image = $form->get('front_image')->getData();
            $back_image = $form->get('back_image')->getData();
            if ($card && $card->getFrontImage()){
                $remove_front_image = $form->get('remove_front_image')->getData();
                if ($remove_front_image) {
                    $this->cardService->removeImage($card, 'front');
                }
            }
            if ($card && $card->getBackImage()){
                $remove_back_image = $form->get('remove_back_image')->getData();
                if ($remove_back_image) {
                    $this->cardService->removeImage($card, 'back');
                }
            }
            if ($front_image)
                $this->cardService->setImage($card, $front_image, 'front');
            if ($back_image)
                $this->cardService->setImage($card, $back_image, 'back');
            $this->cardService->create($card, $deck);
            return $this->redirectToRoute('deck',
                ['id' => $deck->getId()]);
        }
        return $this->render('card/form.html.twig', [
            'title' => $card->getId() ? 'Upravit kartičku' : 'Nová kartička',
            'deck' => $deck,
            'card' => $card,
            'form' => $form,
        ]);
    }

    /* Displays a confirmation form to remove a card.
       Checks if the user has permission to remove the card.
       Upon successful form submission, deletes the card and redirects to the deck view. */
    #[Route('/{id_deck}/card/{id_card}/remove', name: 'card_remove', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'])]
    public function remove(#[MapEntity(class: Deck::class, id: 'id_deck')]
                         Deck $deck,
                         #[MapEntity(class: Card::class, id: 'id_card')]
                         Card $card, Request $request): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(AcceptType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->cardService->remove($card);
            return $this->redirectToRoute('deck', ['id' => $deck->getId()]);
        }
        return $this->render('card/accept.html.twig', [
            'title' => 'Odstranění kartički',
            'card' => $card,
            'form' => $form,
        ]);
    }

    /* Handles copying an existing card to another deck chosen by the user.
       Displays a form to select a target deck and, upon submission, clones the card into the selected deck.
       Redirects to the target deck view after copying. */
    #[Route('/{id_deck}/card/{id_card}/copy', 'card_copy', requirements: ['id_deck' => '\d+', 'id_card' => '\d+'])]
    public function copy_card(#[MapEntity(class: Deck::class, id: 'id_deck')]
                                Deck $deck,
                                #[MapEntity(class: Card::class, id: 'id_card')]
                                Card $card, Request $request): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        $form = $this->createForm(ChoiceDeckType::class, null, [
                'currentUser' => $user,
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $another_deck = $form->get('deck_choice')->getData();
            $this->cardService->clone($card, $another_deck);
            return $this->redirectToRoute('deck', ['id' => $another_deck->getId()]);
        }

        return $this->render('card/copy_card.html.twig', [
            'title' => 'Zkopírovat kartičku',
            'form' => $form,
            'card' => $card,
        ]);
    }
}