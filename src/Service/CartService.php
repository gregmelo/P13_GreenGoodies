<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private const CART_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    ) {}

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new \RuntimeException('Aucune requête courante disponible pour accéder à la session.');
        }

        return $request->getSession();
    }

    public function add(int $productId): void
    {
        $cart = $this->getSession()->get(self::CART_KEY, []);

        $cart[$productId] = ($cart[$productId] ?? 0) + 1;

        $this->getSession()->set(self::CART_KEY, $cart);
    }

    public function clear(): void
    {
        $this->getSession()->remove(self::CART_KEY);
    }

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

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item['lineTotal'];
        }

        return $total;
    }

    public function getShipping(): float
    {
        // frais de livraison fixes pour cette version
        return 4.5;
    }
}
