<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Order;
use App\Entity\OrderItem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        $items = $cartService->getItems();
        $total = $cartService->getTotal();
        $shipping = $cartService->getShipping();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $items,
            'cartTotal' => $total,
            'shipping'  => $shipping,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'])]
    public function add(int $id, ProductRepository $productRepository, CartService $cartService): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $cartService->add($id);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Votre panier a été vidé.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/validate', name: 'app_cart_validate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function validate(CartService $cartService, EntityManagerInterface $entityManager): Response
    {
        $items = $cartService->getItems();

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setOrderNumber('GG-' . uniqid());

        $total = 0;

        foreach ($items as $cartItem) {
            $product = $cartItem['product'];
            $quantity = $cartItem['quantity'];

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setUnitPrice($product->getPrice());

            $order->addItem($orderItem);
            $entityManager->persist($orderItem);

            $total += $product->getPrice() * $quantity;
        }

        // total commande = produits + frais de port
        $totalWithShipping = $total + $cartService->getShipping();
        $order->setTotalPrice($totalWithShipping);

        $entityManager->persist($order);
        $entityManager->flush();

        $cartService->clear();

        $this->addFlash('success', 'Votre commande a bien été enregistrée.');
        return $this->redirectToRoute('app_account');
    }
}
