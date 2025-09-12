<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use App\Repository\TestRepository;
use Symfony\Component\Routing\RouterInterface;

class StatsService
{
    public function __construct(
        private DeckRepository  $deckRepository,
        private CardRepository  $cardRepository,
        private TestRepository  $testRepository,
        private RouterInterface $router,
    ) {}

    /* Retrieves statistics for a given user on a specific date, summarizing their study progress for each private deck.
       The statistics include the number of studied cards, cards left to study, and completed tests for each deck.
       It also aggregates the total studied cards, cards to study, and completed tests across all decks.
       Returns an array with the detailed stats for each deck and the overall totals. */
    public function getStatisticsPerDay(AppUser $user, \DateTimeImmutable $date): array
    {
        $stats = [];
        $total_studied = 0;
        $total_to_study = 0;
        $total_tests = 0;
        foreach ($this->deckRepository->findPrivateByUser($user) as $deck) {
            $studied = $this->cardRepository->countOfStudiedPerDay($deck, $date);
            $to_study = count($this->cardRepository->findCardsToStudy($deck));
            $tests = $this->testRepository->countOfFinishedPerDay($deck, $date);
            $total_studied += $studied;
            $total_to_study += $to_study;
            $total_tests += $tests;
            $stats[] = [
                'deck_href' => $this->router->generate('deck', ['id' => $deck->getId()]),
                'deck_name' => $deck->getName(),
                'studied_cards' => $studied,
                'cards_to_study' => $to_study,
                'tests' => $tests
            ];
        }
        return [
            'total_studied' => $total_studied,
            'total_to_study' => $total_to_study,
            'total_tests' => $total_tests,
            'stats' => $stats];
    }

    // Retrieves statistics for the user on the specified date, including the total number of studied cards and completed tests.
    public function getSomeStatisticsPerDay(AppUser $user, \DateTimeImmutable $date): array{
        return ['total_studied' => $this->cardRepository->countOfStudiedByUser($user, $date),
            'total_tests' => $this->testRepository->countOfFinishedPerUser($user, $date)];
    }

    /* Retrieves statistics for the user over the past week (7 days),
       including the total number of studied cards and completed tests for each day. */
    public function getStatisticsPerWeek(AppUser $user, \DateTimeImmutable $date): array{
        $stats = [];
        $day_ago = -6;
        while ($day_ago < 0){
            $actual_date = new \DateTimeImmutable("$day_ago day");
            $some_stats = $this->getSomeStatisticsPerDay($user, $actual_date);
            $stats[] = [
                'date' => $actual_date,
                'total_studied' => $some_stats['total_studied'],
                'total_tests' => $some_stats['total_tests']
            ];
            $day_ago++;
        }
        return $stats;
    }
}