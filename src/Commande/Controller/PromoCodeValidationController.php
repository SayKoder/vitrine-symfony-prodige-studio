<?php

namespace App\Commande\Controller;

use App\Commande\Service\PanierManager;
use App\Commande\Service\PanierVideException;
use App\Promotion\Service\PromoCodeInvalideException;
use App\Promotion\Service\PromoCodeManager;
use App\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PromoCodeValidationController
{
    public function __construct(
        private readonly Security $security,
        private readonly PanierManager $panierManager,
        private readonly PromoCodeManager $promoCodeManager,
    ) {
    }

    #[Route('/api/promo_codes/valider', name: 'api_promo_code_valider', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $donnees = json_decode($request->getContent(), true) ?? [];
        $code = \is_string($donnees['code'] ?? null) ? $donnees['code'] : '';

        try {
            $panier = $this->panierManager->getOrCreateForUser($user);

            if ($panier->getLignesPanier()->isEmpty()) {
                throw new PanierVideException();
            }

            $totalAvantReduction = $this->panierManager->calculerTotal($panier);
            $prestations = $this->panierManager->listerPrestations($panier);

            $promoCode = $this->promoCodeManager->trouverParCode($code);
            $this->promoCodeManager->verifierValidite($promoCode, $totalAvantReduction, $prestations);

            $totalApresReduction = $this->promoCodeManager->calculerTotalApresReduction($totalAvantReduction, $promoCode);
            $montantReduction = bcsub($totalAvantReduction, $totalApresReduction, 2);
        } catch (PanierVideException|PromoCodeInvalideException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 400);
        }

        return new JsonResponse([
            'code' => $promoCode->getCode(),
            'totalAvantReduction' => $totalAvantReduction,
            'totalApresReduction' => $totalApresReduction,
            'montantReduction' => $montantReduction,
        ]);
    }
}
