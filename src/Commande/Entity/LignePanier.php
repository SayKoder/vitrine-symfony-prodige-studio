<?php

namespace App\Commande\Entity;

use App\Catalogue\Entity\Prestation;
use App\Commande\Repository\LignePanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LignePanierRepository::class)]
class LignePanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: 'lignesPanier')]
    #[ORM\JoinColumn(nullable: false)]
    private Panier $panier;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Prestation $prestation;

    #[ORM\Column]
    private int $quantite;

    public function __construct(Panier $panier, Prestation $prestation, int $quantite = 1)
    {
        $this->panier = $panier;
        $this->prestation = $prestation;
        $this->quantite = $quantite;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanier(): Panier
    {
        return $this->panier;
    }

    public function setPanier(Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }

    public function getPrestation(): Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(Prestation $prestation): static
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }
}
