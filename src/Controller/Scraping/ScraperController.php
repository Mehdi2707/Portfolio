<?php

namespace App\Controller\Scraping;

use App\Entity\Alerts;
use App\Repository\AlertsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ScraperController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/alert_disponibility', name: 'app_scraper')]
    public function index(AlertsRepository $alertsRepository): Response
    {
        return $this->render('Scraping/home/index.html.twig', [
            'alerts' => $alertsRepository->findAll()
        ]);
    }

    #[Route('/create-alert', name: 'create_alert')]
    public function createAlert(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Récupère les données du formulaire
        $name = $request->get('alert_form')['name'] ?? null;

        if (!$name) {
            return new JsonResponse(['success' => false, 'message' => 'Le champ est requis.'], 400);
        }

        // Crée une nouvelle instance de l'entité
        $alert = new Alerts();
        $alert->setLink($name);
        $alert->setIsClosed(false);

        // Persiste l'entité en base de données
        $em->persist($alert);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Alerte créée avec succès.']);
    }
}
