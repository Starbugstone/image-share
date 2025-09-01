<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Create a test user for login testing',
)]
class CreateTestUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Delete existing test user if exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
        if ($existingUser) {
            $this->entityManager->remove($existingUser);
            $this->entityManager->flush();
            $output->writeln('Removed existing test user');
        }

        // Create new test user
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setUsername('testuser');
        $user->setIsVerified(true);
        $user->setRoles(['ROLE_USER']);
        
        // Hash the password properly
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Created test user:');
        $output->writeln('Email: test@test.com');
        $output->writeln('Password: password123');
        $output->writeln('Verified: Yes');

        return Command::SUCCESS;
    }
}