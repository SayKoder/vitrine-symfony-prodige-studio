<?php

namespace App\Catalogue\Entity;

enum CategoriePhoto: string
{
    case Portrait = 'portrait';
    case Mariage = 'mariage';
    case Corporate = 'corporate';
    case Nature = 'nature';
    case Evenement = 'evenement';

    public function libelle(): string
    {
        return match ($this) {
            self::Portrait => 'Portrait',
            self::Mariage => 'Mariage',
            self::Corporate => 'Corporate',
            self::Nature => 'Nature',
            self::Evenement => 'Evenement',
        };
    }
}
