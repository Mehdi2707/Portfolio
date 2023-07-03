<?php

namespace App\Controller\Blog;

use App\Entity\Category;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog', name: 'blog_')]
class CategoryController extends AbstractController
{
    #[Route('/category/{slug}', name: 'category_show')]
    public function show(ArticleService $articleService, ?Category $category): Response
    {
        if(!$category)
            return $this->redirectToRoute("blog_home");

        return $this->render('Blog/category/show.html.twig', [
            'category' => $category,
            'articles' => $articleService->getPaginatedArticles($category),
        ]);
    }
}
