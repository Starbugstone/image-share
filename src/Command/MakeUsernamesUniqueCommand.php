<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:make-usernames-unique',
    description: 'Make usernames unique in the user table',
)]
class MakeUsernamesUniqueCommand extends Command
{
    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->info('Checking current username constraints...');
            
            // Check if unique constraint already exists
            $constraints = $this->connection->fetchAllAssociative(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'user' 
                 AND CONSTRAINT_TYPE = 'UNIQUE' 
                 AND CONSTRAINT_NAME LIKE '%username%'"
            );

            if (!empty($constraints)) {
                $io->warning('Username unique constraint already exists!');
                return Command::SUCCESS;
            }

            $io->info('Adding unique constraint to username field...');
            
            // Add unique constraint
            $this->connection->executeStatement(
                'ALTER TABLE `user` ADD UNIQUE INDEX UNIQ_8D93D649F85E0677 (username)'
            );

            $io->success('Username field is now unique!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error making usernames unique: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
