<?php

namespace App\Catalogue\Entity;

use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Catalogue\Repository\PrestationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['prestation:read']],
    paginationItemsPerPage: 10,
)]
#[ApiFilter(SearchFilter::class, properties: ['nom' => 'partial'])]
#[ApiFilter(RangeFilter::class, properties: ['prix'])]
#[ORM\Entity(repositoryClass: PrestationRepository::class)]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['prestation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['prestation:read'])]
    private string $nom;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups(['prestation:read'])]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['prestation:read'])]
    private string $prix;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Groups(['prestation:read'])]
    private ?int $dureeMinutes = null;

    #[ORM\Column]
    #[Groups(['prestation:read'])]
    private bool $actif = true;

    #[ORM\Column]
    #[Groups(['prestation:read'])]
    private bool $misEnAvant = false;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['prestation:read'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['prestation:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $nom, string $description, string $prix)
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDureeMinutes(): ?int
    {
        return $this->dureeMinutes;
    }

    public function setDureeMinutes(?int $dureeMinutes): static
    {
        $this->dureeMinutes = $dureeMinutes;

        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function isMisEnAvant(): bool
    {
        return $this->misEnAvant;
    }

    public function setMisEnAvant(bool $misEnAvant): static
    {
        $this->misEnAvant = $misEnAvant;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
