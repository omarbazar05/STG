<?php

namespace App\Models;

require_once __DIR__ . '/../helpers/security.php';

class Employee
{
    public static function findByEmail(string $email): array|false
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO employees (employee_id_hash, email, password_hash, name, position, department, hired_at)
             VALUES (:employee_id_hash, :email, :password_hash, :name, :position, :department, :hired_at)"
        );
        $stmt->execute([
            'employee_id_hash' => hashPassword($data['employee_id']),
            'email'            => $data['email'],
            'password_hash'    => hashPassword($data['password']),
            'name'             => $data['name'],
            'position'         => $data['position'] ?? null,
            'department'       => $data['department'] ?? null,
            'hired_at'         => $data['hired_at'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function verifyEmployeeId(string $idRaw, string $hash): bool
    {
        return verifyHash($idRaw, $hash);
    }

    public static function getAssignedClients(int $employeeId): array
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT DISTINCT c.* FROM clients c
             JOIN incidents i ON i.client_id = c.id
             WHERE i.assigned_employee = :employee_id"
        );
        $stmt->execute(['employee_id' => $employeeId]);
        return $stmt->fetchAll();
    }
}