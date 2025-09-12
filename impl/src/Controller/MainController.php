<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MainController extends AbstractController
{
    public function __construct(
        private StatsService $statsService,
    )
    {
    }

    /* Displays the main application page.
       If the user is not authenticated, shows links to log in or register.
       If the user is authenticated, shows daily statistics (e.g., learned words, completed tests)
       and provides quick access links to important sections of the app. */
    #[Route('', name: 'main_page')]
    public function show(): Response{
        $stats = [];
        /** @var AppUser $user */
        $user = $this->getUser();
        if ($user){
            $stats = $this->statsService->getSomeStatisticsPerDay($user,
                new \DateTimeImmutable('today'));
        }
        return $this->render('main.html.twig', [
            'stats' => $stats
        ]);
    }

    // Displays a page for user authentication.
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response{
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('login.html.twig', [
            'error' => $error
        ]);
    }

    // Handles user logout.
    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // Handles errors and renders a custom error page
    #[Route('/error', name: 'show_error')]
    public function showError(FlattenException $exception): Response{
        return $this->render('error.html.twig',
            ['status_code' => $exception->getStatusCode(),
                'message' => $exception->getMessage()]);
    }
}