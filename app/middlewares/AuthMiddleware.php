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
    // getallheaders() n'existe pas en CLI — on utilise $_SERVER comme fallback
    if (function_exists('getallheaders')) {
        $headers     = getallheaders();
        $authHeader  = $headers['Authorization'] ?? '';
    } else {
        // En CLI (tests) : on lit depuis $_SERVER
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    }

    if (!str_starts_with($authHeader, 'Bearer ')) {
        errorResponse('Non authentifié.', 401);
        return false;
    }

    $token   = substr($authHeader, 7);
    $payload = \App\Services\JWTService::verify($token);

    if ($payload === false) {
        errorResponse('Token invalide ou expiré.', 401);
        return false;
    }

    if (!empty($allowedRoles) && !in_array($payload['role'], $allowedRoles)) {
        errorResponse('Accès refusé.', 403);
        return false;
    }

    return $payload;
}

}