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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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



            if ($lien == "https://www.ticketmaster.fr/fr/manifestation/imagine-dragons-billet/idmanif/595972") {


                $process = new Process(['node', __DIR__.'../../../scripts/check-ticketmaster.js', $lien]);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                $result = trim($process->getOutput());

                $crawler = new Crawler($result);

                $placesDisponibles = false;

                // Parcours toutes les listes avec la classe session-price-list
                $crawler->filter('ul.session-price-list')->each(function (Crawler $ul) use (&$placesDisponibles) {
                    // Parcours chaque item de la liste
                    $ul->filter('li.session-price-item')->each(function (Crawler $li) use (&$placesDisponibles) {
                        $statusNode = $li->filter('span.session-price-cat-title-status');
                        if ($statusNode->count() === 0) {
                            // Pas de status = probablement disponible
                            $placesDisponibles = true;
                            return;
                        }
                        $statusText = trim($statusNode->text());
                        if (stripos($statusText, 'Épuisé') === false) {
                            // Pas "Épuisé" dans le texte, donc dispo
                            $placesDisponibles = true;
                            return;
                        }
                    });
                });

                if ($placesDisponibles) {
                    // Logique pour envoyer un email et fermer l'alerte
                    $email = (new Email())
                        ->from('mehdibrbt@gmail.com')
                        ->to($alert->getEmail())
                        ->subject('Imagine Dragons - Place disponible')
                        ->text('Au moins une place s\'est libéré pour Imagine Dragons, ne perdez pas de temps : ' . $lien);

                    $this->mailer->send($email);
                }

                continue;
            }




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

            // Analyse du contenu HTML
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
                            ->text('Votre produit est maintenant disponible, ne rater pas cette occasion et passer commande : ' . $alert->getLink());

                        $this->mailer->send($email);

                        // Marquer l'alerte comme fermée
                        $alert->setIsClosed(true);
                        $this->entityManager->persist($alert);
                    }
                }
            });

            // Sauvegarder les changements en base de données après le foreach
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
