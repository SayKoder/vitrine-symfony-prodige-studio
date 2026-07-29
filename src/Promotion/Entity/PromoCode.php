<?php

namespace App\Promotion\Entity;

use App\Catalogue\Entity\Prestation;
use App\Promotion\Repository\PromoCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromoCodeRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_promo_code_code', columns: ['code'])]
class PromoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $code;

    #[ORM\Column(enumType: TypeReductionPromoCode::class)]
    private TypeReductionPromoCode $typeReduction;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $valeur;

    #[ORM\Column]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $montantMinimum = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreUtilisationsMax = null;

    #[ORM\Column]
    private int $nombreUtilisationsActuelles = 0;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Prestation $prestation = null;

    public function __construct(
        string $code,
        TypeReductionPromoCode $typeReduction,
        string $valeur,
        \DateTimeImmutable $dateDebut,
        \DateTimeImmutable $dateFin,
    ) {
        $this->code = $code;
        $this->typeReduction = $typeReduction;
        $this->valeur = $valeur;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getTypeReduction(): TypeReductionPromoCode
    {
        return $this->typeReduction;
    }

    public function setTypeReduction(TypeReductionPromoCode $typeReduction): static
    {
        $this->typeReduction = $typeReduction;

        return $this;
    }

    public function getValeur(): string
    {
        return $this->valeur;
    }

    public function setValeur(string $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getDateDebut(): \DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): \DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getMontantMinimum(): ?string
    {
        return $this->montantMinimum;
    }

    public function setMontantMinimum(?string $montantMinimum): static
    {
        $this->montantMinimum = $montantMinimum;

        return $this;
    }

    public function getNombreUtilisationsMax(): ?int
    {
        return $this->nombreUtilisationsMax;
    }

    public function setNombreUtilisationsMax(?int $nombreUtilisationsMax): static
    {
        $this->nombreUtilisationsMax = $nombreUtilisationsMax;

        return $this;
    }

    public function getNombreUtilisationsActuelles(): int
    {
        return $this->nombreUtilisationsActuelles;
    }

    public function setNombreUtilisationsActuelles(int $nombreUtilisationsActuelles): static
    {
        $this->nombreUtilisationsActuelles = $nombreUtilisationsActuelles;

        return $this;
    }

    public function getPrestation(): ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestation $prestation): static
    {
        $this->prestation = $prestation;

        return $this;
    }
}
