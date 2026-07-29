<?php

namespace App\Commande\ApiPlatform;

use App\Catalogue\Entity\Prestation;
use Symfony\Component\Validator\Constraints as Assert;

class LignePanierInput
{
    #[Assert\NotNull]
    public ?Prestation $prestation = null;

    #[Assert\Positive]
    public int $quantite = 1;
}
