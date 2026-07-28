<?php

namespace App\Catalogue\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeoController extends AbstractController
{
    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $sitemapUrl = $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $contenu = <<<TXT
        User-agent: *
        Allow: /
        Disallow: /admin
        Disallow: /compte
        Disallow: /login

        Sitemap: {$sitemapUrl}

        TXT;

        return new Response($contenu, 200, ['Content-Type' => 'text/plain']);
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $routes = ['app_home', 'app_prestations', 'app_galerie', 'app_a_propos', 'app_contact'];
        $urls = array_map(
            fn (string $route) => $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL),
            $routes,
        );

        return $this->render('seo/sitemap.xml.twig', ['urls' => $urls], new Response('', 200, [
            'Content-Type' => 'application/xml',
        ]));
    }
}
