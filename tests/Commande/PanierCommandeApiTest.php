<?php

namespace App\Tests\Commande;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Catalogue\Entity\Prestation;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PanierCommandeApiTest extends ApiTestCase
{
    use MailerAssertionsTrait;

    protected static ?bool $alwaysBootKernel = true;

    private function createUser(array $roles): User
    {
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('panier-test-', true).'@vitrineps.test', 'Test', 'User');
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password1234'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createPrestation(string $prix = '100.00'): Prestation
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $prestation = new Prestation('Prestation de test', 'Description', $prix);
        $entityManager->persist($prestation);
        $entityManager->flush();

        return $prestation;
    }

    public function testMinePanierIsCreatedLazilyForCurrentUser(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));

        $response = $client->request('GET', '/api/paniers/mine', ['headers' => ['Accept' => 'application/json']]);

        self::assertResponseIsSuccessful();
        self::assertSame([], $response->toArray()['lignesPanier']);
    }

    public function testAddingLigneToPanier(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestation = $this->createPrestation('120.00');

        $response = $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 2],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertSame(2, $response->toArray()['quantite']);

        $panier = $client->request('GET', '/api/paniers/mine', ['headers' => ['Accept' => 'application/json']])->toArray();
        self::assertCount(1, $panier['lignesPanier']);
    }

    public function testCheckoutCreatesCommandeAndEmptiesPanier(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestation = $this->createPrestation('50.00');

        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 3],
        ]);

        $response = $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'json' => [],
        ]);

        self::assertResponseStatusCodeSame(201);
        $commande = $response->toArray();
        self::assertSame('150.00', $commande['total']);
        self::assertSame('validee', $commande['statut']);

        $panier = $client->request('GET', '/api/paniers/mine', ['headers' => ['Accept' => 'application/json']])->toArray();
        self::assertSame([], $panier['lignesPanier']);
    }

    public function testCheckoutSendsConfirmationEmailToUser(): void
    {
        $client = self::createClient();
        $user = $this->createUser(['ROLE_CLIENT']);
        $client->loginUser($user);
        $prestation = $this->createPrestation('80.00');

        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);

        $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertEmailCount(1);

        $email = self::getMailerMessage(0);
        self::assertEmailHeaderSame($email, 'To', $user->getEmail());
        self::assertEmailHeaderSame($email, 'Subject', 'Confirmation de votre commande Prodige Studio');
        self::assertEmailHtmlBodyContains($email, '80.00');
    }

    public function testCheckoutWithEmptyPanierReturns400(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));

        $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testUserCannotAccessAnotherUsersPanier(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $ownPanierId = $client->request('GET', '/api/paniers/mine', ['headers' => ['Accept' => 'application/json']])->toArray()['id'];

        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $client->request('GET', '/api/paniers/'.$ownPanierId, ['headers' => ['Accept' => 'application/json']]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testCommandeCollectionOnlyShowsOwnOrdersForClient(): void
    {
        $client = self::createClient();
        $userA = $this->createUser(['ROLE_CLIENT']);
        $client->loginUser($userA);
        $prestation = $this->createPrestation('75.00');
        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);
        $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);

        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $response = $client->request('GET', '/api/commandes', ['headers' => ['Accept' => 'application/json']]);

        self::assertSame([], $response->toArray());
    }

    public function testAdminSeesAllCommandes(): void
    {
        $client = self::createClient();
        $clientUser = $this->createUser(['ROLE_CLIENT']);
        $client->loginUser($clientUser);
        $prestation = $this->createPrestation('60.00');
        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);
        $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);

        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $response = $client->request('GET', '/api/commandes', ['headers' => ['Accept' => 'application/json']]);

        self::assertGreaterThanOrEqual(1, \count($response->toArray()));
    }

    public function testCannotReassignPrestationOnLignePanierThroughPatch(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestationInitiale = $this->createPrestation('80.00');
        $prestationCiblee = $this->createPrestation('999.00');

        $created = $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestationInitiale->getId(), 'quantite' => 1],
        ])->toArray();

        $response = $client->request('PATCH', '/api/ligne_paniers/'.$created['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json', 'Accept' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestationCiblee->getId(), 'quantite' => 4],
        ]);

        self::assertResponseIsSuccessful();
        $data = $response->toArray();
        self::assertSame(4, $data['quantite']);
        self::assertSame($prestationInitiale->getId(), $data['prestation']['id']);
    }
}
