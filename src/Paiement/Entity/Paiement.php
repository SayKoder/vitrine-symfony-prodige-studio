<?php

namespace App\Paiement\Entity;

use App\Commande\Entity\Commande;
use App\Paiement\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private Commande $commande;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montant;

    #[ORM\Column(enumType: StatutPaiement::class)]
    private StatutPaiement $statut;

    #[ORM\Column(length: 50)]
    private string $moyenPaiement;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceExterne = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Commande $commande, string $montant, string $moyenPaiement)
    {
        $this->commande = $commande;
        $this->montant = $montant;
        $this->moyenPaiement = $moyenPaiement;
        $this->statut = StatutPaiement::EnAttente;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): Commande
    {
        return $this->commande;
    }

    public function getMontant(): string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getStatut(): StatutPaiement
    {
        return $this->statut;
    }

    public function setStatut(StatutPaiement $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMoyenPaiement(): string
    {
        return $this->moyenPaiement;
    }

    public function setMoyenPaiement(string $moyenPaiement): static
    {
        $this->moyenPaiement = $moyenPaiement;

        return $this;
    }

    public function getReferenceExterne(): ?string
    {
        return $this->referenceExterne;
    }

    public function setReferenceExterne(?string $referenceExterne): static
    {
        $this->referenceExterne = $referenceExterne;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
