<?php

namespace App\Controller\Portfolio\Admin;

use App\Entity\Works;
use App\Form\WorksFormType;
use App\Repository\ContactRepository;
use App\Repository\WorksRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, PictureService $pictureService, EntityManagerInterface $entityManager, WorksRepository $worksRepository, ContactRepository $contactRepository): Response
    {
        $works = $worksRepository->findAll();
        $contacts = $contactRepository->findBy([], ['id' => 'DESC']);
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
            'works' => $works,
            'contacts' => $contacts
        ]);
    }

    #[Route('/admin/download/file/{fileName}', name: 'contact_download_file')]
    public function downloadFile($fileName): Response
    {
        $filePath = $this->getParameter('images_directory') . 'contact/' . $fileName;

        if (file_exists($filePath))
        {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            $response->setContent(file_get_contents($filePath));

            return $response;
        }
        else
        {
            $this->addFlash('warning', 'Le fichier que vous voulez télécharger n\'existe pas');
            return $this->redirectToRoute('app_admin');
        }
    }

    #[Route('/admin/work/edit/{id}', name: 'app_admin_edit_work')]
    public function editWork(Request $request, PictureService $pictureService, EntityManagerInterface $entityManager, Works $work): Response
    {
        $form = $this->createForm(WorksFormType::class, $work);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if($work->getImageName() == '')
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
                    return $this->redirectToRoute('app_admin_edit_work');
                }

                $work->setImageName($file);
            }

            $entityManager->persist($work);
            $entityManager->flush();

            $this->addFlash('success', 'Projet modifié avec succès');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('Portfolio/admin/edit_work.html.twig', [
            'form' => $form->createView(),
            'work' => $work
        ]);
    }

    #[Route('/admin/delete/image/{id}', name: 'work_delete_image', methods: ['DELETE'])]
    public function deleteImage(Works $work, Request $request, EntityManagerInterface $entityManager, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $work->getId(), $data['_token']))
        {
            $name = $work->getImageName();

            if($pictureService->delete($name, 'works'))
            {
                $work->setImageName('');
                $entityManager->persist($work);
                $entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }
        return new JsonResponse(['error' => 'Token invalide'], 400);
    }

    #[Route('/admin/delete/work/{id}', name: 'work_delete', methods: ['DELETE'])]
    public function deleteWork(Works $work, Request $request, EntityManagerInterface $entityManager, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $work->getId(), $data['_token']))
        {
            if($pictureService->delete($work->getImageName(), 'works'))
            {
                $entityManager->remove($work);
                $entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }
        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}