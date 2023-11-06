<?php

namespace App\Controller\Portfolio;

use App\Entity\Contact;
use App\Entity\FilesContact;
use App\Form\ContactFormType;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, UploadService $uploadService): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactFormType::class, $contact);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $files = $form->get('files')->getData();

            foreach($files as $file)
            {
                $folder = 'contact';

                $fichier = $uploadService->add($file, $folder);

                $newFile = new FilesContact();
                $newFile->setName($fichier);
                $newFile->setContact($contact);
                $contact->addFile($newFile);
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('Portfolio/home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
