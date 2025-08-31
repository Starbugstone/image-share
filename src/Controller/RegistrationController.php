<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    // Registration is now handled by API endpoints in ApiController

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, EntityManagerInterface $entityManager): Response
    {
        // Extract user ID from the verification link
        $userId = $request->query->get('id');
        if (!$userId) {
            $this->addFlash('verify_email_error', 'Invalid verification link.');
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('verify_email_error', 'User not found.');
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        // Always ensure verified users have ROLE_USER
        if ($user->isVerified()) {
            // Ensure user has ROLE_USER when email is verified
            $currentRoles = $user->getRoles();
            if (!in_array('ROLE_USER', $currentRoles)) {
                $user->setRoles(['ROLE_USER']);
                $entityManager->flush();
            }
        }

        $this->addFlash('success', 'Your email address has been verified. You can now log in.');

        return $this->redirectToRoute('app_login');
    }

    // Terms page is now handled by Vue.js frontend

    #[Route('/verify/resend/{id}', name: 'app_verify_resend')]
    public function resendVerification(
        int $id,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', 'Your email is already verified.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Generate a new verification email
        try {
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@imageshare.com', 'ImageShare'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email - ImageShare')
                    ->htmlTemplate('email/verification.html.twig')
                    ->context(['user' => $user])
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to send verification email. Please try again later.');
            return $this->redirectToRoute('app_dashboard');
        }

        $this->addFlash('success', 'A new verification email has been sent to your email address.');

        return $this->redirectToRoute('app_dashboard');
    }


}

