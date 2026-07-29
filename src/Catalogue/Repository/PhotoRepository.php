<?php

namespace App\Catalogue\Repository;

use App\Catalogue\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /**
     * @return Photo[]
     */
    public function pourGalerie(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Photo[]
     */
    public function misesEnAvant(int $limite): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.misEnAvant = true')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }
}
