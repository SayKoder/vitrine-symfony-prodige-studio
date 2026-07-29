<?php

namespace App\Paiement\Entity;

enum StatutPaiement: string
{
    case EnAttente = 'en_attente';
    case Reussi = 'reussi';
    case Echoue = 'echoue';
}
