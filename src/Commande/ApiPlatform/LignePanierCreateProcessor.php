<?php

namespace App\Commande\ApiPlatform;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Commande\Entity\LignePanier;
use App\Commande\Service\PanierManager;
use App\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LignePanierCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly PanierManager $panierManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): LignePanier
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $panier = $this->panierManager->getOrCreateForUser($user);

        return $this->panierManager->addLigne($panier, $data->prestation, $data->quantite);
    }
}
