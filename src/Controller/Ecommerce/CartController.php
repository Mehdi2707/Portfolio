<?php

namespace App\Controller\Ecommerce;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ecommerce/panier', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $panier = $session->get("panier", []);

        $dataPanier = [];
        $total = 0;

        foreach($panier as $id => $quantite)
        {
            $product = $productsRepository->find($id);
            $dataPanier[] = [
                "produit" => $product,
                "quantite" => $quantite
            ];
            $total += ($product->getPrice() /100 ) * $quantite;
        }

        return $this->render('Ecommerce/cart/index.html.twig', [
            'dataPanier' => $dataPanier,
            'total' => $total
        ]);
    }

    #[Route('/ajouter/{id}', name: 'add')]
    public function add(Products $products, SessionInterface $session): Response
    {
        $panier = $session->get("panier", []);
        $id = $products->getId();

        if(!empty($panier[$id]))
            $panier[$id]++;
        else
            $panier[$id] = 1;

        $session->set("panier", $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/ajoutRapide/{id}', name: 'addFast')]
    public function addFast(Products $products, SessionInterface $session, Request $request): Response
    {
        $panier = $session->get("panier", []);
        $id = $products->getId();

        if(!empty($panier[$id]))
            $panier[$id]++;
        else
            $panier[$id] = 1;

        $session->set("panier", $panier);

        $refererUrl = $request->headers->get('referer');
        $redirectUrl = $refererUrl ?? $this->generateUrl('homepage');

        return $this->redirect($redirectUrl);
    }

    #[Route('/enlever/{id}', name: 'remove')]
    public function remove(Products $products, SessionInterface $session): Response
    {
        $panier = $session->get("panier", []);
        $id = $products->getId();

        if(!empty($panier[$id]))
        {
            if($panier[$id] > 1)
                $panier[$id]--;
            else
                unset($panier[$id]);
        }
        else
            $panier[$id] = 1;

        $session->set("panier", $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(Products $products, SessionInterface $session): Response
    {
        $panier = $session->get("panier", []);
        $id = $products->getId();

        if(!empty($panier[$id]))
            unset($panier[$id]);

        $session->set("panier", $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/confirmation', name: 'confirm')]
    public function confirm(SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $panier = $session->get("panier", []);
        $user = $this->getUser();

        if(!$user)
            return $this->redirectToRoute('ecommerce_login');

        if(empty($panier))
            return $this->redirectToRoute('cart_index');

        $dataPanier = [];
        $total = 0;

        foreach($panier as $id => $quantite)
        {
            $product = $productsRepository->find($id);
            $dataPanier[] = [
                "produit" => $product,
                "quantite" => $quantite
            ];
            $total += ($product->getPrice() /100 ) * $quantite;
        }

        return $this->render('Ecommerce/cart/confirm.html.twig' , [
            'dataPanier' => $dataPanier,
            'total' => $total,
            'user' => $user
        ]);
    }

    #[Route('/paiement', name: 'pay')]
    public function pay(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): Response
    {
        $panier = $session->get("panier", []);
        $user = $this->getUser();

        if(!$user->getIsVerified())
        {
            $this->addFlash('danger', 'Votre compte n\'est pas activé');
            return $this->redirectToRoute('ecommerce_main');
        }

        $dataPanier = [];

        foreach($panier as $id => $quantite)
        {
            $product = $productsRepository->find($id);
            $dataPanier[] = [
                "produit" => $product,
                "quantite" => $quantite
            ];
        }

        $order = new Orders();
        $order->setUsers($user);
        $order->setReference($this->generateRandomString(6));

        $entityManager->persist($order);

        foreach($dataPanier as $product)
        {
            $orderDetails = new OrdersDetails();
            $orderDetails->setOrders($order);
            $orderDetails->setProducts($product['produit']);
            $orderDetails->setQuantity($product['quantite']);
            $orderDetails->setPrice($product['quantite'] * $product['produit']->getPrice());

            $produit = $product['produit'];

            // vérifier si le stock est suffisant
            $stock = $produit->getStock();
            if ($stock >= $product['quantite']) {
                // déduire la quantité commandée du stock
                $stock -= $product['quantite'];
                $produit->setStock($stock);

                $entityManager->persist($orderDetails);
                $entityManager->persist($produit);
            } else {
                // stock insuffisant, annuler la commande
                $this->addFlash("danger", "Le stock de ".$produit->getName()." est insuffisant pour satisfaire la commande.");
                return $this->redirectToRoute('cart_index');
            }
        }

        $entityManager->flush();

        $session->remove("panier", []);

        $this->addFlash('success', 'Votre commande à bien été enregistrée');

        return $this->redirectToRoute('ecommerce_main');
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
