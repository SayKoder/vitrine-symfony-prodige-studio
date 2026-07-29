<?php

namespace App\Tests\Promotion;

use App\Catalogue\Entity\Prestation;
use App\Promotion\Entity\PromoCode;
use App\Promotion\Entity\TypeReductionPromoCode;
use App\Promotion\Repository\PromoCodeRepository;
use App\Promotion\Service\PromoCodeInvalideException;
use App\Promotion\Service\PromoCodeManager;
use PHPUnit\Framework\TestCase;

class PromoCodeManagerTest extends TestCase
{
    private function createPromoCode(
        TypeReductionPromoCode $type,
        string $valeur,
        ?string $montantMinimum = null,
        ?int $nombreUtilisationsMax = null,
        int $nombreUtilisationsActuelles = 0,
        ?Prestation $prestation = null,
        ?\DateTimeImmutable $dateDebut = null,
        ?\DateTimeImmutable $dateFin = null,
    ): PromoCode {
        $promoCode = new PromoCode(
            'CODE'.uniqid(),
            $type,
            $valeur,
            $dateDebut ?? new \DateTimeImmutable('-1 day'),
            $dateFin ?? new \DateTimeImmutable('+1 day'),
        );
        $promoCode->setMontantMinimum($montantMinimum);
        $promoCode->setNombreUtilisationsMax($nombreUtilisationsMax);
        $promoCode->setNombreUtilisationsActuelles($nombreUtilisationsActuelles);
        $promoCode->setPrestation($prestation);

        return $promoCode;
    }

    private function createManager(?PromoCode $trouve = null): PromoCodeManager
    {
        $repository = $this->createStub(PromoCodeRepository::class);
        $repository->method('findOneBy')->willReturn($trouve);

        return new PromoCodeManager($repository);
    }

    public function testCalculerTotalApresReductionPourcentage(): void
    {
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '10');

        $total = $this->createManager()->calculerTotalApresReduction('200.00', $promoCode);

        self::assertSame('180.00', $total);
    }

    public function testCalculerTotalApresReductionMontantFixe(): void
    {
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::MontantFixe, '15.00');

        $total = $this->createManager()->calculerTotalApresReduction('100.00', $promoCode);

        self::assertSame('85.00', $total);
    }

    public function testCalculerTotalApresReductionNePeutPasEtreNegatif(): void
    {
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::MontantFixe, '50.00');

        $total = $this->createManager()->calculerTotalApresReduction('10.00', $promoCode);

        self::assertSame('0.00', $total);
    }

    public function testVerifierValiditeRejetteUnCodeExpire(): void
    {
        $promoCode = $this->createPromoCode(
            TypeReductionPromoCode::Pourcentage,
            '10',
            dateDebut: new \DateTimeImmutable('-10 days'),
            dateFin: new \DateTimeImmutable('-1 day'),
        );

        $this->expectException(PromoCodeInvalideException::class);
        $this->createManager()->verifierValidite($promoCode, '100.00', []);
    }

    public function testVerifierValiditeRejetteUnCodePasEncoreActif(): void
    {
        $promoCode = $this->createPromoCode(
            TypeReductionPromoCode::Pourcentage,
            '10',
            dateDebut: new \DateTimeImmutable('+1 day'),
            dateFin: new \DateTimeImmutable('+10 days'),
        );

        $this->expectException(PromoCodeInvalideException::class);
        $this->createManager()->verifierValidite($promoCode, '100.00', []);
    }

    public function testVerifierValiditeRejetteSiMontantMinimumNonAtteint(): void
    {
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '10', montantMinimum: '50.00');

        $this->expectException(PromoCodeInvalideException::class);
        $this->createManager()->verifierValidite($promoCode, '49.99', []);
    }

    public function testVerifierValiditeAccepteSiMontantMinimumAtteint(): void
    {
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '10', montantMinimum: '50.00');

        $this->createManager()->verifierValidite($promoCode, '50.00', []);

        self::assertTrue(true);
    }

    public function testVerifierValiditeRejetteSiNombreUtilisationsMaxAtteint(): void
    {
        $promoCode = $this->createPromoCode(
            TypeReductionPromoCode::Pourcentage,
            '10',
            nombreUtilisationsMax: 5,
            nombreUtilisationsActuelles: 5,
        );

        $this->expectException(PromoCodeInvalideException::class);
        $this->createManager()->verifierValidite($promoCode, '100.00', []);
    }

    public function testVerifierValiditeRejetteSiPrestationRestreinteAbsenteDuPanier(): void
    {
        $prestationRestreinte = new Prestation('Reportage mariage', 'Description', '1200.00');
        $prestationDuPanier = new Prestation('Portrait studio', 'Description', '150.00');
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '10', prestation: $prestationRestreinte);

        $this->expectException(PromoCodeInvalideException::class);
        $this->createManager()->verifierValidite($promoCode, '150.00', [$prestationDuPanier]);
    }

    public function testVerifierValiditeAccepteSiPrestationRestreintePresenteDansLePanier(): void
    {
        $prestationRestreinte = new Prestation('Reportage mariage', 'Description', '1200.00');
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '10', prestation: $prestationRestreinte);

        $this->createManager()->verifierValidite($promoCode, '1200.00', [$prestationRestreinte]);

        self::assertTrue(true);
    }

    public function testTrouverParCodeRejetteUnCodeInconnu(): void
    {
        $this->expectException(PromoCodeInvalideException::class);
        $this->createManager(null)->trouverParCode('INCONNU');
    }

    public function testTrouverParCodeRetourneLePromoCodeTrouve(): void
    {
        $promoCode = $this->createPromoCode(TypeReductionPromoCode::Pourcentage, '10');

        $trouve = $this->createManager($promoCode)->trouverParCode($promoCode->getCode());

        self::assertSame($promoCode, $trouve);
    }
}
