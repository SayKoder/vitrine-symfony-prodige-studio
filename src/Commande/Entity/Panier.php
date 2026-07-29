<?php

namespace App\Commande\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Commande\ApiPlatform\PanierMineProvider;
use App\Commande\Repository\PanierRepository;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/paniers/mine',
            provider: PanierMineProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object.getUser() == user"),
    ],
    normalizationContext: ['groups' => ['panier:read', 'prestation:read']],
)]
#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['panier:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private User $user;

    #[ORM\Column]
    #[Groups(['panier:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['panier:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, LignePanier>
     */
    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: LignePanier::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['panier:read'])]
    private Collection $lignesPanier;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAt = new \DateTimeImmutable();
        $this->lignesPanier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, LignePanier>
     */
    public function getLignesPanier(): Collection
    {
        return $this->lignesPanier;
    }

    public function addLignePanier(LignePanier $lignePanier): static
    {
        if (!$this->lignesPanier->contains($lignePanier)) {
            $this->lignesPanier->add($lignePanier);
            $lignePanier->setPanier($this);
        }

        return $this;
    }

    public function removeLignePanier(LignePanier $lignePanier): static
    {
        $this->lignesPanier->removeElement($lignePanier);

        return $this;
    }
}
