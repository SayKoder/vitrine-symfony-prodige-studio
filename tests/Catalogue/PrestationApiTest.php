<?php

namespace App\Tests\Catalogue;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Catalogue\Entity\Prestation;
use Doctrine\ORM\EntityManagerInterface;

class PrestationApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private function createPrestation(EntityManagerInterface $entityManager, string $nom, string $prix, bool $actif = true): Prestation
    {
        $prestation = new Prestation($nom, 'Description de '.$nom, $prix);
        $prestation->setActif($actif);
        $entityManager->persist($prestation);
        $entityManager->flush();

        return $prestation;
    }

    public function testCollectionOnlyReturnsActivePrestations(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->createPrestation($entityManager, 'Prestation active visible', '100.00');
        $inactive = $this->createPrestation($entityManager, 'Prestation masquee', '100.00', false);

        $response = $client->request('GET', '/api/prestations', ['headers' => ['Accept' => 'application/json']]);

        self::assertResponseIsSuccessful();
        $noms = array_column($response->toArray(), 'nom');
        self::assertContains('Prestation active visible', $noms);
        self::assertNotContains($inactive->getNom(), $noms);
    }

    public function testGetItemReturnsPrestation(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $prestation = $this->createPrestation($entityManager, 'Seance de test API', '250.00');

        $response = $client->request('GET', '/api/prestations/'.$prestation->getId(), ['headers' => ['Accept' => 'application/json']]);

        self::assertResponseIsSuccessful();
        self::assertSame('Seance de test API', $response->toArray()['nom']);
        self::assertSame('250.00', $response->toArray()['prix']);
    }

    public function testGetItemOfInactivePrestationReturns404(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $prestation = $this->createPrestation($entityManager, 'Prestation retiree', '100.00', false);

        $client->request('GET', '/api/prestations/'.$prestation->getId());

        self::assertResponseStatusCodeSame(404);
    }

    public function testSearchFilterByNom(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->createPrestation($entityManager, 'Reportage anniversaire', '80.00');
        $this->createPrestation($entityManager, 'Portrait entreprise', '150.00');

        $response = $client->request('GET', '/api/prestations?nom=anniversaire', ['headers' => ['Accept' => 'application/json']]);

        self::assertResponseIsSuccessful();
        $noms = array_column($response->toArray(), 'nom');
        self::assertSame(['Reportage anniversaire'], $noms);
    }

    public function testWriteOperationsAreNotExposedByTheApi(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/prestations', [
            'json' => [
                'nom' => 'Injection non autorisee',
                'description' => 'Ne doit jamais etre cree',
                'prix' => '1.00',
            ],
        ]);

        self::assertResponseStatusCodeSame(405);
    }
}
