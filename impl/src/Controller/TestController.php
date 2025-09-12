<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\AppUser;
use App\Entity\Test;
use App\Form\QuestionType1;
use App\Form\QuestionType2;
use App\Form\QuestionType3;
use App\Form\TestChoiceType;
use App\Service\TestService;
use App\Service\UserService;
use App\Repository\CardRepository;
use App\Voter\DeckVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/decks/deck')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TestController extends AbstractController
{
    public function __construct(
        private TestService    $testService,
        private CardRepository $cardRepository,
        private UserService $userService
    ){}

    /* Displays the form for creating a test based on a given deck. Checks user permission
       and handles form submission. Validates whether the deck has a sufficient number of studied cards
       for the selected question types and quantity. If validation passes, a test is generated and
       the user is redirected to take it. */
    #[Route('/{id}/tests', 'deck_tests', requirements: ['id' => '\d+'])]
    public function all_test(Deck $deck, Request $request): Response{
        if (!$this->isGranted(DeckVoter::CREATE_TEST, $deck)){
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(TestChoiceType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $types = $form->get('types_of_questions')->getData();
            $count_questions = $form->get('number_of_questions')->getData();
            $count_cards = $deck->countOfStudiedCards();
            $count_types = count($types);
            if ($count_cards < 4 ||
                $count_types === 1 && $count_cards < $count_questions ||
                $count_types === 2 && ceil($count_questions / 2) > $count_cards ||
                $count_types === 3 && ceil($count_questions / 3) > $count_cards
            ){
                return $this->render('tests/no_cards.html.twig',[
                    'deck' => $deck,
                ]);
            }
            $test = $this->testService->createTest($deck, $types, $count_questions);
            if ($this->getUser() === $deck->getOwner())
                return $this->redirectToRoute('deck_test', [
                    'id_deck' => $deck->getId(),
                    'id_test' => $test->getId()
                ]);
            $form->setData([]);
        }
        return $this->render('tests/tests.html.twig', [
           'deck' => $deck,
            'form' => $form
        ]);
    }

    /* Handles the rendering and processing of a test question for a specific deck.
       Verifies the user has permission to take the test on the given deck.
       Redirects to results if all questions have been answered.
       Determines the question type and generates the appropriate form.
       Processes the submitted answer and advances to the next question. */
    #[Route('/{id_deck}/tests/{id_test}', 'deck_test', requirements: ['id_deck' => '\d+', 'id_test' => '\d+'])]
    public function test(
        #[MapEntity(class: Deck::class, id: 'id_deck')]
        Deck $deck,
        #[MapEntity(class: Test::class, id: 'id_test')]
        Test $test, Request $request): Response{
        if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck)){
            throw $this->createAccessDeniedException();
        }
        if ($test->getQurrentQuestion() === $test->getNumberOfQuestions()){
            return $this->redirectToRoute('deck_test_results', [
                'id_deck' => $deck->getId(),
                'id_test' => $test->getId()
            ]);
        }
        $question = $test->getActualQuestion();
        $term = null;
        if ($question->getQuestionType() === 1){
            $answers = $this->cardRepository->get4RandomBacks($deck, $question->getCard());
            $form = $this->createForm(QuestionType1::class, null, [
                'answers' => $answers
            ]);
        }
        else if ($question->getQuestionType() === 2){
            if ($question->getCorrectAnswer()[0])
                $term = $question->getCard()->getBackSide();
            else
                $term = $this->cardRepository->getRandomCards($deck, 1, null, $question->getCard())[0]->getBackSide();
            $form = $this->createForm(QuestionType2::class);
        }
        else
            $form = $this->createForm(QuestionType3::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $this->testService->answer($question, $form->get('answer')->getData());
            $this->testService->toNextQuestion($test);
            return $this->redirectToRoute('deck_test', [
                'id_deck' => $deck->getId(),
                'id_test' => $test->getId()
            ]);
        }
        return $this->render('tests/test.html.twig', [
            'test' => $test,
            'question' => $question,
            'answer' => $form,
            'term' => $term
        ]);
    }

    // Handles navigation to the previous question in a test.
    #[Route('/{id_deck}/tests/{id_test}/previous', 'deck_test_previous', requirements: ['id_deck' => '\d+', 'id_test' => '\d+'])]
    public function previous_question(
        #[MapEntity(class: Deck::class, id: 'id_deck')]
        Deck $deck,
        #[MapEntity(class: Test::class, id: 'id_test')]
        Test $test): Response{
        if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck)){
            throw $this->createAccessDeniedException();
        }
        if ($test->getQurrentQuestion() === 0){
            throw new NotFoundHttpException("Žádná předchozí otázka není k dispozici");
        }
        $this->testService->toPreviousQuestion($test);
        return $this->redirectToRoute('deck_test', [
            'id_deck' => $deck->getId(),
            'id_test' => $test->getId()
        ]);
    }

    // Handles navigation to the next question in a test.
    #[Route('/{id_deck}/tests/{id_test}/next', 'deck_test_next', requirements: ['id_deck' => '\d+', 'id_test' => '\d+'])]
    public function next_question(
        #[MapEntity(class: Deck::class, id: 'id_deck')]
        Deck $deck,
        #[MapEntity(class: Test::class, id: 'id_test')]
        Test $test): Response{
        if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck)){
            throw $this->createAccessDeniedException();
        }
        if ($test->getQurrentQuestion() >= $test->getNumberOfQuestions() - 1){
            throw new NotFoundHttpException("Žádná další otázka není k dispozici");
        }
        $this->testService->toNextQuestion($test);
        return $this->redirectToRoute('deck_test', [
            'id_deck' => $deck->getId(),
            'id_test' => $test->getId()
        ]);
    }

    /* Displays the results of a completed test for a specific deck.
       Ensures the user has permission to view the results or finish the test.
       If the test hasn't been marked as finished, finalizes it. Updates user's last activity.
       If the user has an active goal, registers the test and checks for completion. */
    #[Route('/{id_deck}/tests/{id_test}/results', 'deck_test_results', requirements: ['id_deck' => '\d+', 'id_test' => '\d+'])]
    public function show_results(#[MapEntity(class: Deck::class, id: 'id_deck')]
                                 Deck $deck,
                                 #[MapEntity(class: Test::class, id: 'id_test')]
                                 Test $test): Response{
        if (!$this->isGranted(DeckVoter::CREATE_TEST, $deck)){
            throw $this->createAccessDeniedException();
        }
        if (!$test->getFinishedAt()){
            if (!$this->isGranted(DeckVoter::STUDY_TEST, $deck))
                throw $this->createAccessDeniedException();
            $now = new \DateTimeImmutable();
            /** @var AppUser $user */
            $user = $this->getUser();
            $this->userService->setLastActive($user, $now);
            $correct_answers = $this->testService->finishTest($test, $now);
            $goal = $user->getActualGoal();
            if ($goal){
                $this->userService->finishTest($user, $test, $correct_answers);
                if ($goal->isCompleted())
                    return $this->redirectToRoute('completed_goal');
            }
        }
        return $this->render('tests/result.html.twig', [
            'test' => $test,
        ]);
    }
}