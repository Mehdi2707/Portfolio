<?php

namespace App\Controller\AllYouNeedIsHere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/barberz', name: 'app_barberz_')]
class BarberzController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/index.html.twig', [
        ]);
    }

    #[Route('/services', name: 'services')]
    public function services(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/services.html.twig', [
        ]);
    }

    #[Route('/barbershop', name: 'barbershop')]
    public function barbershop(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/barbershop.html.twig', [
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/about.html.twig', [
        ]);
    }

    #[Route('/blog', name: 'blog')]
    public function blog(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/blog.html.twig', [
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/contact.html.twig', [
        ]);
    }

    #[Route('/single', name: 'single')]
    public function single(): Response
    {
        return $this->render('AllYouNeedIsHere/barberz/single.html.twig', [
        ]);
    }
}
