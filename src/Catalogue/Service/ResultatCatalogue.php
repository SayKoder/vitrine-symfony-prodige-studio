<?php

namespace App\Catalogue\Service;

use App\Catalogue\Entity\Prestation;

final class ResultatCatalogue
{
    /**
     * @param Prestation[] $prestations
     */
    public function __construct(
        public readonly array $prestations,
        public readonly int $total,
        public readonly int $page,
        public readonly int $parPage,
    ) {
    }

    public function nombreDePages(): int
    {
        return (int) max(1, ceil($this->total / $this->parPage));
    }
}
