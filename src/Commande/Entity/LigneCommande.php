<?php

namespace App\Commande\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Catalogue\Entity\Prestation;
use App\Commande\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object.getCommande().getUser() == user"),
    ],
    normalizationContext: ['groups' => ['commande:read', 'prestation:read']],
)]
#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commande:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'lignesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    private Commande $commande;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['commande:read'])]
    private Prestation $prestation;

    #[ORM\Column]
    #[Groups(['commande:read'])]
    private int $quantite;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['commande:read'])]
    private string $prixUnitaire;

    public function __construct(Commande $commande, Prestation $prestation, int $quantite, string $prixUnitaire)
    {
        $this->commande = $commande;
        $this->prestation = $prestation;
        $this->quantite = $quantite;
        $this->prixUnitaire = $prixUnitaire;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): Commande
    {
        return $this->commande;
    }

    public function setCommande(Commande $commande): static
    {
        $this->commande = $commande;

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

    public function getPrixUnitaire(): string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }
}
