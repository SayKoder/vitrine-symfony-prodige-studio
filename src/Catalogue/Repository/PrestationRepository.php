<?php

namespace App\Catalogue\Repository;

use App\Catalogue\Entity\Prestation;
use App\Catalogue\Service\ResultatCatalogue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prestation>
 */
class PrestationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prestation::class);
    }

    /**
     * @return Prestation[]
     */
    public function trouverActivesPourVitrine(int $limite): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.actif = true')
            ->orderBy('p.prix', 'ASC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function rechercherPourCatalogue(?string $nom, ?string $prixMin, ?string $prixMax, int $page, int $parPage): ResultatCatalogue
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.actif = true')
            ->orderBy('p.prix', 'ASC');

        if (null !== $nom && '' !== $nom) {
            $queryBuilder
                ->andWhere('p.nom LIKE :nom')
                ->setParameter('nom', '%'.$nom.'%');
        }

        if (null !== $prixMin && '' !== $prixMin) {
            $queryBuilder
                ->andWhere('p.prix >= :prixMin')
                ->setParameter('prixMin', $prixMin);
        }

        if (null !== $prixMax && '' !== $prixMax) {
            $queryBuilder
                ->andWhere('p.prix <= :prixMax')
                ->setParameter('prixMax', $prixMax);
        }

        $queryBuilder
            ->setFirstResult(($page - 1) * $parPage)
            ->setMaxResults($parPage);

        $paginator = new Paginator($queryBuilder);

        return new ResultatCatalogue(
            iterator_to_array($paginator->getIterator()),
            $paginator->count(),
            $page,
            $parPage,
        );
    }
}
