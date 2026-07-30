<?php

namespace App\Tests\Catalogue;

use App\Catalogue\Entity\CategoriePhoto;
use App\Catalogue\Entity\Photo;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PhotoControllerTest extends WebTestCase
{
    private const string PNG_UN_PIXEL_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    private function creerFichierImageTemporaire(): string
    {
        $chemin = sys_get_temp_dir().'/'.uniqid('image-test-', true).'.png';
        file_put_contents($chemin, base64_decode(self::PNG_UN_PIXEL_BASE64));

        return $chemin;
    }

    private function createAdmin(): User
    {
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User(uniqid('photo-test-', true).'@vitrineps.test', 'Test', 'Admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($passwordHasher->hashPassword($user, 'password1234'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createPhoto(EntityManagerInterface $entityManager): Photo
    {
        $photo = new Photo(CategoriePhoto::Portrait);
        $entityManager->persist($photo);
        $entityManager->flush();

        return $photo;
    }

    public function testCreatePhoto(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $crawler = $client->request('GET', '/admin/photos/new');
        $form = $crawler->selectButton('Enregistrer')->form([
            'photo[categorie]' => CategoriePhoto::Mariage->value,
            'photo[legende]' => 'Cérémonie au parc',
        ]);
        /** @var \Symfony\Component\DomCrawler\Field\FileFormField $champImage */
        $champImage = $form['photo[imageFichier]'];
        $champImage->upload($this->creerFichierImageTemporaire());
        $client->submit($form);

        self::assertResponseRedirects('/admin/photos');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Photo ajoutee');

        $photo = $entityManager->getRepository(Photo::class)->findOneBy(['legende' => 'Cérémonie au parc']);
        self::assertNotNull($photo);
        self::assertSame(CategoriePhoto::Mariage, $photo->getCategorie());
        self::assertNotNull($photo->getImage());
    }

    public function testEditPhoto(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $photo = $this->createPhoto($entityManager);

        $crawler = $client->request('GET', '/admin/photos/'.$photo->getId().'/edit');
        $form = $crawler->selectButton('Enregistrer')->form([
            'photo[categorie]' => CategoriePhoto::Nature->value,
            'photo[legende]' => 'Legende modifiee',
            'photo[misEnAvant]' => true,
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/photos');

        $entityManager->clear();
        $photo = $entityManager->getRepository(Photo::class)->find($photo->getId());

        self::assertSame(CategoriePhoto::Nature, $photo->getCategorie());
        self::assertSame('Legende modifiee', $photo->getLegende());
        self::assertTrue($photo->isMisEnAvant());
    }

    public function testDeletePhoto(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $photo = $this->createPhoto($entityManager);
        $id = $photo->getId();

        $crawler = $client->request('GET', '/admin/photos/'.$id);
        $client->submit($crawler->filter('form')->form());

        self::assertResponseRedirects('/admin/photos');
        self::assertNull($entityManager->getRepository(Photo::class)->find($id));
    }

    public function testEditPhotoWithImageUploadReplacesThePreviousFile(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $photo = $this->createPhoto($entityManager);
        $dossierUploads = static::getContainer()->getParameter('kernel.project_dir').'/public/uploads/photos';
        $filesystem = new Filesystem();

        $crawler = $client->request('GET', '/admin/photos/'.$photo->getId().'/edit');
        $form = $crawler->selectButton('Enregistrer')->form([
            'photo[categorie]' => CategoriePhoto::Portrait->value,
        ]);
        /** @var \Symfony\Component\DomCrawler\Field\FileFormField $champImage */
        $champImage = $form['photo[imageFichier]'];
        $champImage->upload($this->creerFichierImageTemporaire());
        $client->submit($form);

        $entityManager->clear();
        $photo = $entityManager->getRepository(Photo::class)->find($photo->getId());
        $premiereImage = $photo->getImage();

        self::assertNotNull($premiereImage);
        self::assertFileExists($dossierUploads.'/'.$premiereImage);

        $crawler = $client->request('GET', '/admin/photos/'.$photo->getId().'/edit');
        $form = $crawler->selectButton('Enregistrer')->form([
            'photo[categorie]' => CategoriePhoto::Portrait->value,
        ]);
        /** @var \Symfony\Component\DomCrawler\Field\FileFormField $champImage */
        $champImage = $form['photo[imageFichier]'];
        $champImage->upload($this->creerFichierImageTemporaire());
        $client->submit($form);

        $entityManager->clear();
        $photo = $entityManager->getRepository(Photo::class)->find($photo->getId());
        $secondeImage = $photo->getImage();

        self::assertNotSame($premiereImage, $secondeImage);
        self::assertFileDoesNotExist($dossierUploads.'/'.$premiereImage);
        self::assertFileExists($dossierUploads.'/'.$secondeImage);

        $filesystem->remove($dossierUploads.'/'.$secondeImage);
    }
}
