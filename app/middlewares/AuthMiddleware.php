<?php

namespace App\Middlewares;

use App\Services\JWTService;

class AuthMiddleware
{
    /**
     * @param array $allowedRoles Rôles autorisés pour cette route (vide = tous connectés)
     */
    public static function handle(array $allowedRoles = []): array|false
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            errorResponse('Non authentifié.', 401);
            return false;
        }

        $token = substr($authHeader, 7);
        $payload = JWTService::verify($token);

        if ($payload === false) {
            errorResponse('Token invalide ou expiré.', 401);
            return false;
        }

        if (!empty($allowedRoles) && !in_array($payload['role'], $allowedRoles)) {
            errorResponse('Accès refusé.', 403);
            return false;
        }

        return $payload; // contient role, user_id, user_type...
    }
}