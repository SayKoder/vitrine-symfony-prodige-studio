<?php

namespace App\Commande\Entity;

enum StatutCommande: string
{
    case Brouillon = 'brouillon';
    case Validee = 'validee';
    case Payee = 'payee';
    case Annulee = 'annulee';
}
