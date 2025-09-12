<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Form\RegistrationFormType;
use App\Service\UserService;
use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private UserService   $userService)
    {}

    /* Handles user registration, processing the registration form,
       hashing the password, storing the user, sending a verification email, and logging the user in upon success. */
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             Security $security): Response
    {
        $user = new AppUser();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->store($user, $userPasswordHasher);
            $this->userService->sendVerificationEmail($user);
            return $security->login($user, 'form_login', 'main');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /* Handles email verification after registration.
    Ensures the user is authenticated, attempts to confirm their email using the verification link,
    and redirects appropriately based on the success or failure of the verification process.
    Source:  https://github.com/SymfonyCasts/verify-email-bundle */
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            /** @var AppUser $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $reason = $exception->getReason();
            if (str_contains($reason, 'expired')){
                $this->addFlash('verify_email_error', 'Ověřovací odkaz již vypršel.');
                return $this->redirectToRoute('app_user');
            }
            else if (str_contains($reason, 'invalid')){
                $this->addFlash('verify_email_error', 'Ověřovací odkaz není platný.');
                return $this->redirectToRoute('register');
            }
            else {
                $this->addFlash('verify_email_error', $translator->trans($reason, [], 'VerifyEmailBundle'));
                return $this->redirectToRoute('login');
            }
        }
        return $this->redirectToRoute('my_decks');
    }
}
