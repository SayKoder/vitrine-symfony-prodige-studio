<?php

namespace App\Catalogue\Controller;

use App\Catalogue\Entity\CategoriePhoto;
use App\Catalogue\Entity\Photo;
use App\Catalogue\Form\PhotoType;
use App\Catalogue\Repository\PhotoRepository;
use App\Catalogue\Service\PhotoManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/photos', name: 'admin_photo_')]
#[IsGranted('ROLE_ADMIN')]
class PhotoController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(PhotoRepository $photoRepository): Response
    {
        return $this->render('admin/photo/index.html.twig', [
            'photos' => $photoRepository->pourGalerie(),
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, PhotoManager $photoManager): Response
    {
        $photo = new Photo(CategoriePhoto::Portrait);
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoManager->save($photo, $form->get('imageFichier')->getData());
            $this->addFlash('success', 'Photo ajoutee.');

            return $this->redirectToRoute('admin_photo_index');
        }

        return $this->render('admin/photo/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'])]
    public function show(Photo $photo): Response
    {
        return $this->render('admin/photo/show.html.twig', [
            'photo' => $photo,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Photo $photo, PhotoManager $photoManager): Response
    {
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoManager->save($photo, $form->get('imageFichier')->getData());
            $this->addFlash('success', 'Photo modifiee.');

            return $this->redirectToRoute('admin_photo_index');
        }

        return $this->render('admin/photo/edit.html.twig', [
            'photo' => $photo,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Photo $photo, PhotoManager $photoManager): Response
    {
        if ($this->isCsrfTokenValid('delete-photo-'.$photo->getId(), $request->request->get('_token'))) {
            $photoManager->delete($photo);
            $this->addFlash('success', 'Photo supprimee.');
        }

        return $this->redirectToRoute('admin_photo_index');
    }
}
