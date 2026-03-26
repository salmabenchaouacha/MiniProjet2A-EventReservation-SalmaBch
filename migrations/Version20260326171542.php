<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326171542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, location VARCHAR(255) NOT NULL, seats INT NOT NULL, available_seats INT NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_tokens (refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, event_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_4DA23971F7E88B (event_id), INDEX IDX_4DA239A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE webauthn_credentials (id INT AUTO_INCREMENT NOT NULL, credential_id LONGTEXT NOT NULL, credential_data JSON NOT NULL, name VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_DFEA8490A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA23971F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE webauthn_credentials ADD CONSTRAINT FK_DFEA8490A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA23971F7E88B');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239A76ED395');
        $this->addSql('ALTER TABLE webauthn_credentials DROP FOREIGN KEY FK_DFEA8490A76ED395');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE webauthn_credentials');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
