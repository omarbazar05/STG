<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../helpers/response.php';

use App\Models\Notification;

class NotificationController
{
    /**
     * GET /api/notifications — toutes les notifs de l'utilisateur connecté
     */
    public function index(): void
    {
        $user    = $this->getAuthUser();
        $notifs  = Notification::getForRecipient($user['user_id'], $user['user_type']);
        jsonResponse(['notifications' => $notifs]);
    }

    /**
     * GET /api/notifications/unread — notifs non lues uniquement
     */
    public function unread(): void
    {
        $user   = $this->getAuthUser();
        $notifs = Notification::getForRecipient($user['user_id'], $user['user_type'], unreadOnly: true);
        $count  = Notification::countUnread($user['user_id'], $user['user_type']);

        jsonResponse([
            'notifications' => $notifs,
            'count'         => $count,
        ]);
    }

    /**
     * PUT /api/notifications/{id} — marquer une notif comme lue
     */
    public function markRead(int $id): void
    {
        Notification::markAsRead($id);
        jsonResponse(['message' => 'Notification marquée comme lue.']);
    }

    /**
     * PUT /api/notifications/all — marquer toutes les notifs comme lues
     */
    public function markAllRead(): void
    {
        $user = $this->getAuthUser();
        Notification::markAllAsRead($user['user_id'], $user['user_type']);
        jsonResponse(['message' => 'Toutes les notifications marquées comme lues.']);
    }

    /**
     * Récupère l'utilisateur authentifié depuis le JWT (injecté par AuthMiddleware)
     */
    private function getAuthUser(): array
    {
        $headers    = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        $token      = substr($authHeader, 7);

        return \App\Services\JWTService::verify($token);
    }
}