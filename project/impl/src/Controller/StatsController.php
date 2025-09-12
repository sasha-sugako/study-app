<?php

namespace App\Controller;
use App\Entity\AppUser;
use App\Service\StatsService;
use App\Repository\BonusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stats')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class StatsController extends AbstractController
{
    public function __construct(
        private StatsService    $statsService,
        private BonusRepository $bonusRepository
    ){}

    /* Displays user statistics including daily and weekly study/test data, bonus availability,
       and a streak of continuous learning days. It fetches day and week statistics, calculates
       unused bonuses, and determines dates when the user maintained a study streak, including
       dates eligible for bonus rewards (every 5th day). */
    #[Route('/', name: 'stats')]
    public function all_stats(): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $date = new \DateTimeImmutable();
        $day_stats = $this->statsService->getStatisticsPerDay($user, $date);
        $week_stats = $this->statsService->getStatisticsPerWeek($user, $date);
        $bonuses = [
            'day_bonuses' => $this->bonusRepository->getCountNotUsedDayBonuses($user),
            'test_bonuses' => $this->bonusRepository->getCountNotUsedTestBonuses($user)
        ];
        $week_stats[] = [
            'date' => $date,
            'total_studied' => $day_stats['total_studied'],
            'total_tests' => $day_stats['total_tests']
        ];
        $last_active = $user->getLastActive();
        $days_without_break = $user->getDaysWithoutBreak();
        $learnedDates = [];
        $bonusDates = [];
        for ($i = 0; $i < $days_without_break; $i++) {
            $learnedDates[] = $last_active->format('Y-m-d');
            $last_active = $last_active->modify('-1 day');
        }
        usort($learnedDates, function ($a, $b) {
            return $a <=> $b;
        });
        for ($i = 1; $i <= $days_without_break; $i++){
            if ($i % 5 === 0)
                $bonusDates[] = $learnedDates[$i-1];
        }
        return $this->render('stats/all_stats.html.twig', [
            'stats' => $day_stats['stats'],
            'total_studied' => $day_stats['total_studied'],
            'total_to_study' => $day_stats['total_to_study'],
            'total_tests' => $day_stats['total_tests'],
            'week_stats' => $week_stats,
            'bonuses' => $bonuses,
            'continuous_learning' => $learnedDates,
            'bonus_dates' => $bonusDates,
            'user' => $user
        ]);
    }
}