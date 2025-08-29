<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add UserProfile table
 */
final class Version20250128000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UserProfile table';
    }

    public function up(Schema $schema): void
    {
        // Create user_profile table
        $this->addSql('CREATE TABLE user_profile (
            user_id INT NOT NULL,
            display_name VARCHAR(255) DEFAULT NULL,
            bio LONGTEXT DEFAULT NULL,
            profile_image_name VARCHAR(255) DEFAULT NULL,
            profile_image_size INT DEFAULT NULL,
            profile_image_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            status VARCHAR(50) NOT NULL DEFAULT \'offline\',
            last_seen DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            location VARCHAR(100) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            social_links JSON DEFAULT \'[]\',
            is_public TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(user_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        
        // Create indexes
        $this->addSql('CREATE INDEX IDX_D95AB405F85E0677 ON user_profile (display_name)');
        $this->addSql('CREATE INDEX IDX_D95AB4057B00651C ON user_profile (status)');
        $this->addSql('CREATE INDEX IDX_D95AB4058B8E8428 ON user_profile (created_at)');
    }

    public function down(Schema $schema): void
    {
        // Drop user_profile table
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('DROP TABLE user_profile');
    }
}
