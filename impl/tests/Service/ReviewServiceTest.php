<?php

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\Deck;
use App\Entity\Review;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ReviewServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ReviewService $reviewService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void{
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->reviewService = new ReviewService($this->entityManager);
    }

    /* Tests the store method of ReviewService.
       Ensures that the review is persisted and flushed.
       Verifies that the persist and flush methods are called with the appropriate review instance. */
    public function testStore(): void{
        $user = $this->createMock(AppUser::class);
        $deck = $this->createMock(Deck::class);
        $deck->method('getParent')->willReturn(null);
        $review = $this->createMock(Review::class);
        $review->method('getRate')->willReturn(5);
        $this->entityManager->expects($this->once())->method('persist')->with(
            $this->isInstanceOf(Review::class));
        $this->entityManager->expects($this->once())->method('flush');
        $this->reviewService->store($review, $deck, $user);
    }

    /* Tests the store method of ReviewService with a deck that has a parent deck.
       Verifies that the review is persisted for both the child and parent decks (2 persist calls).
       Ensures that the flush method is called once, ensuring both reviews are saved to the database. */
    public function testStoreWithParentDeck(): void{
        $parentDeck = $this->createMock(Deck::class);
        $parentDeck->method('getParent')->willReturn(null);
        $user = $this->createMock(AppUser::class);
        $deck = $this->createMock(Deck::class);
        $deck->method('getParent')->willReturn($parentDeck);
        $review = $this->createMock(Review::class);
        $review->method('getRate')->willReturn(5);
        $this->entityManager->expects($this->exactly(2))->method('persist')->with(
            $this->isInstanceOf(Review::class));
        $this->entityManager->expects($this->once())->method('flush');
        $this->reviewService->store($review, $deck, $user);
    }

    // Tests the remove method of ReviewService.
    public function testRemove(): void{
        $review = $this->createMock(Review::class);
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($review);
        $this->reviewService->remove($review);
    }
}
