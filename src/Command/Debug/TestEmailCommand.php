<?php

namespace App\Command\Debug;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'debug:test-email',
    description: 'Test email functionality - send verification email to user ID or test email to admins',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user-id', InputArgument::OPTIONAL, 'User ID to resend verification email to (optional)')
            ->setHelp('
This command allows you to test email functionality:

<info>Send verification email to specific user:</info>
  <comment>php bin/console debug:test-email 5</comment>

<info>Send test email to all admin accounts:</info>
  <comment>php bin/console debug:test-email</comment>
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('user-id');

        try {
            if ($userId) {
                return $this->sendVerificationEmail($io, (int)$userId);
            } else {
                return $this->sendTestEmailToAdmins($io);
            }
        } catch (\Exception $e) {
            $io->error('Command failed: ' . $e->getMessage());
            if ($output->isVerbose()) {
                $io->error('Stack trace: ' . $e->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }

    private function sendVerificationEmail(SymfonyStyle $io, int $userId): int
    {
        $io->title('Sending Verification Email');

        // Find the user
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            $io->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        $io->info("Found user: {$user->getEmail()} (ID: {$user->getId()})");
        $io->info("Username: {$user->getUsername()}");
        $io->info("Verified: " . ($user->isVerified() ? 'Yes' : 'No'));

        if ($user->isVerified()) {
            $io->warning('User is already verified. Sending verification email anyway...');
        }

        // Send verification email
        $io->info('Sending verification email...');
        
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@imageshare.com', 'ImageShare'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email - ImageShare')
                ->htmlTemplate('email/verification.html.twig')
                ->context(['user' => $user])
        );

        $io->success("Verification email sent successfully to {$user->getEmail()}!");
        $io->note('Check Mailpit at http://localhost:8026 to view the email.');
        
        return Command::SUCCESS;
    }

    private function sendTestEmailToAdmins(SymfonyStyle $io): int
    {
        $io->title('Sending Test Email to Admin Accounts');

        // Find all admin users
        $adminUsers = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :adminRole')
            ->setParameter('adminRole', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        if (empty($adminUsers)) {
            $io->warning('No admin users found. Sending test email to all verified users instead...');
            
            // Fallback to verified users
            $adminUsers = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.isVerified = :verified')
                ->setParameter('verified', true)
                ->setMaxResults(5) // Limit to first 5 users
                ->getQuery()
                ->getResult();
        }

        if (empty($adminUsers)) {
            $io->error('No users found to send test email to.');
            return Command::FAILURE;
        }

        $io->info('Found ' . count($adminUsers) . ' user(s) to send test email to:');
        foreach ($adminUsers as $user) {
            $roles = implode(', ', $user->getRoles());
            $io->text("  - {$user->getEmail()} ({$user->getUsername()}) - Roles: {$roles}");
        }

        $io->newLine();
        $io->info('Sending test emails...');

        $successCount = 0;
        $failureCount = 0;

        foreach ($adminUsers as $user) {
            try {
                $email = (new Email())
                    ->from(new Address('noreply@imageshare.com', 'ImageShare Debug'))
                    ->to($user->getEmail())
                    ->subject('ImageShare - Email System Test')
                    ->html($this->getTestEmailHtml($user))
                    ->text($this->getTestEmailText($user));

                $this->mailer->send($email);
                $io->text("✓ Sent to {$user->getEmail()}");
                $successCount++;
            } catch (\Exception $e) {
                $io->text("✗ Failed to send to {$user->getEmail()}: " . $e->getMessage());
                $failureCount++;
            }
        }

        $io->newLine();
        if ($successCount > 0) {
            $io->success("Successfully sent {$successCount} test email(s)!");
        }
        if ($failureCount > 0) {
            $io->warning("Failed to send {$failureCount} email(s).");
        }

        $io->note('Check Mailpit at http://localhost:8026 to view the emails.');
        
        return $failureCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function getTestEmailHtml(User $user): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>ImageShare Email Test</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #3B82F6, #8B5CF6); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
                .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🧪 Email System Test</h1>
                <p>ImageShare Development Environment</p>
            </div>
            
            <div class='content'>
                <h2>Hello {$user->getUsername()}!</h2>
                
                <p>This is a test email to verify that the ImageShare email system is working correctly.</p>
                
                <div class='info'>
                    <h3>📋 Test Details</h3>
                    <ul>
                        <li><strong>Recipient:</strong> {$user->getEmail()}</li>
                        <li><strong>User ID:</strong> {$user->getId()}</li>
                        <li><strong>Username:</strong> {$user->getUsername()}</li>
                        <li><strong>Roles:</strong> " . implode(', ', $user->getRoles()) . "</li>
                        <li><strong>Verified:</strong> " . ($user->isVerified() ? 'Yes' : 'No') . "</li>
                        <li><strong>Sent at:</strong> " . (new \DateTime())->format('Y-m-d H:i:s') . "</li>
                    </ul>
                </div>
                
                <p>✅ If you received this email, the email system is functioning properly!</p>
                
                <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                
                <p><small>This is an automated test email from the ImageShare development environment. No action is required.</small></p>
            </div>
            
            <div class='footer'>
                <p>© " . date('Y') . " ImageShare - Development Environment</p>
                <p>Email sent via debug:test-email command</p>
            </div>
        </body>
        </html>
        ";
    }

    private function getTestEmailText(User $user): string
    {
        return "
ImageShare Email System Test
============================

Hello {$user->getUsername()}!

This is a test email to verify that the ImageShare email system is working correctly.

Test Details:
- Recipient: {$user->getEmail()}
- User ID: {$user->getId()}
- Username: {$user->getUsername()}
- Roles: " . implode(', ', $user->getRoles()) . "
- Verified: " . ($user->isVerified() ? 'Yes' : 'No') . "
- Sent at: " . (new \DateTime())->format('Y-m-d H:i:s') . "

✅ If you received this email, the email system is functioning properly!

---
This is an automated test email from the ImageShare development environment.
No action is required.

© " . date('Y') . " ImageShare - Development Environment
Email sent via debug:test-email command
        ";
    }
}