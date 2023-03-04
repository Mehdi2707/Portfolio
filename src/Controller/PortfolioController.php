<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PortfolioController extends AbstractController
{
    #[Route('/portfolio', name: 'app_portfolio')]
    public function index(UrlGeneratorInterface $urlGenerator): Response
    {
        $logo = $urlGenerator->generate('path_to_images').'Logo_mehdi_3.png';
        $photoProfil = $urlGenerator->generate('path_to_images').'received_5457719520910618.jpeg';
        $imageSymfony = $urlGenerator->generate('path_to_images').'symfony.jpg';
        $imageChatgpt = $urlGenerator->generate('path_to_images').'ChatGPT_NEW_LEAD.jpg';
        $imageBlog = $urlGenerator->generate('path_to_images').'cap_blog.png';

        return $this->render('portfolio/index.html.twig', [
            'logo' => $logo,
            'photo_de_profil' => $photoProfil,
            'image_symfony' => $imageSymfony,
            'image_chatgpt' => $imageChatgpt,
            'image_blog' => $imageBlog
        ]);
    }

    #[Route('/portfolio/accueil', name: 'app_portfolio_accueil')]
    public function accueil(UrlGeneratorInterface $urlGenerator): Response
    {
        $photoProfil = $urlGenerator->generate('path_to_images').'received_5457719520910618.jpeg';
        $imageSymfony = $urlGenerator->generate('path_to_images').'symfony.jpg';
        $imageChatgpt = $urlGenerator->generate('path_to_images').'ChatGPT_NEW_LEAD.jpg';
        $imageBlog = $urlGenerator->generate('path_to_images').'cap_blog.png';

        return $this->render('portfolio/accueil.html.twig', [
            'photo_de_profil' => $photoProfil,
            'image_symfony' => $imageSymfony,
            'image_chatgpt' => $imageChatgpt,
            'image_blog' => $imageBlog
        ]);
    }

    #[Route('/portfolio/experiences', name: 'app_portfolio_experiences')]
    public function experiences(UrlGeneratorInterface $urlGenerator): Response
    {
        $imageSymfony = $urlGenerator->generate('path_to_images').'symfony.jpg';
        $imageChatgpt = $urlGenerator->generate('path_to_images').'ChatGPT_NEW_LEAD.jpg';
        $imageBlog = $urlGenerator->generate('path_to_images').'cap_blog.png';

        return $this->render('portfolio/experiences.html.twig', [
            'image_symfony' => $imageSymfony,
            'image_chatgpt' => $imageChatgpt,
            'image_blog' => $imageBlog
        ]);
    }

    #[Route('/portfolio/formations', name: 'app_portfolio_formations')]
    public function formations(): Response
    {
        return $this->render('portfolio/formations.html.twig', [
            'controller_name' => 'PortfolioController',
        ]);
    }

    #[Route('/portfolio/contact', name: 'app_portfolio_contact')]
    public function contact(): Response
    {
        return $this->render('portfolio/contact.html.twig', [
            'controller_name' => 'PortfolioController',
        ]);
    }
}
