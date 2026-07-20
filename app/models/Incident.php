<?php

require_once __DIR__ . '/../../config/database.php';

class Incident {

    private static function getDB(): PDO {
        return Database::getConnection();
    }

    // Incidents d'un client précis
    public static function findByClientId(int $clientId): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, client_id, assigned_employee, severity, description, status, detected_at, resolved_at
             FROM incidents
             WHERE client_id = :client_id
             ORDER BY detected_at DESC"
        );
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Incidents assignés à un employé précis
    public static function findByEmployeeId(int $employeeId): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, client_id, assigned_employee, severity, description, status, detected_at, resolved_at
             FROM incidents
             WHERE assigned_employee = :employee_id
             ORDER BY detected_at DESC"
        );
        $stmt->execute([':employee_id' => $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tous les incidents (admin uniquement)
    public static function getAll(): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, client_id, assigned_employee, severity, description, status, detected_at, resolved_at
             FROM incidents
             ORDER BY detected_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Un incident précis par id (avec vérif d'appartenance possible côté controller)
    public static function findById(int $id): array|false {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, client_id, assigned_employee, severity, description, status, detected_at, resolved_at
             FROM incidents
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Statistiques agrégées pour un client
    public static function getStatsByClientId(int $clientId): array {
        $db = self::getDB();

        $stmt = $db->prepare(
            "SELECT severity, COUNT(*) as total
             FROM incidents
             WHERE client_id = :client_id
             GROUP BY severity"
        );
        $stmt->execute([':client_id' => $clientId]);
        $bySeverity = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare(
            "SELECT status, COUNT(*) as total
             FROM incidents
             WHERE client_id = :client_id
             GROUP BY status"
        );
        $stmt->execute([':client_id' => $clientId]);
        $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'by_severity' => $bySeverity,
            'by_status'   => $byStatus,
        ];
    }

    // Statistiques globales (admin)
    public static function getGlobalStats(): array {
        $db = self::getDB();

        $stmt = $db->prepare(
            "SELECT severity, COUNT(*) as total FROM incidents GROUP BY severity"
        );
        $stmt->execute();
        $bySeverity = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare(
            "SELECT status, COUNT(*) as total FROM incidents GROUP BY status"
        );
        $stmt->execute();
        $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'by_severity' => $bySeverity,
            'by_status'   => $byStatus,
        ];
    }
    
}
