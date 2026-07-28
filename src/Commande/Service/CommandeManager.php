<?php

namespace App\Commande\Service;

use App\Commande\Entity\Commande;
use App\Commande\Entity\LigneCommande;
use App\Commande\Entity\StatutCommande;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CommandeManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PanierManager $panierManager,
    ) {
    }

    public function checkout(User $user): Commande
    {
        $panier = $this->panierManager->getOrCreateForUser($user);

        if ($panier->getLignesPanier()->isEmpty()) {
            throw new PanierVideException();
        }

        $commande = new Commande($user, '0.00');
        $total = '0.00';

        foreach ($panier->getLignesPanier() as $lignePanier) {
            $prestation = $lignePanier->getPrestation();
            $ligneCommande = new LigneCommande(
                $commande,
                $prestation,
                $lignePanier->getQuantite(),
                $prestation->getPrix(),
            );
            $commande->addLigneCommande($ligneCommande);

            $sousTotal = bcmul($prestation->getPrix(), (string) $lignePanier->getQuantite(), 2);
            $total = bcadd($total, $sousTotal, 2);
        }

        $commande->setTotal($total);
        $commande->setStatut(StatutCommande::Validee);

        $this->entityManager->persist($commande);
        $this->panierManager->clear($panier);
        $this->entityManager->flush();

        return $commande;
    }
}
