<?php

namespace App\User\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route(path: '/admin', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route(path: '/compte', name: 'app_compte_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function compte(): Response
    {
        return $this->render('compte/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
