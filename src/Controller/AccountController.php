<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OrderRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $orders = $orderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('account/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/account/api/toggle', name: 'app_account_api_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleApiAccess(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $user->setApiAccess(!$user->isApiAccess());
        $entityManager->flush();

        $message = $user->isApiAccess()
            ? 'Votre accès API a été activé.'
            : 'Votre accès API a été désactivé.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_account');
    }

    #[Route('/account/delete', name: 'app_account_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Supprimer d'abord les commandes de l'utilisateur
        $orders = $orderRepository->findBy(['user' => $user]);
        foreach ($orders as $order) {
            $entityManager->remove($order);
        }

        // Puis supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnexion propre : on efface le token de sécurité + la session
        $tokenStorage->setToken(null);
        $requestStack->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte et vos commandes ont été supprimés.');

        return $this->redirectToRoute('app_home');
    }
}
