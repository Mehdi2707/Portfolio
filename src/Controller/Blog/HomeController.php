<?php

namespace App\Controller\Blog;

use App\Entity\Option;
use App\Entity\User;
use App\Form\Type\WelcomeType;
use App\Model\WelcomeModel;
use App\Repository\CategoryRepository;
use App\Service\ArticleService;
use App\Service\OptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog', name: 'blog_')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ArticleService $articleService, CategoryRepository $categoryRepo): Response
    {
        return $this->render('Blog/home/index.html.twig', [
            'articles' => $articleService->getPaginatedArticles(),
            'categories' => $categoryRepo->findAll(),
        ]);
    }

    #[Route('/welcome', name: 'welcome')]
    public function welcome(Request $request,
                            EntityManagerInterface $em,
                            UserPasswordHasherInterface $passwordHasher,
                            OptionService $optionService): Response
    {
        if($optionService->getValue(WelcomeModel::SITE_INSTALLED_NAME))
            return $this->redirectToRoute('blog_home');

        $welcomeForm = $this->createForm(WelcomeType::class, new \App\Model\WelcomeModel());

        $welcomeForm->handleRequest($request);

        if($welcomeForm->isSubmitted() && $welcomeForm->isValid())
        {
            /** @var WelcomeModel $data */
            $data = $welcomeForm->getData();

            $siteTitle = new Option(WelcomeModel::SITE_TITLE_LABEL, WelcomeModel::SITE_TITLE_NAME, $data->getSiteTitle(), TextType::class);
            $siteInstalled = new Option(WelcomeModel::SITE_INSTALLED_LABEL, WelcomeModel::SITE_INSTALLED_NAME, true, null);
            $usersCanRegister = new Option('Tout le monde peut s\'inscrire', 'users_can_register', true, CheckboxType::class);
            $nbArticlesParPage = new Option('Nombre d\'articles par page', 'blog_articles_limit', 5, NumberType::class);
            $copyright = new Option('Texte du copyright', 'blog_copyright', 'Tous droits réservés', TextType::class);

            $user = new User($data->getUsername());
            $user->setRoles(['ROLE_ADMIN']);
            $user->setEmail($data->getEmail());
            $user->setLastname($data->getLastname());
            $user->setFirstname($data->getFirstname());
            $user->setAddress($data->getAddress());
            $user->setZipcode($data->getZipcode());
            $user->setCity($data->getCity());
            $user->setIsVerified(false);
            $user->setPassword($passwordHasher->hashPassword($user, $data->getPassword()));

            $em->persist($siteTitle);
            $em->persist($siteInstalled);
            $em->persist($usersCanRegister);
            $em->persist($nbArticlesParPage);
            $em->persist($copyright);
            $em->persist($user);

            $em->flush();

            return $this->redirectToRoute('blog_home');
        }

        return $this->render('Blog/home/welcome.html.twig', [
            'form' => $welcomeForm->createView()
        ]);
    }
}
