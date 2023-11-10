<?php

namespace App\Controller\Portfolio\Admin;

use App\Entity\Works;
use App\Form\WorksFormType;
use App\Repository\WorksRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, PictureService $pictureService, EntityManagerInterface $entityManager, WorksRepository $worksRepository): Response
    {
        $works = $worksRepository->findAll();
        $work = new Works();

        $form = $this->createForm(WorksFormType::class, $work);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('imageName')->getData();

            $folder = 'works';

            try
            {
                $file = $pictureService->add($image, $folder);
            }
            catch (\Exception $e)
            {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('app_admin');
            }

            $work->setImageName($file);

            $entityManager->persist($work);
            $entityManager->flush();

            $this->addFlash('success', 'Projet ajouté avec succès');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('Portfolio/admin/index.html.twig', [
            'form' => $form->createView(),
            'works' => $works
        ]);
    }
}