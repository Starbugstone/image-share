<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/password')]
class PasswordResetController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    #[Route('/reset', name: 'api_password_reset_request', methods: ['POST'])]
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate email
        $violations = $this->validator->validate($data['email'] ?? '', [
            new Assert\NotBlank(['message' => 'Email is required']),
            new Assert\Email(['message' => 'Please enter a valid email address'])
        ]);

        if (count($violations) > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => $violations[0]->getMessage()
            ], 400);
        }

        $email = $data['email'];
        $user = $this->userRepository->findOneBy(['email' => $email]);

        // Always return success to prevent email enumeration
        if (!$user) {
            return new JsonResponse([
                'success' => true,
                'message' => 'If an account with that email exists, we have sent a password reset link.'
            ]);
        }

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $resetTokenExpiry = new \DateTime('+1 hour');

        $user->setPasswordResetToken($resetToken);
        $user->setPasswordResetTokenExpiry($resetTokenExpiry);

        $this->entityManager->flush();

        // Send reset email
        $this->sendPasswordResetEmail($user, $resetToken);

        return new JsonResponse([
            'success' => true,
            'message' => 'If an account with that email exists, we have sent a password reset link.'
        ]);
    }

    #[Route('/reset/confirm', name: 'api_password_reset_confirm', methods: ['POST'])]
    public function confirmPasswordReset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate input
        $violations = $this->validator->validate($data, [
            new Assert\Collection([
                'token' => [
                    new Assert\NotBlank(['message' => 'Reset token is required']),
                    new Assert\Length(['min' => 64, 'max' => 64, 'exactMessage' => 'Invalid reset token'])
                ],
                'password' => [
                    new Assert\NotBlank(['message' => 'Password is required']),
                    new Assert\Length(['min' => 8, 'minMessage' => 'Password must be at least 8 characters long'])
                ],
                'password_confirmation' => [
                    new Assert\NotBlank(['message' => 'Password confirmation is required'])
                ]
            ])
        ]);

        if (count($violations) > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => $violations[0]->getMessage()
            ], 400);
        }

        // Check password confirmation
        if ($data['password'] !== $data['password_confirmation']) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Password confirmation does not match'
            ], 400);
        }

        // Find user by reset token
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $data['token']]);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid or expired reset token'
            ], 400);
        }

        // Check if token is expired
        if ($user->getPasswordResetTokenExpiry() < new \DateTime()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Reset token has expired. Please request a new password reset.'
            ], 400);
        }

        // Update password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        
        // Clear reset token
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenExpiry(null);

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Your password has been successfully reset. You can now log in with your new password.'
        ]);
    }

    private function sendPasswordResetEmail(User $user, string $token): void
    {
        $frontendUrl = $this->getParameter('app.frontend_url') ?? 'http://localhost:5173';
        $resetUrl = $frontendUrl . '/password/reset?token=' . $token;

        $email = (new Email())
            ->from($this->getParameter('app.mail_from'))
            ->to($user->getEmail())
            ->subject('Reset Your Password - ImageShare')
            ->html($this->renderView('emails/password_reset.html.twig', [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expiryTime' => '1 hour'
            ]));

        $this->mailer->send($email);
    }
}