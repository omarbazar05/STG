<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/Incident.php';

class IncidentController
{
    /**
     * GET /incidents — squelette HTML (pas de vérif JWT ici)
     */
    public function index(): void
    {
        $pageTitle  = "Incidents";
        $isLoggedIn = true;
        $userRole   = null;

        ob_start();
        require __DIR__ . '/../../resources/views/pages/incidents.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }

    /**
     * GET /api/incidents — retourne les incidents selon le rôle (isolation stricte)
     */
    public function data(): void
    {
        require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
        $user = \App\Middlewares\AuthMiddleware::handle();

        if ($user === false) {
            return; // 401 déjà envoyé par AuthMiddleware
        }

        $role   = $user['role']    ?? $user['user_type'] ?? null;
        $userId = (int) ($user['user_id'] ?? 0);

        // Isolation stricte selon le rôle — jamais d'accès croisé
        $incidents = match ($role) {
            'client'   => \Incident::findByClientId($userId),
            'employee' => \Incident::findByEmployeeId($userId),
            'admin'    => \Incident::getAll(),
            default    => [],
        };

        header('Content-Type: application/json');
        echo json_encode([
            'role'      => $role,
            'incidents' => $incidents,
        ]);
    }
}