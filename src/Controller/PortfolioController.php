<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PortfolioController extends AbstractController
{
    private function getPhotoProfil(): string
    {
        return $this->generateUrl('path_to_images') . 'received_5457719520910618.jpeg';
    }

    private function getLogo(): string
    {
        return $this->generateUrl('path_to_images') . 'Logo_mehdi_3.png';
    }

    private function getImageArticle($file): string
    {
        return $this->generateUrl('path_to_images') . $file;
    }

    #[Route('/portfolio', name: 'app_portfolio')]
    public function index(): Response
    {
        return $this->render('portfolio/index.html.twig', [
            'logo' => $this->getLogo(),
            'photo_de_profil' => $this->getPhotoProfil(),
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
    }

    #[Route('/portfolio/accueil', name: 'app_portfolio_accueil')]
    public function accueil(): Response
    {
        $html = $this->renderView('portfolio/accueil.html.twig', [
            'photo_de_profil' => $this->getPhotoProfil(),
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
        return new Response($html);
    }

    #[Route('/portfolio/experiences', name: 'app_portfolio_experiences')]
    public function experiences(): Response
    {
        $html = $this->renderView('portfolio/experiences.html.twig', [
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
        return new Response($html);
    }

    #[Route('/portfolio/articles', name: 'app_portfolio_articles')]
    public function articles(): Response
    {
        $html = $this->renderView('portfolio/articles.html.twig', [
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
        return new Response($html);
    }

    #[Route('/portfolio/formations', name: 'app_portfolio_formations')]
    public function formations(): Response
    {
        $html = $this->renderView('portfolio/formations.html.twig', [
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
        return new Response($html);
    }

    #[Route('/portfolio/contact', name: 'app_portfolio_contact')]
    public function contact(): Response
    {
        $html = $this->renderView('portfolio/contact.html.twig', [
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
        return new Response($html);
    }

    #[Route('/portfolio/generateur_profil', name: 'app_generateur_profil')]
    public function generateur_profil(): Response
    {
        $html = $this->renderView('portfolio/generateur_profil.html.twig', [
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
        ]);
        return new Response($html);
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

    #[Route('/portfolio/generateur_nom', name: 'app_generateur_nom')]
    public function generateur_nom(): Response
    {
        $url = $this->generateUrl('path_to_names');
        $html = $this->renderView('portfolio/generateur_nom.html.twig', [
            'image_symfony' => $this->getImageArticle('symfony.jpg'),
            'image_chatgpt' => $this->getImageArticle('ChatGPT_NEW_LEAD.jpg'),
            'image_blog' => $this->getImageArticle('cap_blog.png'),
            'lien_blog' => $this->getParameter('lien_blog'),
            'url' => $url,
        ]);
        return new Response($html);
    }
}
