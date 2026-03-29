<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260329010043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration vidée car les colonnes et clés étrangères existent déjà dans la base';
    }

    public function up(Schema $schema): void
    {
        // Rien à faire : la base contient déjà ces changements.
    }

    public function down(Schema $schema): void
    {
        // Rien à faire.
    }
}