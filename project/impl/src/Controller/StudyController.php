<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\AppUser;
use App\Form\StudyFormType;
use App\Service\CardService;
use App\Service\UserService;
use App\Repository\CardRepository;
use App\Voter\DeckVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/decks/deck')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class StudyController extends AbstractController
{
    public function __construct(
        private CardRepository $cardRepository,
        private CardService    $cardService,
        private UserService $userService
    ){}

    /* Handles the study session for a given deck. Verifies access rights and retrieves cards
       to be studied. Maintains study order in session using a shuffled queue of card IDs.
       If the study session is ongoing, presents the next card in line; otherwise, displays a
       message when there are no cards left. When a user submits a study result, it updates
       the card's learning data, tracks the user's progress toward a study goal, and redirects
       accordingly. */
    #[Route('/{id}/study', 'study', requirements: ['id' => '\d+'])]
    public function study(Deck $deck, Request $request, SessionInterface $session): Response{
        if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck)){
            throw $this->createAccessDeniedException();
        }
        $deck_id = $deck->getId();
        $deck_queue = 'study_order_' . $deck_id;
        $order = $session->get($deck_queue, []);
        $all_cards_to_study = $this->cardRepository->findCardsToStudy($deck);
        $cards_ids = array_map(fn ($card) => $card->getId(), $all_cards_to_study);
        if (empty($order)){
            if (empty($all_cards_to_study)){
                return $this->render('study/no_cards.html.twig', [
                    'deck' => $deck
                ]);
            }
            shuffle($cards_ids);
            $session->set($deck_queue, $cards_ids);
            $order = $cards_ids;
        }
        else{
            $new_cards = array_diff($cards_ids, $order);
            shuffle($new_cards);
            $order = array_merge($order, $new_cards);
            $session->set($deck_queue, $order);
        }
        $current_card_id = array_shift($order);
        if (!$current_card_id){
            $session->remove($deck_queue);
            return $this->render('study/no_cards.html.twig', [
                'deck' => $deck
            ]);
        }
        /** @var AppUser $user */
        $user = $this->getUser();
        $card = $this->cardRepository->findOneById($current_card_id);
        $goal = $user->getActualGoal();
        $form = $this->createForm(StudyFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $score = $form->get('learn_score')->getData();
            $now = new \DateTimeImmutable();
            $this->cardService->learn($card, $score, $now);
            $this->userService->setLastActive($user, $now);
            $session->set($deck_queue, $order);
            if ($goal){
                $this->userService->learnCard($user);
                if ($goal->isCompleted()){
                    return $this->redirectToRoute('completed_goal');
                }
            }
            return $this->redirectToRoute('study', ['id' => $deck->getId()]);
        }
        return $this->render('study/show.html.twig', [
            'card' => $card,
            'form' => $form
        ]);
    }
}