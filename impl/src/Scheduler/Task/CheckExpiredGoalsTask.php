<?php
declare(strict_types=1);
namespace App\Scheduler\Task;

use App\Service\UserService;
use App\Repository\GoalRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: '10 seconds', schedule: 'expired_goals_schedule')]
class CheckExpiredGoalsTask
{
    public function __construct(
        private GoalRepository  $goalRepository,
        private UserService     $userService,
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {}

    /* Handles expired goals by sending notifications and updating their status.
       Retrieves a list of expired goals. For each goal, it checks if it has been completed;
       if it has, the method returns early. If the goal is not completed,
       logs a warning and sends an email to the user (if they are verified) informing them that the goal was not
       achieved by the end date. After sending the email, the goal's status is updated.
       Also creates an expired goal notification for the user. */
    public function __invoke(){
        $expiredGoals = $this->goalRepository->findExpiredGoals();
        foreach ($expiredGoals as $goal){
            $user = $goal->getOwner();
            if ($goal->isCompleted())
                return;
            $this->logger->warning("Message: expired goal. User {$user->getId()}");
            if ($user->isVerified()){
                $endDate = $goal->getEndDate()->format('d.m.Y');
                $email = (new Email())
                    ->from('mailer@wordly.com')
                    ->to($user->getEmail())
                    ->subject("Cíl nebyl dosažen. Termín splnění byl do {$endDate}.")
                    ->text('Chybělo opravdu jen maličko, ale to nevadí. Příště to určitě zvládnete lépe! 💪');
                try {
                    $this->mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    $this->logger->warning('Message: error ' . $e->getMessage());
                }
            }
            $goal->setIsCurrent(false);
            $this->userService->updateGoal($goal);
            $this->userService->createExpiredGoalNotification($user, $goal);
        }
    }
}