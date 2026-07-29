<?php

namespace App\Catalogue\Service;

use App\Catalogue\Entity\Prestation;

class PrestationEnUsageException extends \RuntimeException
{
    public function __construct(private readonly Prestation $prestation, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'La prestation "%s" est utilisee dans au moins une commande ou un panier et ne peut pas etre supprimee.',
                $prestation->getNom(),
            ),
            previous: $previous,
        );
    }

    public function getPrestation(): Prestation
    {
        return $this->prestation;
    }
}
