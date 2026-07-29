<?php

namespace App\Commande\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Catalogue\Entity\Prestation;
use App\Commande\ApiPlatform\LignePanierCreateProcessor;
use App\Commande\ApiPlatform\LignePanierInput;
use App\Commande\Repository\LignePanierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            processor: LignePanierCreateProcessor::class,
            input: LignePanierInput::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Get(security: "is_granted('ROLE_ADMIN') or object.getPanier().getUser() == user"),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or object.getPanier().getUser() == user",
            denormalizationContext: ['groups' => ['ligne_panier:write']],
        ),
        new Delete(security: "is_granted('ROLE_ADMIN') or object.getPanier().getUser() == user"),
    ],
    normalizationContext: ['groups' => ['panier:read', 'prestation:read']],
)]
#[ORM\Entity(repositoryClass: LignePanierRepository::class)]
class LignePanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['panier:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: 'lignesPanier')]
    #[ORM\JoinColumn(nullable: false)]
    private Panier $panier;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['panier:read'])]
    private Prestation $prestation;

    #[ORM\Column]
    #[Assert\Positive]
    #[Groups(['panier:read', 'ligne_panier:write'])]
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
