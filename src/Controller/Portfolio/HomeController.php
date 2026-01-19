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
use Symfony\Component\HttpFoundation\JsonResponse;
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
        if (!$session->has('game')) return $this->redirectToRoute('app_skullking_start');

        $game = $session->get('game');
        $totalRoundsPlayed = count($game['rounds']);
        $maxRounds = $game['max_rounds'] ?? 10;

        $displayRound = min($totalRoundsPlayed + 1, $maxRounds);
        $isGameOver = $totalRoundsPlayed >= $maxRounds;

        $totals = [];
        foreach ($game['players'] as $player) { $totals[$player] = 0; }
        foreach ($game['rounds'] as $round) {
            foreach ($game['players'] as $player) {
                $totals[$player] += $round[$player]['points'] ?? 0;
            }
        }

        $ranking = $totals;
        arsort($ranking);

        if ($request->isMethod('POST')) {
            $totalRoundsPlayed = count($game['rounds']);
            $data = $request->request->all();
            $newRound = [];

            foreach ($game['players'] as $player) {
                $bet = (int)($data['bet_' . $player] ?? 0);
                $taken = (int)($data['taken_' . $player] ?? 0);
                $bonus = (int)($data['bonus_' . $player] ?? 0);

                $points = $this->calculatePoints($bet, $taken, $totalRoundsPlayed + 1) + $bonus;
                $newRound[$player] = ['bet' => $bet, 'taken' => $taken, 'points' => $points];
            }

            $game['rounds'][] = $newRound;
            $session->set('game', $game);

            $totalRoundsPlayed = count($game['rounds']);
            $maxRounds = $game['max_rounds'] ?? 10;

            $totals = [];
            foreach ($game['players'] as $p) { $totals[$p] = 0; }
            foreach ($game['rounds'] as $r) {
                foreach ($game['players'] as $p) { $totals[$p] += $r[$p]['points']; }
            }

            $ranking = $totals; arsort($ranking);

            return new JsonResponse([
                'success' => true,
                'isGameOver' => $totalRoundsPlayed >= $maxRounds,
                'currentRound' => min($totalRoundsPlayed + 1, $maxRounds),
                'totals' => $totals,
                'ranking' => $ranking,
                'newRoundData' => $newRound,
                'roundNumber' => $totalRoundsPlayed
            ]);
        }

        return $this->render('Portfolio/skullking/index.html.twig', [
            'players' => $game['players'],
            'rounds' => $game['rounds'],
            'currentRound' => $displayRound,
            'maxRounds' => $maxRounds,
            'isGameOver' => $isGameOver,
            'totals' => $totals,
            'ranking' => $ranking
        ]);
    }

    #[Route('/skullking/start', name: 'app_skullking_start')]
    public function start(Request $request, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $playerNamesJson = $request->request->get('players_list');
            $playerNames = json_decode($playerNamesJson, true);
            $maxRounds = $request->request->getInt('max_rounds', 10);

            if (empty($playerNames)) {
                return $this->redirectToRoute('app_skullking_start');
            }

            $session->set('game', [
                'players' => $playerNames,
                'rounds' => [],
                'max_rounds' => $maxRounds
            ]);

            return $this->redirectToRoute('app_skullking');
        }
        return $this->render('Portfolio/skullking/new.html.twig');
    }

    #[Route('/skullking/reset_all', name: 'app_skullking_reset_all')]
    public function resetAll(SessionInterface $session): Response
    {
        $session->clear();
        return $this->redirectToRoute('app_skullking_start');
    }

    #[Route('/skullking/reset_scores', name: 'app_skullking_reset_scores')]
    public function resetScores(SessionInterface $session): Response
    {
        if ($session->has('game')) {
            $game = $session->get('game');
            $game['rounds'] = []; // Efface les scores
            $session->set('game', $game);
        }

        return $this->redirectToRoute('app_skullking');
    }

    private function calculatePoints(int $bet, int $taken, int $roundNumber): int
    {
        // Cas 1 : Le joueur a misé sur zéro
        if ($bet === 0) {
            if ($taken === 0) {
                // Mise à zéro réussie : 10 points par carte distribuée (équivalent au numéro de la manche)
                return $roundNumber * 10;
            } else {
                // Mise à zéro manquée : -10 points par carte distribuée
                return $roundNumber * -10;
            }
        } else {
            // Cas 2 : Le joueur a misé sur un ou plusieurs plis
            if ($bet === $taken) {
                // Mise réussie : 20 points par pli annoncé
                return $bet * 20;
            } else {
                // Mise manquée : -10 points par pli de différence
                return abs($bet - $taken) * -10;
            }
        }
    }
}
