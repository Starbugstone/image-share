<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add apiToken field to user table
 */
final class Version20250128000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add apiToken field to user table';
    }

    public function up(Schema $schema): void
    {
        // Add apiToken column to user table
        $this->addSql('ALTER TABLE `user` ADD api_token VARCHAR(255) DEFAULT NULL');
        
        // Create index for faster token lookups
        $this->addSql('CREATE INDEX IDX_8D93D6497DA5256D ON `user` (api_token)');
    }

    public function down(Schema $schema): void
    {
        // Remove index
        $this->addSql('DROP INDEX IDX_8D93D6497DA5256D ON `user`');
        
        // Remove apiToken column
        $this->addSql('ALTER TABLE `user` DROP api_token');
    }
}
