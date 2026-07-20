<?php

require_once __DIR__ . '/../../config/database.php';

class SOCConfig {

    private static function getDB(): PDO {
        return Database::getConnection();
    }

    // Récupère la config SOC d'un client précis
    public static function findByClientId(int $clientId): array|false {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, client_id, siem, soar, ti, dashboard_url, updated_at
             FROM soc_configs
             WHERE client_id = :client_id
             LIMIT 1"
        );
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Toutes les configs (admin uniquement)
    public static function getAll(): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, client_id, siem, soar, ti, dashboard_url, updated_at
             FROM soc_configs
             ORDER BY updated_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}