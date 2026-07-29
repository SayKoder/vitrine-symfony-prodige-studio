<?php

namespace App\Catalogue\Controller;

use App\Catalogue\Form\ContactMessage;
use App\Catalogue\Form\ContactType;
use App\Catalogue\Repository\PhotoRepository;
use App\Catalogue\Service\ContactMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class VitrineController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function accueil(PhotoRepository $photoRepository): Response
    {
        return $this->render('public/home.html.twig', [
            'photosMisesEnAvant' => $photoRepository->misesEnAvant(3),
        ]);
    }

    #[Route('/galerie', name: 'app_galerie', methods: ['GET'])]
    public function galerie(PhotoRepository $photoRepository): Response
    {
        return $this->render('public/galerie.html.twig', [
            'photos' => $photoRepository->pourGalerie(),
        ]);
    }

    #[Route('/a-propos', name: 'app_a_propos', methods: ['GET'])]
    public function aPropos(): Response
    {
        return $this->render('public/a-propos.html.twig');
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, ContactMailer $contactMailer): Response
    {
        $contactMessage = new ContactMessage();
        $form = $this->createForm(ContactType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ('' === ($contactMessage->siteWeb ?? '')) {
                try {
                    $contactMailer->envoyerMessage($contactMessage);
                    $this->addFlash('success', 'Merci, votre message a bien ete envoye. Je reviens vers vous sous 48h.');
                } catch (TransportExceptionInterface) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi. Contactez-moi directement par telephone ou e-mail.');
                }
            } else {
                $this->addFlash('success', 'Merci, votre message a bien ete envoye. Je reviens vers vous sous 48h.');
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('public/contact.html.twig', [
            'form' => $form,
        ]);
    }
}
