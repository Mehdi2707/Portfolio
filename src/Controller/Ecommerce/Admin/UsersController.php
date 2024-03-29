<?php

namespace App\Controller\Ecommerce\Admin;

use App\Form\UsersFormType;
use App\Repository\UserRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ecommerce/admin/utilisateurs', name: 'admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserRepository $usersRepository): Response
    {
        $users = $usersRepository->findBy([], ['firstname' => 'asc']);

        return $this->render('Ecommerce/admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/edition', name: 'edit')]
    public function edit(Request $request, UserRepository $usersRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userId = $request->get('id');
        $user = $usersRepository->find($userId);

        $form = $this->createForm(UsersFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if($request->request->get('pass') == $request->request->get('pass2'))
            {
                $user->setPassword($passwordHasher->hashPassword($user, $request->request->get('pass')));
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès');

                return $this->redirectToRoute('admin_users_index');
            }
            else
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
        }

        return $this->render('Ecommerce/admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

}