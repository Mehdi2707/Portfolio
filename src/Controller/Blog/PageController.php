<?php

namespace App\Controller\Blog;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog', name: 'blog_')]
class PageController extends AbstractController
{
    #[Route('/page/{slug}', name: 'page_show')]
    public function show(): Response
    {
        return $this->render('Blog/page/show.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
