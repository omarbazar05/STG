<?php

namespace App\Models;
use function hashPassword;
use function verifyHash;

require_once __DIR__ . '/../helpers/security.php';

class Admin
{
    public static function findByEmail(string $email): array|false
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO admins (admin_id_hash, email, password_hash, name, role)
             VALUES (:admin_id_hash, :email, :password_hash, :name, :role)"
        );
        $stmt->execute([
            'admin_id_hash' => hashPassword($data['admin_id']),
            'email'         => $data['email'],
            'password_hash' => hashPassword($data['password']),
            'name'          => $data['name'],
            'role'          => $data['role'] ?? 'admin',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function verifyAdminId(string $idRaw, string $hash): bool
    {
        return verifyHash($idRaw, $hash);
    }

    public static function updateLastLogin(int $id): void
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}