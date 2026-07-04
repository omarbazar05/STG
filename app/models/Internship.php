<?php

require_once __DIR__ . '/../../config/database.php';

class Internship {

    private static function getDB(): PDO {
        return Database::getInstance();
    }

    // Récupère tous les stages ouverts
    public static function getAllOpen(): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, title, description, requirements, status, created_at 
             FROM cms_stages 
             WHERE status = 'open' 
             ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un stage par son id
    public static function findById(int $id): array|false {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, title, description, requirements, status, created_at 
             FROM cms_stages 
             WHERE id = :id 
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupère tous les stages (pour l'admin)
    public static function getAll(): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, title, description, requirements, status, created_at 
             FROM cms_stages 
             ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}