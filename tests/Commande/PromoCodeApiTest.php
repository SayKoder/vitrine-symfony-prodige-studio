<?php

namespace App\Tests\Commande;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Catalogue\Entity\Prestation;
use App\Promotion\Entity\PromoCode;
use App\Promotion\Entity\TypeReductionPromoCode;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PromoCodeApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private function createUser(array $roles): User
    {
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('promo-test-', true).'@vitrineps.test', 'Test', 'User');
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

    private function createPromoCode(
        TypeReductionPromoCode $type,
        string $valeur,
        ?\DateTimeImmutable $dateDebut = null,
        ?\DateTimeImmutable $dateFin = null,
    ): PromoCode {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $promoCode = new PromoCode(
            uniqid('PROMO-', true),
            $type,
            $valeur,
            $dateDebut ?? new \DateTimeImmutable('-1 day'),
            $dateFin ?? new \DateTimeImmutable('+1 day'),
        );

        $entityManager->persist($promoCode);
        $entityManager->flush();

        return $promoCode;
    }

    public function testValiderCodePromoRetourneLeTotalApresReduction(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestation = $this->createPrestation('100.00');
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '20');

        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);

        $response = $client->request('POST', '/api/promo_codes/valider', [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'json' => ['code' => $promoCode->getCode()],
        ]);

        self::assertResponseIsSuccessful();
        $data = $response->toArray();
        self::assertSame('100.00', $data['totalAvantReduction']);
        self::assertSame('80.00', $data['totalApresReduction']);
        self::assertSame('20.00', $data['montantReduction']);
    }

    public function testValiderCodePromoInconnuRetourne400(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestation = $this->createPrestation();

        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);

        $client->request('POST', '/api/promo_codes/valider', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['code' => 'INCONNU'],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testValiderCodePromoAvecPanierVideRetourne400(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '20');

        $client->request('POST', '/api/promo_codes/valider', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['code' => $promoCode->getCode()],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testCheckoutAvecCodePromoAppliqueLaReductionEtIncrementeLesUtilisations(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestation = $this->createPrestation('100.00');
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::MontantFixe, '30.00');

        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);

        $response = $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'json' => ['codePromo' => $promoCode->getCode()],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertSame('70.00', $response->toArray()['total']);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $promoCodeRecharge = $entityManager->getRepository(PromoCode::class)->find($promoCode->getId());
        self::assertSame(1, $promoCodeRecharge->getNombreUtilisationsActuelles());
    }

    public function testCheckoutRejetteUnCodePromoExpireEtNeViDePasLePanier(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));
        $prestation = $this->createPrestation('100.00');
        $promoCode = $this->createPromoCode(
            TypeReductionPromoCode::Pourcentage,
            '10',
            new \DateTimeImmutable('-10 days'),
            new \DateTimeImmutable('-1 day'),
        );

        $client->request('POST', '/api/ligne_paniers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['prestation' => '/api/prestations/'.$prestation->getId(), 'quantite' => 1],
        ]);

        $client->request('POST', '/api/commandes', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['codePromo' => $promoCode->getCode()],
        ]);

        self::assertResponseStatusCodeSame(400);

        $panier = $client->request('GET', '/api/paniers/mine', ['headers' => ['Accept' => 'application/json']])->toArray();
        self::assertCount(1, $panier['lignesPanier']);
    }
}
