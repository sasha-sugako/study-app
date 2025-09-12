<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\Deck;
use App\Entity\Test;
use App\Entity\TestResult;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
class TestService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CardRepository $cardRepository,
        private CardService $cardService
    ) {}

    /* Persists the given test to the database.
       Returns the ID of the stored test. */
    public function store(Test $test): ?int
    {
        $this->manager->persist($test);
        $this->manager->flush();
        return $test->getId();
    }

    /* Creates a new Test object with the given deck, question types, and question count.
       Persists the test to the database and generates the associated questions.
       Returns the created Test. */
    public function createTest(Deck $deck, array $types, int $count_questions): Test{
        $test = new Test();
        $test->setDeck($deck);
        $test->setTypesOfQuestions($types);
        $test->setNumberOfQuestions($count_questions);
        $this->store($test);
        $this->generateQuestions($test);
        return $test;
    }

    /* Generates a list of questions for the given test based on its associated deck and selected question types.
       For each question, a random card and question type are chosen, avoiding duplicates.
       The generated questions are added to the test and persisted. */
    public function generateQuestions(Test $test): void{
        $number = 0;
        $deck = $test->getDeck();
        $question_types = $test->getTypesOfQuestions();
        $used_questions = [];
        while ($number < $test->getNumberOfQuestions()){
            $type = $question_types[array_rand($question_types)];
            $card = $this->cardRepository->getRandomCards($deck, 1, $type, null, $used_questions)[0];
            if (!$test->hasSameQuestion($card, $type)){
                $question = new TestResult();
                $question->setQuestionType($type);
                $question->setTest($test);
                $question->setCard($card);
                $question->setQuestionNumber($number++);
                if ($type === 2){
                    $isCorrect = rand(0, 1) === 1;
                    $question->setCorrectAnswer([$isCorrect]);
                }
                else
                    $question->setCorrectAnswer([$card->getBackSide()]);
                $this->storeQuestion($question);
                $test->addTestResult($question);
                $used_questions[] = $question;
            }
        }
    }

    // Saves the user's answer to the given test question. The answer is stored and the result is persisted.
    public function answer(TestResult $result, $answer): void{
        $result->setUserAnswer([$answer]);
        $this->storeQuestion($result);
    }

    // Moves the test to the next question by incrementing the current question index.
    public function toNextQuestion(Test $test): void{
        $test->setQurrentQuestion($test->getQurrentQuestion()+1);
        $this->store($test);
    }

    // Moves the test to the next question by decrementing the current question index.
    public function toPreviousQuestion(Test $test): void{
        $test->setQurrentQuestion($test->getQurrentQuestion()-1);
        $this->store($test);
    }

    // Persists the given testResult to the database.
    public function storeQuestion(TestResult $question): void
    {
        $this->manager->persist($question);
        $this->manager->flush();
    }

    /* Finishes the test, marks answers as correct or incorrect, updates the test results,
       and adjusts the learning score and next learning time for the cards based on the user's answers. */
    public function finishTest(Test $test, \DateTimeImmutable $time): int{
        $test->setFinishedAt($time);
        $correct_answers = 0;
        foreach($test->getTestResults() as $result){
            if ($result->getCorrectAnswer() === $result->getUserAnswer()){
                $result->setIsCorrect(true);
                $correct_answers++;
            }
            else{
                $card = $result->getCard();
                if ($card->getLearnScore() > 2)
                    $card->setLearnScore(2);
                if ($card->getToLearn() > $time)
                    $card->setToLearn($time);
                $this->cardService->store($card);
                $result->setIsCorrect(false);
            }
            $this->storeQuestion($result);
        }
        $this->store($test);
        return $correct_answers;
    }

    /* Removes the given test and all its related entities (testResults).
       All changes are persisted to the database. */
    public function remove(Test $test): void{
        foreach($test->getTestResults() as $result){
            $this->manager->remove($result);
        }
        $this->manager->remove($test);
        $this->manager->flush();
    }
}