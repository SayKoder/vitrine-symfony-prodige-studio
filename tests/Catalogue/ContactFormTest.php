<?php

namespace App\Tests\Catalogue;

use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactFormTest extends WebTestCase
{
    use MailerAssertionsTrait;

    public function testValidSubmissionSendsEmailAndRedirects(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('ENVOYER LE MESSAGE')->form([
            'contact[nom]' => 'Camille Test',
            'contact[email]' => 'camille@example.test',
            'contact[telephone]' => '06 40 27 77 16',
            'contact[sujet]' => 'Mariage',
            'contact[message]' => 'Nous cherchons un photographe pour notre mariage en septembre prochain.',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/contact');

        self::assertEmailCount(1);
        $email = self::getMailerMessage(0);
        self::assertEmailHeaderSame($email, 'Reply-To', 'Camille Test <camille@example.test>');
        self::assertEmailHtmlBodyContains($email, 'Mariage');

        $client->followRedirect();
        self::assertSelectorTextContains('.flash-message--succes', 'Merci');
    }

    public function testInvalidSubmissionShowsErrorsAndSendsNoEmail(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('ENVOYER LE MESSAGE')->form([
            'contact[nom]' => 'A',
            'contact[email]' => 'pas-un-email',
            'contact[sujet]' => 'Mariage',
            'contact[message]' => 'Trop court',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
    }

    public function testInvalidPhoneNumberIsRejected(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('ENVOYER LE MESSAGE')->form([
            'contact[nom]' => 'Camille Test',
            'contact[email]' => 'camille@example.test',
            'contact[telephone]' => 'pas-un-numero',
            'contact[sujet]' => 'Mariage',
            'contact[message]' => 'Nous cherchons un photographe pour notre mariage en septembre prochain.',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
    }

    public function testHoneypotFilledSilentlyDropsMessage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('ENVOYER LE MESSAGE')->form([
            'contact[nom]' => 'Robot Test',
            'contact[email]' => 'robot@example.test',
            'contact[sujet]' => 'Autre',
            'contact[message]' => 'Ceci est un message automatise envoye par un robot de test.',
            'contact[siteWeb]' => 'http://spam.example',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/contact');
        self::assertEmailCount(0);
    }
}
