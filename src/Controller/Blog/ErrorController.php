<?php

namespace App\Controller\Blog;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception, Environment $env): Response
    {
        $view = "Blog/bundles/TwigBundle/Exception/error{$exception->getStatusCode()}.html.twig";

        if(!$env->getLoader()->exists($view))
            $view = "Blog/bundles/TwigBundle/Exception/error.html.twig";

        return $this->render($view);
    }
}