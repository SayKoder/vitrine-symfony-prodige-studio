<?php

namespace App\Catalogue\Controller;

use App\Catalogue\Entity\Prestation;
use App\Catalogue\Form\PrestationType;
use App\Catalogue\Repository\PrestationRepository;
use App\Catalogue\Service\PrestationEnUsageException;
use App\Catalogue\Service\PrestationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/prestations', name: 'admin_prestation_')]
#[IsGranted('ROLE_ADMIN')]
class PrestationController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(PrestationRepository $prestationRepository): Response
    {
        return $this->render('admin/prestation/index.html.twig', [
            'prestations' => $prestationRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, PrestationManager $prestationManager): Response
    {
        $prestation = new Prestation('', '', '0');
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prestationManager->save($prestation, $form->get('imageFichier')->getData());
            $this->addFlash('success', 'Prestation creee.');

            return $this->redirectToRoute('admin_prestation_index');
        }

        return $this->render('admin/prestation/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'])]
    public function show(Prestation $prestation): Response
    {
        return $this->render('admin/prestation/show.html.twig', [
            'prestation' => $prestation,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prestation $prestation, PrestationManager $prestationManager): Response
    {
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prestationManager->save($prestation, $form->get('imageFichier')->getData());
            $this->addFlash('success', 'Prestation modifiee.');

            return $this->redirectToRoute('admin_prestation_index');
        }

        return $this->render('admin/prestation/edit.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Prestation $prestation, PrestationManager $prestationManager): Response
    {
        if ($this->isCsrfTokenValid('delete-prestation-'.$prestation->getId(), $request->request->get('_token'))) {
            try {
                $prestationManager->delete($prestation);
                $this->addFlash('success', 'Prestation supprimee.');
            } catch (PrestationEnUsageException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->redirectToRoute('admin_prestation_index');
    }
}
