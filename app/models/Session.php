<?php

namespace App\Models;

require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/env.php';

class Session
{
    public static function create(int $userId, string $userType, string $ip = null): string
    {
        $pdo = \Database::getConnection();

        // Génère un token brut aléatoire (envoyé au client)
        $tokenRaw  = generateToken(32);
        $tokenHash = hash('sha256', $tokenRaw);

        // Durée selon le rôle
        $ttl = match ($userType) {
            'admin'    => 28800,  // 8h
            'employee' => 28800,  // 8h
            'client'   => 86400,  // 24h
            default    => 28800,
        };

        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

        $stmt = $pdo->prepare(
            "INSERT INTO sessions (user_id, user_type, token_hash, ip_address, expires_at)
             VALUES (:user_id, :user_type, :token_hash, :ip_address, :expires_at)"
        );
        $stmt->execute([
            'user_id'    => $userId,
            'user_type'  => $userType,
            'token_hash' => $tokenHash,
            'ip_address' => $ip,
            'expires_at' => $expiresAt,
        ]);

        return $tokenRaw; // On retourne le token brut au client, jamais le hash
    }

    public static function findByToken(string $tokenRaw): array|false
    {
        $pdo = \Database::getConnection();
        $tokenHash = hash('sha256', $tokenRaw);

        $stmt = $pdo->prepare(
            "SELECT * FROM sessions
             WHERE token_hash = :token_hash
             LIMIT 1"
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        return $stmt->fetch();
    }

    public static function isExpired(array $session): bool
    {
        return strtotime($session['expires_at']) < time();
    }

    public static function destroy(string $tokenRaw): void
    {
        $pdo = \Database::getConnection();
        $tokenHash = hash('sha256', $tokenRaw);

        $stmt = $pdo->prepare("DELETE FROM sessions WHERE token_hash = :token_hash");
        $stmt->execute(['token_hash' => $tokenHash]);
    }

    public static function cleanExpired(): int
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}