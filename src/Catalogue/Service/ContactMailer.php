<?php

namespace App\Catalogue\Service;

use App\Catalogue\Form\ContactMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ContactMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(MAILER_EXPEDITEUR)%')] private readonly string $adresseStudio,
    ) {
    }

    public function envoyerMessage(ContactMessage $contactMessage): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->adresseStudio, 'Site Prodige Studio'))
            ->to($this->adresseStudio)
            ->replyTo(new Address($contactMessage->email, $contactMessage->nom))
            ->subject(\sprintf('Nouveau message du site : %s', $contactMessage->sujet))
            ->htmlTemplate('emails/contact_message.html.twig')
            ->context(['contact' => $contactMessage]);

        $this->mailer->send($email);
    }
}
