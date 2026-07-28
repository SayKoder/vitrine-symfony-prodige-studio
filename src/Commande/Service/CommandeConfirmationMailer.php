<?php

namespace App\Commande\Service;

use App\Commande\Entity\Commande;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class CommandeConfirmationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(MAILER_EXPEDITEUR)%')] private readonly string $expediteur,
    ) {
    }

    public function envoyerConfirmation(Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->expediteur, 'Prodige Studio'))
            ->to($commande->getUser()->getEmail())
            ->subject('Confirmation de votre commande Prodige Studio')
            ->htmlTemplate('emails/commande_confirmation.html.twig')
            ->context(['commande' => $commande]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
        }
    }
}
