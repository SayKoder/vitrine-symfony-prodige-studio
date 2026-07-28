<?php

namespace App\Catalogue\Controller;

use App\Catalogue\Form\PrestationFiltreType;
use App\Catalogue\Repository\PrestationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogueController extends AbstractController
{
    private const int PAR_PAGE = 6;

    #[Route('/prestations', name: 'app_prestations', methods: ['GET'])]
    public function index(Request $request, PrestationRepository $prestationRepository): Response
    {
        $form = $this->createForm(PrestationFiltreType::class);
        $form->handleRequest($request);
        $filtres = $form->getData() ?? [];

        $page = max(1, $request->query->getInt('page', 1));

        $resultat = $prestationRepository->rechercherPourCatalogue(
            $filtres['nom'] ?? null,
            null !== ($filtres['prixMin'] ?? null) ? (string) $filtres['prixMin'] : null,
            null !== ($filtres['prixMax'] ?? null) ? (string) $filtres['prixMax'] : null,
            $page,
            self::PAR_PAGE,
        );

        return $this->render('public/catalogue.html.twig', [
            'form' => $form,
            'resultat' => $resultat,
        ]);
    }
}
