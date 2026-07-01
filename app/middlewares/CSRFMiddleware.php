<?php

namespace App\Middlewares;

class CSRFMiddleware
{
    public static function generateToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function handle(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return true;
        }

        $tokenFromRequest = $_POST['_token']
            ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!$tokenFromRequest || empty($_SESSION['csrf_token'])) {
            errorResponse('Token CSRF manquant.', 403);
            return false;
        }

        if (!hash_equals($_SESSION['csrf_token'], $tokenFromRequest)) {
            errorResponse('Token CSRF invalide.', 403);
            return false;
        }

        return true;
    }
}