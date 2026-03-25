<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur d'authentification pour l'API (route /api/login).
 */
class ApiAuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JwtService $jwtService
    ): JsonResponse {
        // On récupère le corps JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifie que le JSON est valide et que les champs attendus sont présents
        if (!is_array($data) || !isset($data['username'], $data['password'])) {
            // Tu peux choisir 400 ou 401 pour ce cas, mais 400 est plus "logique"
            return new JsonResponse(['error' => 'Requête invalide'], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username']; // ici on interprète "username" comme l’e-mail
        $password = $data['password'];

        // Récupère l'utilisateur par e-mail (champ "email" dans l’entité User)
        /** @var User|null $user */
        $user = $userRepository->findOneBy(['email' => $username]);

        // Si aucun utilisateur trouvé : identifiants incorrects -> 401
        if (!$user) {
            return new JsonResponse(['error' => 'Identifiants incorrects'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifie le mot de passe à l'aide du composant de hash Symfony
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            // Mot de passe incorrect -> 401
            return new JsonResponse(['error' => 'Identifiants incorrects'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifie que l'accès API est bien activé pour cet utilisateur
        if (!$user->isApiAccess()) {
            // Accès API non activé -> 403
            return new JsonResponse(['error' => 'Accès API non activé'], Response::HTTP_FORBIDDEN);
        }

        // Tout est OK : on génère un token JWT pour cet utilisateur
        $token = $jwtService->generateToken($user);

        // Réponse conforme à la spec : statut 200 + body { "token": "..." }
        return new JsonResponse(
            ['token' => $token],
            Response::HTTP_OK
        );
    }
}