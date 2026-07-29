<?php

namespace App\Commande\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Commande\Entity\Commande;
use App\Commande\Entity\LigneCommande;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OwnedByCurrentUserExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Commande::class !== $resourceClass && LigneCommande::class !== $resourceClass) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (Commande::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.user = :current_user', $rootAlias))
                ->setParameter('current_user', $user);

            return;
        }

        $commandeAlias = $queryNameGenerator->generateJoinAlias('commande');
        $queryBuilder
            ->join(sprintf('%s.commande', $rootAlias), $commandeAlias)
            ->andWhere(sprintf('%s.user = :current_user', $commandeAlias))
            ->setParameter('current_user', $user);
    }
}
