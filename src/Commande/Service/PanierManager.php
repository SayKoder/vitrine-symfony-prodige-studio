<?php

namespace App\Commande\Service;

use App\Catalogue\Entity\Prestation;
use App\Commande\Entity\LignePanier;
use App\Commande\Entity\Panier;
use App\Commande\Repository\PanierRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PanierManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PanierRepository $panierRepository,
    ) {
    }

    public function getOrCreateForUser(User $user): Panier
    {
        $panier = $this->panierRepository->findOneBy(['user' => $user]);

        if ($panier instanceof Panier) {
            return $panier;
        }

        $panier = new Panier($user);
        $this->entityManager->persist($panier);
        $this->entityManager->flush();

        return $panier;
    }

    public function addLigne(Panier $panier, Prestation $prestation, int $quantite): LignePanier
    {
        foreach ($panier->getLignesPanier() as $lignePanier) {
            if ($lignePanier->getPrestation() === $prestation) {
                $lignePanier->setQuantite($lignePanier->getQuantite() + $quantite);
                $this->entityManager->flush();

                return $lignePanier;
            }
        }

        $lignePanier = new LignePanier($panier, $prestation, $quantite);
        $panier->addLignePanier($lignePanier);
        $this->entityManager->persist($lignePanier);
        $this->entityManager->flush();

        return $lignePanier;
    }

    public function clear(Panier $panier): void
    {
        foreach ($panier->getLignesPanier() as $lignePanier) {
            $panier->removeLignePanier($lignePanier);
            $this->entityManager->remove($lignePanier);
        }
    }
}
