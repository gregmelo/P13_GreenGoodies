<?php

namespace App\Service;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Service responsable de la génération et de la validation
 * des tokens JWT utilisés par l'API.
 */
class JwtService
{
    /**
     * @param string $secret Clé secrète utilisée pour signer/valider les tokens
     * @param int    $ttl    Durée de vie des tokens en secondes (time-to-live)
     */
    public function __construct(
        private string $secret,
        private int $ttl
    ) {
    }

    /**
     * Génère un token JWT pour un utilisateur donné.
     *
     * Le token contient :
     *  - sub  : l'identifiant de l'utilisateur (subject)
     *  - email: l'e-mail de l'utilisateur
     *  - iat  : date d'émission du token (issued at)
     *  - exp  : date d’expiration du token (expiration)
     *
     * @param User $user L'utilisateur pour lequel on génère le token
     *
     * @return string Le token JWT signé (chaîne compacte)
     */
    public function generateToken(User $user): string
    {
        // Timestamp actuel (en secondes)
        $now = time();

        // Données (payload) embarquées dans le token
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ];

        // Génération du token signé avec l'algorithme HS256
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Tente de décoder et vérifier un token JWT.
     *
     * - Si le token est valide et non expiré, on renvoie son payload sous forme de tableau.
     * - Si le token est invalide, corrompu ou expiré, on renvoie null.
     *
     * @param string $token Le token JWT reçu (par exemple depuis un header Authorization)
     *
     * @return array<string, mixed>|null Le payload décodé ou null si le token est invalide
     */
    public function decodeToken(string $token): ?array
    {
        try {
            // Vérifie la signature + la date d’expiration avec la même clé et l’algorithme HS256
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

            // L’objet retourné par la librairie est converti en tableau associatif
            return (array) $decoded;
        } catch (\Throwable $e) {
            // En cas d’erreur (signature invalide, token expiré, token mal formé, etc.)
            // on renvoie simplement null, ce qui permettra au contrôleur de
            // répondre 401 Unauthorized ou 403 Forbidden selon le cas.
            return null;
        }
    }
}