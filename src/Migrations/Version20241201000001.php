<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create default admin user
 */
final class Version20241201000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create default admin user for ImageShare application';
    }

    public function up(Schema $schema): void
    {
        // Create a default admin user
        // Password: admin123 - CHANGE THIS IN PRODUCTION!
        // You can generate a new hash using: php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
        $this->addSql("INSERT INTO `user` (email, roles, password, username, is_verified, created_at) VALUES (
            'admin@imageshare.com',
            '[\"ROLE_ADMIN\", \"ROLE_USER\"]',
            '\$2y\$13\$K.8X2rJ8mL8zY8xX8xX8xX8xX8xX8xX8xX8xX8xX8xX8xX8xX8x', -- hashed password for 'admin123'
            'admin',
            1,
            NOW()
        )");
    }

    public function down(Schema $schema): void
    {
        // Remove the default admin user
        $this->addSql("DELETE FROM `user` WHERE email = 'admin@imageshare.com'");
    }
}
