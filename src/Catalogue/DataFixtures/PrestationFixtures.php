<?php

namespace App\Catalogue\DataFixtures;

use App\Catalogue\Entity\Prestation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PrestationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $seance = new Prestation(
            'Seance portrait studio',
            'Seance photo en studio, une heure, dix photos retouchees livrees.',
            '150.00',
        );
        $seance->setDureeMinutes(60);
        $manager->persist($seance);

        $mariage = new Prestation(
            'Reportage mariage journee complete',
            'Couverture photo et video de la ceremonie a la soiree.',
            '1200.00',
        );
        $mariage->setDureeMinutes(600);
        $manager->persist($mariage);

        $entreprise = new Prestation(
            'Shooting corporate',
            'Photos d\'equipe et portraits professionnels pour une entreprise.',
            '350.00',
        );
        $entreprise->setDureeMinutes(120);
        $entreprise->setMisEnAvant(true);
        $manager->persist($entreprise);

        $manager->flush();
    }
}
