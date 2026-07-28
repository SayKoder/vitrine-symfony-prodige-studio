<?php

namespace App\Commande\Service;

class PanierVideException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Le panier est vide, impossible de creer une commande.');
    }
}
