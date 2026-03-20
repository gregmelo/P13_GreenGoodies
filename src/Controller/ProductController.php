<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;

/**
 * Contrôleur dédié à l'affichage d'un produit.
 */
final class ProductController extends AbstractController
{
    /**
     * Affiche la fiche détaillée d'un produit.
     *
     * @param Product $product Produit injecté automatiquement par Symfony via l'id
     *
     * @return Response Réponse HTML de la page produit
     */
    #[Route('/product/{id}', name: 'product_show')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }
}
