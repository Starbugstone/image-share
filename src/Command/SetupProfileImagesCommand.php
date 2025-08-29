<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:setup-profile-images',
    description: 'Create the profile images directory',
)]
class SetupProfileImagesCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $profileImagesDir = 'public/uploads/profile_images';

        try {
            if (!is_dir($profileImagesDir)) {
                mkdir($profileImagesDir, 0755, true);
                $io->success("Created directory: {$profileImagesDir}");
            } else {
                $io->info("Directory already exists: {$profileImagesDir}");
            }

            // Create .gitkeep file to ensure the directory is tracked
            $gitkeepFile = $profileImagesDir . '/.gitkeep';
            if (!file_exists($gitkeepFile)) {
                file_put_contents($gitkeepFile, '');
                $io->success("Created .gitkeep file");
            }

            $io->success('Profile images directory setup complete!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error setting up profile images directory: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
