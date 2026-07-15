<?php

require_once __DIR__ . '/../../config/database.php';

class BlogPost {

    private static function getDB(): PDO {
        return Database::getConnection();
    }

    // Récupère tous les articles publiés, du plus récent au plus ancien
    public static function getAllPublished(): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, title, slug, content, published_at, author_id 
             FROM cms_blog 
             WHERE status = 'published' 
             ORDER BY published_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un article par son slug (pour blog_show.php)
    public static function findBySlug(string $slug): array|false {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, title, slug, content, status, published_at, author_id 
             FROM cms_blog 
             WHERE slug = :slug 
             LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupère tous les articles (pour l'admin, y compris les brouillons)
    public static function getAll(): array {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT id, title, slug, status, published_at, author_id 
             FROM cms_blog 
             ORDER BY published_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}