<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728142323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le champ mis_en_avant sur prestation, utilise par la vitrine publique pour le badge populaire.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE prestation ADD mis_en_avant BOOLEAN NOT NULL DEFAULT false");
        $this->addSql('ALTER TABLE prestation ALTER COLUMN mis_en_avant DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prestation DROP mis_en_avant');
    }
}
