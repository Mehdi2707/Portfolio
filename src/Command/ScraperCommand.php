<?php

namespace App\Command;

use App\Entity\Alerts;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ScraperCommand extends Command
{
    protected static $defaultName = 'app:scraper';
    private $mailer;
    private $entityManager;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Scrapes some data.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer les alertes avec `isClosed = false` depuis la base de données
        $alerts = $this->entityManager->getRepository(Alerts::class)->findBy(['isClosed' => false]);

        if (count($alerts) === 0) {
            $output->writeln('Aucune alerte à traiter.');
            return Command::SUCCESS;
        }

        $client = HttpClient::create([
            'timeout' => 30, // timeout global en secondes
            'max_duration' => 35, // durée max pour la requête complète
        ]);

        foreach ($alerts as $alert) {
            $lien = $alert->getLink();
            $maxRetries = 3;
            $attempt = 0;
            $success = false;

            while ($attempt < $maxRetries && !$success) {
                try {
                    $response = $client->request('GET', $lien, [
                        'timeout' => 30,
                        'max_duration' => 35,
                    ]);
                    $html = $response->getContent();
                    $success = true;
                } catch (TransportExceptionInterface $e) {
                    $attempt++;
                    $output->writeln("Erreur lors de la requête vers $lien (tentative $attempt) : " . $e->getMessage());
                    sleep(2); // pause avant nouvelle tentative
                }
            }

            if (!$success) {
                $output->writeln("Échec de récupération après $maxRetries tentatives pour $lien");
                continue; // passe à la prochaine alerte
            }

            if (strpos($lien, 'levelsautomobile.fr') !== false) {
                $this->handleLevelsAutomobile($lien, $html, $alert, $output);
            } else {
                // Logique existante pour les autres sites
                $this->handleRegularScraping($html, $alert, $output);
            }

            // Sauvegarder les changements en base de données après le foreach
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }

    private function handleLevelsAutomobile(string $lien, string $html, $alert, OutputInterface $output)
    {
        $previousHtml = $alert->getHtml();

        if (!$previousHtml) {
            // Premier passage, stocker le HTML initial
            $alert->setHtml($html);
            $this->entityManager->persist($alert);
            $output->writeln("Premier scan de levelsautomobile.fr pour : $lien");
            return;
        }

        if ($html !== $previousHtml) {
            $output->writeln("Nouveau véhicule disponible sur levelsautomobile.fr, email envoyé à {$alert->getEmail()}");

            // Envoyer l'email de notification
            $email = (new Email())
                ->from('mehdibrbt@gmail.com')
                ->to($alert->getEmail())
                ->subject('Nouveau véhicule disponible sur Levels Automobile !')
                ->text('Un nouveau véhicule est maintenant disponible, ne ratez pas cette occasion et jetez un œil : ' . $alert->getLink());

            $this->mailer->send($email);

            // Mettre à jour le HTML stocké
            $alert->setHtml($html);
            $this->entityManager->persist($alert);
        }
    }

    private function handleRegularScraping(string $html, $alert, OutputInterface $output)
    {
        // Analyse du contenu HTML (logique existante)
        $crawler = new Crawler($html);

        $crawler->filter('button.add-to-cart[data-button-action="add-to-cart"]')->each(function ($node, $index) use ($output, $alert) {
            $isDisabled = $node->attr('disabled') !== null; // Vérifie si l'attribut `disabled` existe
            $buttonText = $node->filter('span')->count() > 0 ? trim($node->filter('span')->text()) : '';

            // Index 0 car le bouton du panier est le premier
            if($index==0){
                // Détection de l'état du produit
                if ($isDisabled || strpos(strtolower($buttonText), 'rupture') !== false) {
                    //$output->writeln("Produit indisponible pour l'alerte : {$alert->getLink()}");
                } else {
                    $output->writeln("Produit disponible pour l'alerte : {$alert->getLink()} envoyé à {$alert->getEmail()}");

                    // Logique pour envoyer un email et fermer l'alerte
                    $email = (new Email())
                        ->from('mehdibrbt@gmail.com')
                        ->to($alert->getEmail())
                        ->subject('Votre produit est disponible !')
                        ->text('Votre produit est maintenant disponible, ne ratez pas cette occasion et passez commande : ' . $alert->getLink());

                    $this->mailer->send($email);

                    // Marquer l'alerte comme fermée
                    $alert->setIsClosed(true);
                    $this->entityManager->persist($alert);
                }
            }
        });
    }
}