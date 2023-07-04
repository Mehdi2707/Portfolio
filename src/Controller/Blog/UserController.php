<?php

namespace App\Controller\Blog;

use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Service\OptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/blog', name: 'blog_')]
class UserController extends AbstractController
{
    public function __construct(private OptionService $optionService)
    {
        
    }
    
    #[Route('/user/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        return $this->redirectToRoute('ecommerce_register');

        $usersCanRegister = $this->optionService->getValue('users_can_register');

        if(!$usersCanRegister)
            return $this->redirectToRoute('blog_home');

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('blog_home');
        }

        return $this->render('Blog/user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/user/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('blog_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('Blog/user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/user/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/user/{username}', name: 'profile')]
    public function index(?User $user): Response
    {
        if(!$user)
            return $this->redirectToRoute('blog_home');

        return $this->render('Blog/user/index.html.twig', [
            'user' => $user
        ]);
    }
}
