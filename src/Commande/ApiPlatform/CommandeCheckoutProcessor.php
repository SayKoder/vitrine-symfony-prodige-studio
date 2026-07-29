<?php

namespace App\Commande\ApiPlatform;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Commande\Entity\Commande;
use App\Commande\Service\CommandeManager;
use App\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProcessorInterface<CommandeCheckoutInput, Commande>
 */
class CommandeCheckoutProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly CommandeManager $commandeManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Commande
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $this->commandeManager->checkout($user, $data->codePromo);
    }
}
