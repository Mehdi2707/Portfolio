<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenerateurProfilController extends AbstractController
{
    #[Route('/generateur_profil', name: 'app_generateur_profil')]
    public function index(): Response
    {
        return $this->render('generateur_profil/index.html.twig', [

        ]);
    }

    #[Route('/get_api', name: 'app_get_api')]
    public function api()
    {
        $url = "https://api.namefake.com";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);
        return $this->json($result);
    }

}
