<?php

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\Deck;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Repository\TestRepository;
use App\Service\StatsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class StatsServiceTest extends TestCase
{
    private DeckRepository  $deckRepository;
    private CardRepository  $cardRepository;
    private TestRepository  $testRepository;
    private RouterInterface $router;
    private StatsService $statsService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void
    {
        parent::setUp();
        $this->deckRepository = $this->createMock(DeckRepository::class);
        $this->cardRepository = $this->createMock(CardRepository::class);
        $this->testRepository = $this->createMock(TestRepository::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->statsService = new StatsService($this->deckRepository, $this->cardRepository,
        $this->testRepository, $this->router);
    }

    /* Tests the getStatisticsPerDay method to ensure it correctly retrieves statistics for a specific day,
       including the number of studied cards, cards to study, and completed tests for a user. */
    public function testStatisticsPerDay(): void{
        $user = $this->createMock(AppUser::class);
        $date = $this->createMock(\DateTimeImmutable::class);
        $deck = $this->createMock(Deck::class);
        $this->deckRepository->method('findPrivateByUser')
            ->willReturn([$deck]);
        $this->cardRepository->method('countOfStudiedPerDay')
            ->with($deck, $date)
            ->willReturn(3);
        $this->cardRepository->method('findCardsToStudy')
            ->with($deck)
            ->willReturn([1, 2, 3]);
        $this->testRepository->method('countOfFinishedPerDay')
            ->with($deck, $date)
            ->willReturn(2);
        $result = $this->statsService->getStatisticsPerDay($user, $date);
        $this->assertEquals(3, $result['total_studied']);
        $this->assertEquals(3, $result['total_to_study']);
        $this->assertEquals(2, $result['total_tests']);
        $this->assertCount(1, $result['stats']);
        $this->assertEquals(3, $result['stats'][0]['studied_cards']);
        $this->assertEquals(3, $result['stats'][0]['cards_to_study']);
        $this->assertEquals(2, $result['stats'][0]['tests']);
    }

    /* Tests the getSomeStatisticsPerDay method to verify that it correctly calculates
       the total number of studied cards and completed tests for a given user on a specific day. */
    public function testSomeStatisticsPerDay(): void{
        $user = $this->createMock(AppUser::class);
        $date = $this->createMock(\DateTimeImmutable::class);
        $this->cardRepository->method('countOfStudiedByUser')
            ->with($user, $date)
            ->willReturn(10);
        $this->testRepository->method('countOfFinishedPerUser')
            ->with($user, $date)
            ->willReturn(5);
        $result = $this->statsService->getSomeStatisticsPerDay($user, $date);
        $this->assertEquals(10, $result['total_studied']);
        $this->assertEquals(5, $result['total_tests']);
    }
}
