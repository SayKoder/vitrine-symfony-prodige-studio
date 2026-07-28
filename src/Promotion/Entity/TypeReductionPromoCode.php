<?php

namespace App\Promotion\Entity;

enum TypeReductionPromoCode: string
{
    case Pourcentage = 'pourcentage';
    case MontantFixe = 'montant_fixe';
}
