<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to make usernames unique
 */
final class Version20250128000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make usernames unique in user table';
    }

    public function up(Schema $schema): void
    {
        // Add unique constraint to username field
        $this->addSql('ALTER TABLE `user` ADD UNIQUE INDEX UNIQ_8D93D649F85E0677 (username)');
    }

    public function down(Schema $schema): void
    {
        // Remove unique constraint from username field
        $this->addSql('ALTER TABLE `user` DROP INDEX UNIQ_8D93D649F85E0677');
    }
}
