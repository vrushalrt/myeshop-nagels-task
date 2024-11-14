<?php

namespace App\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    #[Route('/site/site', name: 'app_site_site')]
    public function index(): Response
    {
        return $this->render('site/site/index.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
}
