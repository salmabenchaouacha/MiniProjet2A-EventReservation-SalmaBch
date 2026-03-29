<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329010043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD available_seats INT NOT NULL');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA23971F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE webauthn_credentials ADD CONSTRAINT FK_DFEA8490A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events DROP available_seats');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA23971F7E88B');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239A76ED395');
        $this->addSql('ALTER TABLE webauthn_credentials DROP FOREIGN KEY FK_DFEA8490A76ED395');
    }
}
