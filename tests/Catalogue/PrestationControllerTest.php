<?php

namespace App\Tests\Catalogue;

use App\Catalogue\Entity\Prestation;
use App\Commande\Entity\Commande;
use App\Commande\Entity\LigneCommande;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PrestationControllerTest extends WebTestCase
{
    private function createAdmin(): User
    {
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('prestation-test-', true).'@vitrineps.test', 'Test', 'Admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($passwordHasher->hashPassword($user, 'password1234'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createPrestation(EntityManagerInterface $entityManager): Prestation
    {
        $prestation = new Prestation('Seance test', 'Description test', '100.00');
        $entityManager->persist($prestation);
        $entityManager->flush();

        return $prestation;
    }

    public function testCreatePrestation(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $crawler = $client->request('GET', '/admin/prestations/new');
        $form = $crawler->selectButton('Enregistrer')->form([
            'prestation[nom]' => 'Nouvelle seance',
            'prestation[description]' => 'Une description',
            'prestation[prix]' => '199.00',
            'prestation[dureeMinutes]' => '90',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/prestations');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Nouvelle seance');
    }

    public function testEditPrestation(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $prestation = $this->createPrestation($entityManager);

        $crawler = $client->request('GET', '/admin/prestations/'.$prestation->getId().'/edit');
        $form = $crawler->selectButton('Enregistrer')->form([
            'prestation[nom]' => 'Seance renommee',
            'prestation[description]' => 'Description test',
            'prestation[prix]' => '120.00',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/prestations');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Seance renommee');
    }

    public function testDeletePrestationWithoutOrders(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $prestation = $this->createPrestation($entityManager);
        $id = $prestation->getId();

        $crawler = $client->request('GET', '/admin/prestations/'.$id);
        $client->submit($crawler->filter('form')->form());

        self::assertResponseRedirects('/admin/prestations');
        $client->followRedirect();
        self::assertNull($entityManager->getRepository(Prestation::class)->find($id));
    }

    public function testDeletePrestationInUseIsBlocked(): void
    {
        $client = static::createClient();
        $client->loginUser($admin = $this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $prestation = $this->createPrestation($entityManager);

        $commande = new Commande($admin, '100.00');
        $entityManager->persist($commande);

        $ligneCommande = new LigneCommande($commande, $prestation, 1, '100.00');
        $entityManager->persist($ligneCommande);
        $entityManager->flush();

        $id = $prestation->getId();
        $crawler = $client->request('GET', '/admin/prestations/'.$id);
        $client->submit($crawler->filter('form')->form());

        self::assertResponseRedirects('/admin/prestations');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-error', 'ne peut pas etre supprimee');
        self::assertNotNull($entityManager->getRepository(Prestation::class)->find($id));
    }
}
