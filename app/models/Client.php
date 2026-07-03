<?php

namespace App\Models;
use function hashPassword;
use function verifyHash;

require_once __DIR__ . '/../helpers/security.php';

class Client
{
    public static function findByEmail(string $email): array|false
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO clients (client_id_hash, email, password_hash, company_name, contact_name, phone, status)
             VALUES (:client_id_hash, :email, :password_hash, :company_name, :contact_name, :phone, :status)"
        );
        $stmt->execute([
            'client_id_hash' => hashPassword($data['client_id']),
            'email'          => $data['email'],
            'password_hash'  => hashPassword($data['password']),
            'company_name'   => $data['company_name'],
            'contact_name'   => $data['contact_name'],
            'phone'          => $data['phone'] ?? null,
            'status'         => $data['status'] ?? 'pending',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function verifyClientId(string $idRaw, string $hash): bool
    {
        return verifyHash($idRaw, $hash);
    }

    public static function getIncidents(int $clientId): array
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM incidents WHERE client_id = :client_id ORDER BY detected_at DESC");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }
}