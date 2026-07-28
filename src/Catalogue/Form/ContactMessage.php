<?php

namespace App\Catalogue\Form;

use Symfony\Component\Validator\Constraints as Assert;

class ContactMessage
{
    #[Assert\NotBlank(message: 'Merci de renseigner votre nom.')]
    #[Assert\Length(min: 2, max: 100)]
    public string $nom = '';

    #[Assert\NotBlank(message: 'Merci de renseigner votre e-mail.')]
    #[Assert\Email(message: 'Cette adresse e-mail n\'est pas valide.')]
    public string $email = '';

    #[Assert\Regex(
        pattern: '/^(?:\+33|0)[1-9](?:[\s.-]?\d{2}){4}$/',
        message: 'Ce numero de telephone n\'est pas valide.',
    )]
    public ?string $telephone = null;

    #[Assert\NotBlank(message: 'Merci de preciser le sujet de votre demande.')]
    #[Assert\Choice(choices: ['Portrait', 'Mariage', 'Corporate', 'Evenement', 'Autre'])]
    public string $sujet = '';

    #[Assert\NotBlank(message: 'Merci de decrire votre projet.')]
    #[Assert\Length(min: 20, max: 2000)]
    public string $message = '';

    public ?string $siteWeb = null;
}
