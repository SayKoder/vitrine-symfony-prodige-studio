<?php

namespace App\Tests\Commande;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TunnelControllerTest extends WebTestCase
{
    private function createUser(array $roles): User
    {
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('tunnel-page-test-', true).'@vitrineps.test', 'Test', 'User');
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password1234'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function testAnonymousIsRedirectedToLoginOnTunnelPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tunnel');

        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanAccessTunnelPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_CLIENT']));

        $client->request('GET', '/tunnel');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-controller="tunnel"]');
    }
}
