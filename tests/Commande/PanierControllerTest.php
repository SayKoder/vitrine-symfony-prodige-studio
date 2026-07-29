<?php

namespace App\Tests\Commande;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PanierControllerTest extends WebTestCase
{
    /**
     * @param array<int, string> $roles
     */
    private function createUser(array $roles): User
    {
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('panier-page-test-', true).'@vitrineps.test', 'Test', 'User');
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password1234'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function testAnonymousIsRedirectedToLoginOnPanierPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/panier');

        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanAccessPanierPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));

        $crawler = $client->request('GET', '/panier');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-controller="panier"]');
    }
}
