<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Notification;
use App\Form\GoalType;
use App\Form\UserType;
use App\Entity\AppUser;
use App\Service\UserService;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{

    public function __construct(
        private UserService            $userService,
        private NotificationRepository $notificationRepository
    ) {}

    /* Displays and processes the user profile settings form.
       Handles the user settings form. If the form is valid and submitted, updates the user's data. */
    #[Route('/', name: 'app_user')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var AppUser $user */
        $user = $this->getUser();
        $unread_notifications = $this->notificationRepository->countUnreadForUser($user);
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->edit($user, $form, $passwordHasher);
        }
        return $this->render('user/index.html.twig', [
            'form' => $form,
            'unread_notifications' => $unread_notifications
        ]);
    }

    // Displays the user's current and previous goals.
    #[Route('/goals', name: 'goals')]
    public function goals():Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $actual_goal = $user->getActualGoal();
        $previous_goals = $user->getPreviousGoals();
        $unread_notifications = $this->notificationRepository->countUnreadForUser($user);
        return $this->render('user/goals.html.twig', [
            'actual_goal' => $actual_goal,
            'previous_goals' => $previous_goals,
            'unread_notifications' => $unread_notifications
        ]);
    }

    /* Creates a new goal for the user if they don't have an existing one.
       If the goal is valid, it sets the goal and redirects to the goals page. */
    #[Route('/new_goal', name: 'new_goal')]
    public function new_goal(Request $request): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        if ($user->getActualGoal())
            throw new \LogicException('Cíl na tento týden již byl nastaven');
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (($goal->getTargetCards() ?? 0) <= 0 &&
                ($goal->getTargetTests() ?? 0) <= 0
            ) {
                $form->addError(new FormError('Musíte zadat alespoň jeden cíl větší než 0'));
            }
            else {
                $this->userService->setGoal($user, $goal);
                return $this->redirectToRoute('goals');
            }
        }
        return $this->render('user/new_goal.html.twig', [
            'form' => $form
        ]);
    }

    // Check if goal is completed. Displays a page indicating the user has completed their goal for the week.
    #[Route('/completed_goal', name: 'completed_goal')]
    public function competed_goal(): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $actual_goal = $user->getActualGoal();
        if (!$actual_goal)
            throw new \LogicException('Cíl na tento týden ještě nebyl nastavěn');
        if (!$actual_goal->isCompleted())
            throw new \LogicException('Cíl na tento týden ještě nebyl splněn');
        $this->userService->completeGoal($actual_goal);
        return $this->render('user/completed_goal.html.twig', [
            'actual_goal' => $actual_goal
        ]);
    }

    // Display user's notifications.
    #[Route('/notifications', name: 'notifications')]
    public function show_notifications(): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $notifications = $user->getNotifications();
        return $this->render('user/notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    // Marks a notification as read for the current user.
    #[Route('/notifications/{id}/read', name: 'read_notification', requirements: ['id' => '\d+'])]
    public function read_notification(Notification $notification): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        if ($notification->getPersonToNotificate() !== $user)
            throw $this->createAccessDeniedException();
        $this->userService->readNotification($notification);
        return $this->redirectToRoute('notifications');
    }
}
