<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GenerateurNomController extends AbstractController
{
    #[Route('/generateur_nom', name: 'app_generateur_nom')]
    public function index(UrlGeneratorInterface $urlGenerator): Response
    {
        $url = $urlGenerator->generate('path_to_names');
        return $this->render('generateur_nom/index.html.twig', [
            'url' => $url,
        ]);
    }
}
