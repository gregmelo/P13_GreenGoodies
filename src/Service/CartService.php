<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service de gestion du panier en session (ajout, lecture, total, frais de port).
 */
class CartService
{
    private const CART_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    ) {}

    /**
     * Retourne la session courante à partir de la requête active.
     *
     * @throws \RuntimeException Si aucune requête courante n'est disponible
     *
     * @return SessionInterface Session HTTP courante
     */
    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new \RuntimeException('Aucune requête courante disponible pour accéder à la session.');
        }

        return $request->getSession();
    }

    /**
     * Ajoute une unité du produit donné au panier stocké en session.
     *
     * @param int $productId Identifiant du produit à ajouter
     */
    public function add(int $productId): void
    {
        $cart = $this->getSession()->get(self::CART_KEY, []);

        $cart[$productId] = ($cart[$productId] ?? 0) + 1;

        $this->getSession()->set(self::CART_KEY, $cart);
    }

    /**
     * Vide complètement le panier de la session.
     */
    public function clear(): void
    {
        $this->getSession()->remove(self::CART_KEY);
    }

    /**
     * Retourne les lignes du panier avec le produit, la quantité et le total de ligne.
     *
     * @return array<int, array{product: mixed, quantity: int, lineTotal: float}>
     */
    public function getItems(): array
    {
        $cart = $this->getSession()->get(self::CART_KEY, []);
        $items = [];

        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);

            if (!$product) {
                continue;
            }

            $items[] = [
                'product'   => $product,
                'quantity'  => $quantity,
                'lineTotal' => $product->getPrice() * $quantity,
            ];
        }

        return $items;
    }

    /**
     * Calcule le total du panier (hors frais de livraison).
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item['lineTotal'];
        }

        return $total;
    }

    /**
     * Retourne le montant des frais de livraison.
     */
    public function getShipping(): float
    {
        // frais de livraison fixes pour cette version
        return 4.5;
    }
}
