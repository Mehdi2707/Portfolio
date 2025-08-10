<?php

namespace App\Controller\Portfolio;

use App\Entity\Contact;
use App\Entity\FilesContact;
use App\Form\ContactFormType;
use App\Repository\WorksRepository;
use App\Service\SendMailService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, UploadService $uploadService, SendMailService $mailService, WorksRepository $worksRepository): Response
    {
        $works = $worksRepository->findAll();
        $contact = new Contact();

        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $files = $form->get('files')->getData();
            $nbFiles = 0;

            foreach($files as $file)
            {
                $folder = 'contact';

                $fichier = $uploadService->add($file, $folder);

                $newFile = new FilesContact();
                $newFile->setName($fichier);
                $newFile->setContact($contact);
                $contact->addFile($newFile);
                $nbFiles++;
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            $mailService->send(
                $this->getParameter('app.mailaddress'),
                $this->getParameter('app.mailaddress'),
                'Demande de contact de ' . $contact->getFullname(),
                'contactPortfolio',
                [ 'contact' => $contact, 'nbFiles' => $nbFiles ]
            );

            $mailService->send(
                $this->getParameter('app.mailaddress'),
                $contact->getEmail(),
                'Votre demande de contact sur mehdi-birembaut.fr',
                'contactPortfolioSend',
                [ 'contact' => $contact, 'nbFiles' => $nbFiles ]
            );

            $this->addFlash('success', 'Message envoyé avec succès');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('Portfolio/home/index.html.twig', [
            'form' => $form->createView(),
            'works' => $works
        ]);
    }

    #[Route('/skullking', name: 'app_skullking')]
    public function skullking(Request $request, SessionInterface $session): Response
    {
        // Initialisation de la partie en session si elle n'existe pas
        if (!$session->has('game')) {
            $session->set('game', [
                // Modifiez les noms ici
                'players' => ['Joueur_1', 'Joueur_2', 'Joueur_3', 'Joueur_4'],
                'rounds' => []
            ]);
        }

        $game = $session->get('game');
        $roundNumber = count($game['rounds']) + 1;

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $newRound = [];
            foreach ($game['players'] as $player) {
                // Conversion explicite en entier
                $bet = (int) ($data['bet_' . $player] ?? 0);
                $taken = (int) ($data['taken_' . $player] ?? 0);


                $points = $this->calculatePoints($bet, $taken);
                $newRound[$player] = ['bet' => $bet, 'taken' => $taken, 'points' => $points];
            }

            $game['rounds'][] = $newRound;
            $session->set('game', $game);

            return $this->redirectToRoute('app_skullking');
        }

        return $this->render('Portfolio/skullking/index.html.twig', [
            'players' => $game['players'],
            'rounds' => $game['rounds'],
            'currentRound' => $roundNumber
        ]);
    }

    private function calculatePoints(int $bet, int $taken): int
    {
        if ($bet == $taken) {
            // Condition: si les plis annoncés sont égaux aux plis réalisés
            return $bet == 0 ? 20 : $bet * 20;
        } else {
            // Condition: si les plis annoncés sont différents des plis réalisés
            return abs($bet - $taken) * -10;
        }
    }
}
