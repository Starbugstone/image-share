<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:admin',
    description: 'Manage user roles - promote to admin or list users',
)]
class UserAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::OPTIONAL, 'User ID to promote to admin')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List all users')
            ->addOption('demote', 'd', InputOption::VALUE_NONE, 'Demote user from admin (use with userId)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('userId');

        // List all users
        if ($input->getOption('list')) {
            return $this->listUsers($io);
        }

        // Handle user promotion/demotion
        if ($userId) {
            return $this->manageUserRole($io, $userId, $input);
        }

        // Interactive mode
        return $this->interactiveMode($io, $input);
    }

    private function listUsers(SymfonyStyle $io): int
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        if (empty($users)) {
            $io->warning('No users found.');
            return Command::SUCCESS;
        }

        $io->title('User List');
        $io->table(
            ['ID', 'Username', 'Email', 'Verified', 'Roles'],
            array_map(function (User $user) {
                return [
                    $user->getId(),
                    $user->getUsername(),
                    $user->getEmail(),
                    $user->isVerified() ? '✓' : '✗',
                    implode(', ', $user->getRoles())
                ];
            }, $users)
        );

        return Command::SUCCESS;
    }

    private function manageUserRole(SymfonyStyle $io, string $userId, InputInterface $input): int
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            $io->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        $currentRoles = $user->getRoles();
        $isDemote = $input->getOption('demote');
        $force = $input->getOption('force');

        if ($isDemote) {
            // Remove ROLE_ADMIN
            if (!in_array('ROLE_ADMIN', $currentRoles)) {
                $io->warning("User {$user->getUsername()} is not an admin.");
                return Command::SUCCESS;
            }

            if (!$force && !$io->confirm("Demote {$user->getUsername()} from admin role?")) {
                $io->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            $newRoles = array_filter($currentRoles, fn($role) => $role !== 'ROLE_ADMIN');
            $user->setRoles($newRoles);
            $this->entityManager->flush();

            $io->success("User {$user->getUsername()} demoted from admin.");
        } else {
            // Add ROLE_ADMIN
            if (in_array('ROLE_ADMIN', $currentRoles)) {
                $io->warning("User {$user->getUsername()} is already an admin.");
                return Command::SUCCESS;
            }

            if (!$force && !$io->confirm("Promote {$user->getUsername()} to admin role?")) {
                $io->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            $newRoles = $currentRoles;
            $newRoles[] = 'ROLE_ADMIN';
            $user->setRoles($newRoles);
            $this->entityManager->flush();

            $io->success("User {$user->getUsername()} promoted to admin.");
        }

        return Command::SUCCESS;
    }

    private function interactiveMode(SymfonyStyle $io, InputInterface $input): int
    {
        $io->title('User Admin Management');

        $users = $this->entityManager->getRepository(User::class)->findAll();

        if (empty($users)) {
            $io->warning('No users found.');
            return Command::SUCCESS;
        }

        $choices = [];
        foreach ($users as $user) {
            $roles = implode(', ', $user->getRoles());
            $choices[$user->getId()] = sprintf(
                '%s (ID: %d, Roles: %s, Verified: %s)',
                $user->getUsername(),
                $user->getId(),
                $roles,
                $user->isVerified() ? 'Yes' : 'No'
            );
        }

        $selectedId = $io->choice('Select a user', $choices);
        $userId = array_search($selectedId, $choices);

        $action = $io->choice('What would you like to do?', [
            'promote' => 'Promote to Admin',
            'demote' => 'Demote from Admin',
            'cancel' => 'Cancel'
        ]);

        if ($action === 'cancel') {
            $io->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        return $this->manageUserRole($io, (string)$userId, $input->setOption($action === 'demote' ? 'demote' : null, true));
    }
}
