<?php

namespace App\Tests\User;

use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private function createUser(array $roles): User
    {
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('security-test-', true).'@vitrineps.test', 'Test', 'User');
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password1234'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function testAnonymousIsRedirectedToLoginOnAdminArea(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/login');
    }

    public function testAnonymousIsRedirectedToLoginOnCompteArea(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compte');

        self::assertResponseRedirects('/login');
    }

    public function testClientCannotAccessAdminArea(): void
    {
        $client = static::createClient();
        $user = $this->createUser(['ROLE_CLIENT']);

        $client->loginUser($user);
        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testClientCanAccessCompteArea(): void
    {
        $client = static::createClient();
        $user = $this->createUser(['ROLE_CLIENT']);

        $client->loginUser($user);
        $client->request('GET', '/compte');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCanAccessAdminArea(): void
    {
        $client = static::createClient();
        $user = $this->createUser(['ROLE_ADMIN']);

        $client->loginUser($user);
        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }

    public function testLoginFormAuthenticatesValidCredentials(): void
    {
        $client = static::createClient();
        $user = $this->createUser(['ROLE_CLIENT']);

        $client->request('GET', '/compte');
        self::assertResponseRedirects('/login');

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $user->getEmail(),
            '_password' => 'password1234',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/compte');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
