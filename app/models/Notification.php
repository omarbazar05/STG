<?php

namespace App\Models;

class Notification
{
    public static function create(
        int    $recipientId,
        string $recipientType,
        string $type,
        string $message,
        string $link = null
    ): int {
        $pdo = \Database::getConnection();

        $stmt = $pdo->prepare(
            "INSERT INTO notifications (recipient_id, recipient_type, type, message, link)
             VALUES (:recipient_id, :recipient_type, :type, :message, :link)"
        );
        $stmt->execute([
            'recipient_id'   => $recipientId,
            'recipient_type' => $recipientType,
            'type'           => $type,
            'message'        => $message,
            'link'           => $link,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function getForRecipient(int $recipientId, string $recipientType, bool $unreadOnly = false): array
    {
        $pdo = \Database::getConnection();

        $sql = "SELECT * FROM notifications
                WHERE recipient_id = :recipient_id
                AND recipient_type = :recipient_type";

        if ($unreadOnly) {
            $sql .= " AND is_read = FALSE";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'recipient_id'   => $recipientId,
            'recipient_type' => $recipientType,
        ]);

        return $stmt->fetchAll();
    }

    public static function markAsRead(int $id): void
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function markAllAsRead(int $recipientId, string $recipientType): void
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE notifications SET is_read = TRUE
             WHERE recipient_id = :recipient_id
             AND recipient_type = :recipient_type"
        );
        $stmt->execute([
            'recipient_id'   => $recipientId,
            'recipient_type' => $recipientType,
        ]);
    }

    public static function countUnread(int $recipientId, string $recipientType): int
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM notifications
             WHERE recipient_id = :recipient_id
             AND recipient_type = :recipient_type
             AND is_read = FALSE"
        );
        $stmt->execute([
            'recipient_id'   => $recipientId,
            'recipient_type' => $recipientType,
        ]);

        return (int) $stmt->fetchColumn();
    }
}