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

        $client = HttpClient::create();

        foreach ($alerts as $alert) {
            $lien = $alert->getLink(); // Supposons que chaque alerte a une URL

            //  try {
            // Requête HTTP vers la page produit
            $response = $client->request('GET', $lien);
            $html = $response->getContent();

            // Analyse du contenu HTML
            $crawler = new Crawler($html);

            $crawler->filter('button.add-to-cart[data-button-action="add-to-cart"]')->each(function ($node, $index) use ($output, $alert) {
                $isDisabled = $node->attr('disabled') !== null; // Vérifie si l'attribut `disabled` existe
                $buttonText = $node->filter('span')->count() > 0 ? trim($node->filter('span')->text()) : '';

                if($index==0){
                    // Détection de l'état du produit
                    if ($isDisabled || strpos(strtolower($buttonText), 'rupture') !== false) {
                        //$output->writeln("Produit indisponible pour l'alerte : {$alert->getLink()}"); 
                    } else {
                        $output->writeln("Produit disponible pour l'alerte : {$alert->getLink()}");

                        // Logique pour envoyer un email et fermer l'alerte
                        $email = (new Email())
                            ->from('mehdibrbt@gmail.com')
                            ->to('d38.h4ck3ur@live.fr')
                            ->subject('L\'article est disponible')
                            ->text('L\'article est maintenant disponible : ' . $alert->getLink());

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
