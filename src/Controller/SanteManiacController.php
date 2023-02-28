<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SanteManiacController extends AbstractController
{
    #[Route('/sante_maniac', name: 'app_sante_maniac')]
    public function index(): Response
    {
        return $this->render('sante_maniac/index.html.twig', [
            'controller_name' => 'SanteManiacController',
        ]);
    }
}
