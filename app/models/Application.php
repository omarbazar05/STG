<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Internship.php';

class Application {

    private static function getDB(): PDO {
        return Database::getConnection();
    }

    // Crée une nouvelle candidature
    public static function create(array $data): int|false {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO applications 
                (internship_id, name, email, cv_path, status, created_at)
             VALUES 
                (:internship_id, :name, :email, :cv_path, 'pending', NOW())"
        );

        $success = $stmt->execute([
            ':internship_id' => $data['internship_id'],
            ':name'          => $data['name'],
            ':email'         => $data['email'],
            ':cv_path'       => $data['cv_path'],
        ]);

        return $success ? (int) $db->lastInsertId() : false;
    }

    // Vérifie si un email a déjà postulé pour ce stage
    public static function alreadyApplied(int $internshipId, string $email): bool {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM applications 
             WHERE internship_id = :internship_id 
             AND email = :email"
        );
        $stmt->execute([
            ':internship_id' => $internshipId,
            ':email'         => $email,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }
}