<?php

namespace App\Tests\Service;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\TestResult;
use App\Service\CardService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class CardServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private KernelInterface $kernel;
    private CardService $cardService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->cardService = new CardService($this->entityManager, $this->kernel);
    }

    // Verifies that the create method correctly assigns the given deck to the card.
    public function testCreate(): void{
        $deck = $this->createMock(Deck::class);
        $card = new Card();
        $this->cardService->create($card, $deck);
        $this->assertSame($deck, $card->getDeck());
    }

    /* Tests the store method of CardService.
       Ensures that the card is persisted and flushed. */
    public function testStore(): void{
        $card = $this->createMock(Card::class);
        $this->entityManager->expects($this->once())->method('persist')->with($card);
        $this->entityManager->expects($this->once())->method('flush');
        $this->cardService->store($card);
    }

    /* Tests the remove method of CardService.
    Ensures that removing a card correctly nullifies its association with related test results. */
    public function testRemove(): void{
        $card = new Card();
        $testResult = new TestResult();
        $testResult->setCard($card);
        $card->addTestResult($testResult);
        $this->entityManager->expects($this->once())->method('remove')->with($card);
        $this->cardService->remove($card);
        $this->assertEquals(null, $testResult->getCard());
    }

    /* Tests the clone method of CardService.
       Ensures that when a card is cloned, all information is correctly copied,
       and card is copied to the specified deck. */
    public function testClone(): void{
        $card = $this->createMock(Card::class);
        $deck = $this->createMock(Deck::class);
        $card->method('getFrontSide')->willReturn('Front Side');
        $card->method('getBackSide')->willReturn('Back Side');
        $card->method('getFrontImage')->willReturn('front_image.png');
        $newCard = $this->cardService->clone($card, $deck);
        $this->assertEquals('Front Side', $newCard->getFrontSide());
        $this->assertEquals('Back Side', $newCard->getBackSide());
        $this->assertEquals('front_image.png', $newCard->getFrontImage());
        $this->assertEquals($deck, $newCard->getDeck());
    }

    /* Tests the addImage method of CardService.
       Ensures that the image is correctly set for the front side of the card. */
    public function testAddImage(): void{
        $card = $this->createMock(Card::class);
        $uploadedFile = $this->createMock(UploadedFile::class);
        $file = $this->createMock(File::class);
        $uploadedFile->method('move')->willReturn($file);
        $file->method('getBasename')->willReturn('test_image.jpg');
        $card->expects($this->once())->method('setFrontImage')->with('test_image.jpg');
        $this->cardService->setImage($card, $uploadedFile, 'front');
    }

    /* Tests the removeImage method of CardService.
       Ensures that the image is correctly removed for the front side of the card. */
    public function testRemoveImage(): void{
        $card = new Card();
        $card->setFrontImage('test_image.jpg');
        $this->cardService->removeImage($card, 'front');
        $this->assertEquals(null, $card->getFrontImage());
    }

    /* Tests the learn method to ensure it correctly updates the learning score and
       the repetition date based on different scores.
       Verifies that the appropriate changes are made for each learn score (1, 2, 3, 4). */
    public function testLearn(): void{
        $card = new Card();
        $now = new \DateTimeImmutable();
        $this->cardService->learn($card, 1, $now);
        $this->assertEquals(1, $card->getLearnScore());
        $this->assertEquals($now->modify('+1 minute'), $card->getToLearn());
        $this->cardService->learn($card, 2, $now);
        $this->assertEquals(2, $card->getLearnScore());
        $this->assertEquals($now->modify('+1 hour'), $card->getToLearn());
        $this->cardService->learn($card, 3, $now);
        $this->assertEquals(3, $card->getLearnScore());
        $this->assertEquals($now->modify('+1 day'), $card->getToLearn());
        $this->cardService->learn($card, 4, $now);
        $this->assertEquals(4, $card->getLearnScore());
        $this->assertEquals($now->modify('+2 day'), $card->getToLearn());
    }
}
