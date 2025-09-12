<?php

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\Card;
use App\Entity\Category;
use App\Entity\Deck;
use App\Entity\Review;
use App\Entity\Test;
use App\Service\CardService;
use App\Service\CategoryService;
use App\Service\ReviewService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use App\Service\DeckService;
use Doctrine\ORM\EntityManagerInterface;

class DeckServiceTest extends TestCase
{
    private DeckService $deckService;
    private EntityManagerInterface $entityManager;
    private CategoryService $categoryService;
    private CardService $cardService;
    private ReviewService $reviewService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cardService = $this->createMock(CardService::class);
        $this->categoryService = $this->createMock(CategoryService::class);
        $this->reviewService = $this->createMock(ReviewService::class);
        $this->deckService = new DeckService($this->entityManager, $this->categoryService,
        $this->cardService, $this->reviewService);
    }

    /* Tests the store method of DeckService.
       Ensures that the deck is persisted, flushed, and the user is set as the owner. */
    public function testStore(): void{
        $deck =  new Deck();
        $user = $this->createMock(AppUser::class);
        $this->entityManager->expects($this->once())->method('persist')->with($deck);
        $this->entityManager->expects($this->once())->method('flush');
        $this->deckService->store($deck, $user);
        $this->assertSame($user, $deck->getOwner());
    }

    /* Tests the remove method of DeckService.
       Ensures that deck and all associated cards, reviews, and tests are removed correctly.
       The parent of the child deck is set to null. */
    public function testRemove(): void{
        $deck = $this->createMock(Deck::class);
        $childDeck = $this->createMock(Deck::class);
        $card = $this->createMock(Card::class);
        $review = $this->createMock(Review::class);
        $test = $this->createMock(Test::class);
        $deck->method('getParent')->willReturn($deck);
        $deck->method('getChildDecks')->willReturn(new ArrayCollection([$childDeck]));
        $deck->method('getCards')->willReturn(new ArrayCollection([$card]));
        $deck->method('getTests')->willReturn(new ArrayCollection([$test]));
        $deck->method('getReviews')->willReturn(new ArrayCollection([$review]));
        $this->entityManager->expects($this->exactly(4))
            ->method('remove')
            ->with($this->logicalOr($card, $review, $test, $deck));
        $this->deckService->remove($deck);
        $this->assertEquals(null, $childDeck->getParent());
    }

    /* Tests the clone method of DeckService.
       Ensures that when a deck is cloned, all information is correctly copied,
       that the cloned deck is set to private, and
       that all categories and cards from the original deck are transferred to the new one.
       Verifies that the parent-child relationship. */
    public function testClone(): void{
        $user = $this->createMock(AppUser::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);
        $card = $this->createMock(Card::class);
        $card->method('getFrontSide')->willReturn('Front side');
        $card->method('getBackSide')->willReturn('Back side');
        $deck = $this->createMock(Deck::class);
        $deck->method('getName')->willReturn('Deck Name');
        $deck->method('getAbout')->willReturn('Deck Description');
        $deck->method('getCategories')->willReturn(new ArrayCollection([$category1, $category2]));
        $deck->method('getCards')->willReturn(new ArrayCollection([$card]));
        $clonedDeck = $this->deckService->clone($deck, $user);
        $this->assertEquals('Deck Name', $clonedDeck->getName());
        $this->assertEquals('Deck Description', $clonedDeck->getAbout());
        $this->assertTrue($clonedDeck->IsPrivate());
        $this->assertSame($user, $clonedDeck->getOwner());
        $this->assertCount(2, $clonedDeck->getCategories());
        $this->assertContains($category1, $clonedDeck->getCategories());
        $this->assertContains($category2, $clonedDeck->getCategories());
        $this->assertCount(1, $clonedDeck->getCards());
        $this->assertEquals($deck, $clonedDeck->getParent());
    }

    /* Tests the publicate method of DeckService.
       Ensures that when a deck is published, all information is correctly retained,
       the deck's visibility changes to public,
       and all categories and cards from the original deck are transferred.
       Checks that the published deck has the original deck as its child. */
    public function testPublicate(): void{
        $user = $this->createMock(AppUser::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);
        $deck = $this->createMock(Deck::class);
        $card = $this->createMock(Card::class);
        $card->method('getFrontSide')->willReturn('Front side');
        $card->method('getBackSide')->willReturn('Back side');
        $deck->method('getName')->willReturn('Deck Name');
        $deck->method('getOwner')->willReturn($user);
        $deck->method('isPrivate')->willReturn(true);
        $deck->method('getAbout')->willReturn('Deck Description');
        $deck->method('getCategories')->willReturn(new ArrayCollection([$category1, $category2]));
        $deck->method('getCards')->willReturn(new ArrayCollection([$card]));
        $publishedDeck = $this->deckService->publicate($deck);
        $this->assertEquals('Deck Name', $publishedDeck->getName());
        $this->assertEquals('Deck Description', $publishedDeck->getAbout());
        $this->assertFalse($publishedDeck->IsPrivate());
        $this->assertEquals($user, $publishedDeck->getOwner());
        $this->assertCount(2, $publishedDeck->getCategories());
        $this->assertContains($category1, $publishedDeck->getCategories());
        $this->assertContains($category2, $publishedDeck->getCategories());
        $this->assertCount(1, $publishedDeck->getCards());
        $this->assertContains($deck, $publishedDeck->getChildDecks());
    }

    /* Tests the addCategory method of DeckService.
       Verifies that a category is correctly created with the given name and
       that it is properly associated with the deck.*/
    public function testAddCategory(): void{
        $deck = $this->createMock(Deck::class);
        $category = $this->deckService->addCategory($deck, 'Category Name');
        $this->assertEquals('Category Name', $category->getName());
        $this->assertContains($deck, $category->getDecks());
    }
}
