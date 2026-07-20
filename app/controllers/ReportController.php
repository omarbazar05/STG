<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/Incident.php';

class ReportController
{
    /**
     * GET /reports — squelette HTML
     */
    public function index(): void
    {
        $pageTitle  = "Rapports";
        $isLoggedIn = true;
        $userRole   = null;

        ob_start();
        require __DIR__ . '/../../resources/views/pages/reports.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }

    /**
     * GET /api/reports — statistiques selon le rôle
     */
    public function data(): void
    {
        require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
        $user = \App\Middlewares\AuthMiddleware::handle();

        if ($user === false) {
            return;
        }

        $role   = $user['role']    ?? $user['user_type'] ?? null;
        $userId = (int) ($user['user_id'] ?? 0);

        header('Content-Type: application/json');

        $stats = match ($role) {
            'client' => \Incident::getStatsByClientId($userId),
            'admin'  => \Incident::getGlobalStats(),
            default  => null,
        };

        if ($stats === null) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé pour ce rôle.']);
            return;
        }

        echo json_encode([
            'role'  => $role,
            'stats' => $stats,
        ]);
    }
}