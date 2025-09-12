<?php

namespace App\Tests\Service;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\Test;
use App\Entity\TestResult;
use App\Repository\CardRepository;
use App\Service\CardService;
use App\Service\TestService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TestServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private CardRepository $cardRepository;
    private CardService $cardService;
    private TestService $testService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cardService = $this->createMock(CardService::class);
        $this->cardRepository = $this->createMock(CardRepository::class);
        $this->testService = new TestService($this->entityManager, $this->cardRepository, $this->cardService);
    }

    /* Tests the store method of TestService.
       Ensures that the test is persisted and flushed. */
    public function testStore(): void{
        $test = $this->createMock(Test::class);
        $this->entityManager->expects($this->once())->method('persist')->with($test);
        $this->entityManager->expects($this->once())->method('flush');
        $this->testService->store($test);
    }

    /* Tests the createTest and generateQuestions methods of TestService.
       Tests that a test is correctly created with the given deck, question types, and number of questions.
       Verifies that was generated wanted number of questions. */
    public function testCreate(): void{
        $deck = $this->createMock(Deck::class);
        $card = $this->createMock(Card::class);
        $card->method('getBackSide')->willReturn('Answer');
        $this->cardRepository->method('getRandomCards')->willReturn([$card]);
        $test = $this->testService->createTest($deck, [1, 2], 1);
        $this->assertEquals($deck, $test->getDeck());
        $this->assertEquals([1, 2], $test->getTypesOfQuestions());
        $this->assertEquals(1, $test->getNumberOfQuestions());
        $this->assertEquals(0, $test->getQurrentQuestion());
        $this->assertEquals(1, $test->getTestResults()->count());
    }

    /* Tests the answer methods of TestService.
       Verifies that user's answer is stored correctly. */
    public function testAnswer(): void{
        $question = new TestResult();
        $answer = 'Test answer';
        $this->testService->answer($question, $answer);
        $this->assertEquals([$answer], $question->getUserAnswer());
    }

    // Tests that the current question index is correctly incremented when moving to the next question in the test.
    public function testToNextQuestion(): void{
        $test = new Test();
        $test->setQurrentQuestion(1);
        $test->setNumberOfQuestions(5);
        $this->testService->toNextQuestion($test);
        $this->assertEquals(2, $test->getQurrentQuestion());
    }

    // Tests that the current question index is correctly decremented when moving to the previous question in the test.
    public function testToPreviousQuestion(): void{
        $test = new Test();
        $test->setQurrentQuestion(1);
        $test->setNumberOfQuestions(5);
        $this->testService->toPreviousQuestion($test);
        $this->assertEquals(0, $test->getQurrentQuestion());
    }

    /* Tests that the test is properly finalized: the finish time is set,
       and the number of correct answers is accurately counted. */
    public function testFinishTest(): void{
        $test = new Test();
        $now = new \DateTimeImmutable();
        $test->setNumberOfQuestions(1);
        $question = new TestResult();
        $question->setCorrectAnswer(['answer']);
        $question->setUserAnswer(['answer']);
        $test->addTestResult($question);
        $correct_answers = $this->testService->finishTest($test, $now);
        $this->assertEquals($now, $test->getFinishedAt());
        $this->assertEquals(1, $correct_answers);
    }

    /* Tests the storeQuestion method of TestService.
       Ensures that the testResult is persisted and flushed. */
    public function testStoreQuestion(): void{
        $testResult = $this->createMock(TestResult::class);
        $this->entityManager->expects($this->once())->method('persist')->with($testResult);
        $this->entityManager->expects($this->once())->method('flush');
        $this->testService->storeQuestion($testResult);
    }

    /* Tests the remove method of TestService.
       Ensures that test and all associated testResults are removed correctly. */
    public function testRemove(): void{
        $test = new Test();
        $testResult = new TestResult();
        $test->addTestResult($testResult);
        $this->entityManager->expects($this->exactly(2))->method('remove')
            ->with($this->logicalOr($test, $testResult));
        $this->testService->remove($test);
    }
}
