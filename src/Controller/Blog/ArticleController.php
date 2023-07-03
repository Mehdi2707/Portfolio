<?php

namespace App\Controller\Blog;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\Type\CommentType;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog', name: 'blog_')]
class ArticleController extends AbstractController
{
    #[Route('/article/{slug}', name: 'article_show')]
    public function show(CommentService $commentService, ?Article $article): Response
    {
        if(!$article)
            return $this->redirectToRoute('blog_home');

        $comment = new Comment($article);

        $commentForm = $this->createForm(CommentType::class, $comment);
        
        return $this->render('Blog/article/show.html.twig', [
            'article' => $article,
            'comments' => $commentService->getPaginatedComments($article),
            'commentForm' => $commentForm->createView(),
        ]);
    }
}
