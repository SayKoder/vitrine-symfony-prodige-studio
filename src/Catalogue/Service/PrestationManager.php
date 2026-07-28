<?php

namespace App\Catalogue\Service;

use App\Catalogue\Entity\Prestation;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class PrestationManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function save(Prestation $prestation): void
    {
        $this->entityManager->persist($prestation);
        $this->entityManager->flush();
    }

    public function delete(Prestation $prestation): void
    {
        try {
            $this->entityManager->remove($prestation);
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $exception) {
            throw new PrestationEnUsageException($prestation, $exception);
        }
    }
}
