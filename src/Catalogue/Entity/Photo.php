<?php

namespace App\Catalogue\Entity;

use App\Catalogue\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: CategoriePhoto::class)]
    private CategoriePhoto $categorie;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $legende = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private bool $misEnAvant = false;

    #[ORM\Column(type: 'smallint', options: ['default' => 50])]
    private int $pointFocalX = 50;

    #[ORM\Column(type: 'smallint', options: ['default' => 50])]
    private int $pointFocalY = 50;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(CategoriePhoto $categorie)
    {
        $this->categorie = $categorie;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): CategoriePhoto
    {
        return $this->categorie;
    }

    public function setCategorie(CategoriePhoto $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function setLegende(?string $legende): static
    {
        $this->legende = $legende;

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

    public function isMisEnAvant(): bool
    {
        return $this->misEnAvant;
    }

    public function setMisEnAvant(bool $misEnAvant): static
    {
        $this->misEnAvant = $misEnAvant;

        return $this;
    }

    public function getPointFocalX(): int
    {
        return $this->pointFocalX;
    }

    public function setPointFocalX(?int $pointFocalX): static
    {
        $this->pointFocalX = null === $pointFocalX ? 50 : max(0, min(100, $pointFocalX));

        return $this;
    }

    public function getPointFocalY(): int
    {
        return $this->pointFocalY;
    }

    public function setPointFocalY(?int $pointFocalY): static
    {
        $this->pointFocalY = null === $pointFocalY ? 50 : max(0, min(100, $pointFocalY));

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
