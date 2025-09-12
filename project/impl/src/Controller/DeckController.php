<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\Deck;
use App\Form\AcceptType;
use App\Form\DeckFilterType;
use App\Form\DeckType;
use App\Form\RateType;
use App\Service\DeckService;
use App\Service\ReviewService;
use App\Repository\CategoryRepository;
use App\Repository\DeckRepository;
use App\Repository\NotificationRepository;
use App\Voter\DeckVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/decks')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DeckController extends AbstractController
{
    public function __construct(
        private DeckRepository         $deckRepository,
        private DeckService            $deckService,
        private CategoryRepository     $categoryRepository,
        private ReviewService          $reviewService,
        private NotificationRepository $notificationRepository
    ){}

    /* Displays all public decks to regular users.
       Admin users see all decks, including private ones.
       Also supports filtering of the decks. */
    #[Route('/', name: 'decks')]
    public function all_decks(Request $request): Response{
        $form = $this->createForm(DeckFilterType::class, options: [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isGranted('ROLE_ADMIN'))
                $decks = $this->deckRepository->findByFilter($form->getData(), true);
            else
                $decks = $this->deckRepository->findByFilter($form->getData());
        } else {
            if ($this->isGranted('ROLE_ADMIN'))
                $decks = $this->deckRepository->findAll();
            else
                $decks = $this->deckRepository->findPublic();
        }
        return $this->render('decks/decks.html.twig', [
            'decks' => $decks,
            'filter' => $form
        ]);
    }

    // Displays the user's personal decks.
    #[Route('/my_decks', name: 'my_decks')]
    public function user_decks(): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $decks = $this->deckRepository->findPrivateByUser($user);
        $unread_notifications = $this->notificationRepository->countUnreadForUser($user);
        return $this->render('decks/user_decks.html.twig', [
            'decks' => $decks,
            'unread_notifications' => $unread_notifications
        ]);
    }

    /* Displays the form for creating a new deck or editing an existing one.
       If editing an existing deck, checks if the user has permission to edit it.
       Handles form submission, including adding a new category if specified and saving the deck.
       Redirects to the list of user's decks upon successful form submission. */
    #[Route('/my_decks/create', 'deck_create')]
    #[Route('/my_decks/{id}/edit', 'deck_edit', requirements: ['id' => '\d+'])]
    public function form(?Deck $deck, Request $request): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        if ($deck && !$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(DeckType::class, $deck);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $new_category = $form->get('new_category')->getData();
            if ($new_category && !$this->categoryRepository->findOneByName($new_category)){
                $this->deckService->addCategory($deck, $new_category);
            }
            $this->deckService->store($deck, $user);
            return $this->redirectToRoute('my_decks');
        }
        return $this->render('decks/form.html.twig', [
            'title' => $deck ? 'Upravit kolekci' : 'Nová kolekce',
            'deck' => $deck,
            'form' => $form,
        ]);
    }

    /* Displays a confirmation form to remove a deck.
       Checks if the user has permission to remove the deck.
       Upon successful form submission, deletes the deck and redirects to the list of user's decks. */
    #[Route('/my_decks/{id}/remove', 'deck_remove', requirements: ['id' => '\d+'])]
    public function delete(Deck $deck, Request $request): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(AcceptType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->deckService->remove($deck);
            return $this->redirectToRoute('my_decks');
        }
        return $this->render('decks/accept.html.twig', [
            'title' => 'Odstranění kolekci',
            'akce' => 'odstranit',
            'deck' => $deck,
            'form' => $form,
        ]);
    }

    // Displays the selected deck.
    #[Route('/deck/{id}', 'deck', requirements: ['id' => '\d+'])]
    public function show_deck(Deck $deck): Response{
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        /** @var AppUser $user */
        $user = $this->getUser();
        $unread_notifications = $this->notificationRepository->countUnreadForUser($user);
        return $this->render('decks/deck.html.twig', [
            'deck' => $deck,
            'edit_collection' => $this->isGranted(DeckVoter::EDIT_REMOVE, $deck),
            'unread_notifications' => $unread_notifications
        ]);
    }

    /* Displays a confirmation form to clone a deck.
       Checks if the user has permission to view the deck before allowing the clone.
       Upon successful form submission, clones the deck and redirects to the newly created deck's page. */
    #[Route('/deck/{id}/clone', 'deck_clone', requirements: ['id' => '\d+'])]
    public function clone_deck(Deck $deck, Request $request): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        if (!$this->isGranted(DeckVoter::VIEW, $deck)){
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(AcceptType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $new_deck = $this->deckService->clone($deck, $user);
            return $this->redirectToRoute('deck', ['id' => $new_deck->getId()]);
        }
        return $this->render('decks/accept.html.twig', [
            'title' => 'Duplikovat kolekci',
            'akce' => 'Duplikovat',
            'deck' => $deck,
            'form' => $form,
        ]);
    }

    /* Displays a confirmation form to publish a deck.
       Checks if the user has permission to publish the deck.
       Upon successful form submission, publishes the collection and redirects to the decks list. */
    #[Route('/deck/{id}/publicate', 'deck_publicate', requirements: ['id' => '\d+'])]
    public function publicate(Deck $deck, Request $request): Response{
        if (!$this->isGranted(DeckVoter::EDIT_REMOVE, $deck)){
            throw $this->createAccessDeniedException();
        }
        if ($deck->getParent()){
            throw new \LogicException('Tato kolekce je kopií již existující');
        }
        if (!$deck->isPrivate()){
            throw new \LogicException('Tato kolekce již byla zveřejněna');
        }
        if ($deck->getCards()->count() === 0)
            throw new \LogicException('Tato kolekce neobsahuje kartičky');
        $form = $this->createForm(AcceptType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->deckService->publicate($deck);
            return $this->redirectToRoute('decks');
        }
        return $this->render('decks/accept.html.twig', [
            'title' => 'Publikace kolekci',
            'akce' => 'publikovat',
            'deck' => $deck,
            'form' => $form,
        ]);
    }

    /* Displays a form for creating a review to the deck.
       Checks if the user has permission to review the deck.
       Upon successful form submission, stores the review and redirects to the deck's page. */
    #[Route('/deck/{id}/review', 'deck_review', requirements: ['id' => '\d+'])]
    public function add_review(Deck $deck, Request $request): Response{
        if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck))
            throw $this->createAccessDeniedException();
        if ($deck->countOfStudiedCards() !== $deck->getCards()->count())
            throw new \LogicException('Nemůžete hodnotit kolekci, dokud neprojdete všechny její kartičky.');
        /** @var AppUser $user */
        $user = $this->getUser();
        $form = $this->createForm(RateType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewService->store($form->getData(), $deck, $user);
            return $this->redirectToRoute('deck', ['id' => $deck->getId()]);
        }
        return $this->render('decks/review.html.twig', [
            'deck' => $deck,
            'form' => $form,
        ]);
    }
}