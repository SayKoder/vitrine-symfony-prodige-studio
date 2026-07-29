<?php

namespace App\Promotion\Service;

use App\Catalogue\Entity\Prestation;
use App\Promotion\Entity\PromoCode;
use App\Promotion\Entity\TypeReductionPromoCode;
use App\Promotion\Repository\PromoCodeRepository;

class PromoCodeManager
{
    public function __construct(private readonly PromoCodeRepository $promoCodeRepository)
    {
    }

    public function trouverParCode(string $code): PromoCode
    {
        $promoCode = $this->promoCodeRepository->findOneBy(['code' => $code]);

        if (!$promoCode instanceof PromoCode) {
            throw new PromoCodeInvalideException("Ce code promo n'existe pas.");
        }

        return $promoCode;
    }

    /**
     * @param Prestation[] $prestationsDuPanier
     */
    public function verifierValidite(PromoCode $promoCode, string $totalAvantReduction, array $prestationsDuPanier): void
    {
        $maintenant = new \DateTimeImmutable();

        if ($maintenant < $promoCode->getDateDebut() || $maintenant > $promoCode->getDateFin()) {
            throw new PromoCodeInvalideException('Ce code promo n\'est plus valide.');
        }

        if (null !== $promoCode->getMontantMinimum() && bccomp($totalAvantReduction, $promoCode->getMontantMinimum(), 2) < 0) {
            throw new PromoCodeInvalideException('Le montant du panier n\'atteint pas le minimum requis pour ce code promo.');
        }

        if (null !== $promoCode->getNombreUtilisationsMax() && $promoCode->getNombreUtilisationsActuelles() >= $promoCode->getNombreUtilisationsMax()) {
            throw new PromoCodeInvalideException("Ce code promo a atteint son nombre maximum d'utilisations.");
        }

        $prestationRestreinte = $promoCode->getPrestation();

        if (null !== $prestationRestreinte) {
            $panierContientLaPrestation = false;

            foreach ($prestationsDuPanier as $prestation) {
                if ($prestation === $prestationRestreinte) {
                    $panierContientLaPrestation = true;
                    break;
                }
            }

            if (!$panierContientLaPrestation) {
                throw new PromoCodeInvalideException('Ce code promo est reserve a une prestation qui n\'est pas dans le panier.');
            }
        }
    }

    public function calculerTotalApresReduction(string $totalAvantReduction, PromoCode $promoCode): string
    {
        if (TypeReductionPromoCode::Pourcentage === $promoCode->getTypeReduction()) {
            $tauxDecimal = bcdiv($promoCode->getValeur(), '100', 4);
            $montantReduction = bcmul($totalAvantReduction, $tauxDecimal, 2);
        } else {
            $montantReduction = $promoCode->getValeur();
        }

        $totalApresReduction = bcsub($totalAvantReduction, $montantReduction, 2);

        if (bccomp($totalApresReduction, '0.00', 2) < 0) {
            return '0.00';
        }

        return $totalApresReduction;
    }
}
