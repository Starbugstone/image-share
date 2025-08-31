<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create all tables for ImageShare application';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            username VARCHAR(255) NOT NULL,
            is_verified TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create album table
        $this->addSql('CREATE TABLE album (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            is_public TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_39986E43A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create image table
        $this->addSql('CREATE TABLE image (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            album_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            image_name VARCHAR(255) NOT NULL,
            image_size INT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_C53D045FA76ED395 (user_id),
            INDEX IDX_C53D045F1137ABCF (album_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create share table
        $this->addSql('CREATE TABLE share (
            id INT AUTO_INCREMENT NOT NULL,
            image_id INT DEFAULT NULL,
            album_id INT DEFAULT NULL,
            shared_by_id INT NOT NULL,
            shared_with_id INT NOT NULL,
            shared_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            message LONGTEXT DEFAULT NULL,
            UNIQUE INDEX unique_image_share (image_id, shared_with_id),
            UNIQUE INDEX unique_album_share (album_id, shared_with_id),
            INDEX IDX_EF069D5A3DA5256D (image_id),
            INDEX IDX_EF069D5A1137ABCF (album_id),
            INDEX IDX_EF069D5A8E6B6C3D (shared_by_id),
            INDEX IDX_EF069D5A1C430F99 (shared_with_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create comment table
        $this->addSql('CREATE TABLE comment (
            id INT AUTO_INCREMENT NOT NULL,
            image_id INT DEFAULT NULL,
            share_id INT DEFAULT NULL,
            author_id INT NOT NULL,
            content LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_9474526C3DA5256D (image_id),
            INDEX IDX_9474526C5D25F4A6 (share_id),
            INDEX IDX_9474526CF675F31B (author_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1137ABCF FOREIGN KEY (album_id) REFERENCES album (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A1137ABCF FOREIGN KEY (album_id) REFERENCES album (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A8E6B6C3D FOREIGN KEY (shared_by_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A1C430F99 FOREIGN KEY (shared_with_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C5D25F4A6 FOREIGN KEY (share_id) REFERENCES share (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints first
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C5D25F4A6');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C3DA5256D');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A1C430F99');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A8E6B6C3D');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A1137ABCF');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A3DA5256D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1137ABCF');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FA76ED395');
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43A76ED395');

        // Drop tables in reverse order (due to foreign key dependencies)
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE share');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE `user`');
    }
}

