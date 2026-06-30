<?php

namespace App\Middlewares;

class RateLimitMiddleware
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300; // 5 minutes

    public static function handle(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$ip}";

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'locked_until' => 0];
        }

        $data = $_SESSION[$key];

        if ($data['locked_until'] > time()) {
            errorResponse('Trop de tentatives. Réessayez plus tard.', 429);
            return false;
        }

        if ($data['count'] >= self::MAX_ATTEMPTS) {
            $_SESSION[$key]['locked_until'] = time() + self::LOCK_SECONDS;
            $_SESSION[$key]['count'] = 0;
            errorResponse('Trop de tentatives. Compte verrouillé temporairement.', 429);
            return false;
        }

        return true;
    }

    public static function increment(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$ip}";
        $_SESSION[$key]['count'] = ($_SESSION[$key]['count'] ?? 0) + 1;
    }

    public static function reset(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$ip}";
        unset($_SESSION[$key]);
    }
}