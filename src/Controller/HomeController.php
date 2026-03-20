<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;

/**
 * Contrôleur de la page d'accueil.
 * Récupère la liste des produits et les envoie au template.
 */
final class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil avec tous les produits.
     *
     * @param ProductRepository $productRepository Repository pour récupérer les produits
     *
     * @return Response Réponse HTML de la page d'accueil
     */
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }
}
