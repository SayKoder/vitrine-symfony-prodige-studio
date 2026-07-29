<?php

namespace App\Tests\Catalogue;

use App\Catalogue\Entity\Prestation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CatalogueControllerTest extends WebTestCase
{
    private function createPrestation(EntityManagerInterface $entityManager, string $nom, string $prix, bool $actif = true): Prestation
    {
        $prestation = new Prestation($nom, 'Description de '.$nom, $prix);
        $prestation->setActif($actif);
        $entityManager->persist($prestation);
        $entityManager->flush();

        return $prestation;
    }

    public function testCataloguePagineSurSixResultatsParPage(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $prefixe = uniqid('catalogue-pagination-', true);
        for ($i = 1; $i <= 8; ++$i) {
            $this->createPrestation($entityManager, $prefixe.'-'.$i, sprintf('%d.00', 100 + $i));
        }

        $crawler = $client->request('GET', '/prestations', ['nom' => $prefixe]);

        self::assertResponseIsSuccessful();
        self::assertCount(6, $crawler->filter('.carte-forfait-nom'));
        self::assertSelectorExists('.pagination');

        $crawler = $client->request('GET', '/prestations', ['nom' => $prefixe, 'page' => 2]);

        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('.carte-forfait-nom'));
    }

    public function testCatalogueFiltreParNom(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $motCle = uniqid('unique-recherche-', true);
        $this->createPrestation($entityManager, 'Reportage '.$motCle, '80.00');
        $this->createPrestation($entityManager, 'Portrait sans rapport', '80.00');

        $crawler = $client->request('GET', '/prestations', ['nom' => $motCle]);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.carte-forfait-nom'));
    }

    public function testCatalogueFiltreParPrix(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $prefixe = uniqid('catalogue-prix-', true);
        $this->createPrestation($entityManager, $prefixe.'-petit', '50.00');
        $this->createPrestation($entityManager, $prefixe.'-grand', '500.00');

        $crawler = $client->request('GET', '/prestations', ['nom' => $prefixe, 'prixMin' => '100']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.carte-forfait-nom'));
        self::assertStringContainsString('GRAND', $crawler->filter('.carte-forfait-nom')->text());
    }

    public function testCatalogueNAfficheQueLesPrestationsActives(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $prefixe = uniqid('catalogue-actif-', true);
        $this->createPrestation($entityManager, $prefixe.'-active', '80.00', true);
        $inactive = $this->createPrestation($entityManager, $prefixe.'-inactive', '80.00', false);

        $crawler = $client->request('GET', '/prestations', ['nom' => $prefixe]);

        self::assertResponseIsSuccessful();
        $noms = $crawler->filter('.carte-forfait-nom')->each(fn ($node) => $node->text());
        self::assertNotContains(strtoupper($inactive->getNom()), $noms);
    }
}
