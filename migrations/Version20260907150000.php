<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260907150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le point focal de cadrage (X/Y en pourcentage) aux entites Photo et Prestation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photo ADD point_focal_x SMALLINT DEFAULT 50 NOT NULL');
        $this->addSql('ALTER TABLE photo ADD point_focal_y SMALLINT DEFAULT 50 NOT NULL');
        $this->addSql('ALTER TABLE prestation ADD point_focal_x SMALLINT DEFAULT 50 NOT NULL');
        $this->addSql('ALTER TABLE prestation ADD point_focal_y SMALLINT DEFAULT 50 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photo DROP point_focal_x');
        $this->addSql('ALTER TABLE photo DROP point_focal_y');
        $this->addSql('ALTER TABLE prestation DROP point_focal_x');
        $this->addSql('ALTER TABLE prestation DROP point_focal_y');
    }
}
