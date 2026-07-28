<?php

namespace App\Catalogue\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VitrineController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function accueil(): Response
    {
        return $this->render('public/home.html.twig');
    }

    #[Route('/galerie', name: 'app_galerie', methods: ['GET'])]
    public function galerie(): Response
    {
        return $this->render('public/galerie.html.twig');
    }

    #[Route('/a-propos', name: 'app_a_propos', methods: ['GET'])]
    public function aPropos(): Response
    {
        return $this->render('public/a-propos.html.twig');
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('public/contact.html.twig');
    }
}
