<?php

namespace App\Commande\ApiPlatform;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Commande\Entity\Panier;
use App\Commande\Service\PanierManager;
use App\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProviderInterface<Panier>
 */
class PanierMineProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly PanierManager $panierManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Panier
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $this->panierManager->getOrCreateForUser($user);
    }
}
