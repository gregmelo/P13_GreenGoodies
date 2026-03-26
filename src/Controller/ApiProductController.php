<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Contrôleur pour les routes produits de l'API.
 */
class ApiProductController extends AbstractController
{
    /**
     * Retourne la liste des produits au format JSON.
     *
     * Cette route est protégée par un token JWT passé dans le header
     * "Authorization: Bearer <token>".
     */
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function list(
        Request $request,
        JwtService $jwtService,
        UserRepository $userRepository,
        ProductRepository $productRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        // Récupération du header Authorization
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            // Pas de token fourni -> 401
            return new JsonResponse(
                ['error' => 'Token manquant'],
                Response::HTTP_UNAUTHORIZED
            );
            // (Dans la vraie vie, tu pourrais mettre un message plus neutre)
        }

        // Extraction du token sans le préfixe "Bearer "
        $token = substr($authorizationHeader, 7);

        // Décodage / validation du token
        $payload = $jwtService->decodeToken($token);

        if ($payload === null) {
            // Token invalide ou expiré -> 401
            return new JsonResponse(
                ['error' => 'Token invalide ou expiré'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Récupération de l'utilisateur à partir de l'ID contenu dans le token (champ "sub")
        $userId = $payload['sub'] ?? null;

        if ($userId === null) {
            return new JsonResponse(
                ['error' => 'Token invalide'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        /** @var User|null $user */
        $user = $userRepository->find($userId);

        if (!$user) {
            // L'utilisateur du token n'existe plus -> 401
            return new JsonResponse(
                ['error' => 'Utilisateur introuvable'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Vérifie que l'accès API est toujours activé
        if (!$user->isApiAccess()) {
            // Même logique que pour /api/login -> 403
            return new JsonResponse(
                ['error' => 'Accès API non activé'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Récupération de tous les produits
        $products = $productRepository->findAll();

        // Sérialisation avec le Serializer Symfony
        $json = $serializer->serialize($products, 'json', [
            'groups' => ['product:list'],
        ]);

        // Réponse 200 avec JSON déjà sérialisé
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
