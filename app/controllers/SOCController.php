<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/SOCConfig.php';

class SOCController
{
    /**
     * GET /soc-config — squelette HTML
     */
    public function index(): void
    {
        $pageTitle  = "Configuration SOC";
        $isLoggedIn = true;
        $userRole   = null;

        ob_start();
        require __DIR__ . '/../../resources/views/pages/soc_config.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }

    /**
     * GET /api/soc-config — retourne la config selon le rôle (isolation stricte)
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

        if ($role === 'client') {
            $config = \SOCConfig::findByClientId($userId);
            echo json_encode([
                'role'   => $role,
                'config' => $config ?: null,
            ]);
            return;
        }

        if ($role === 'admin') {
            $configs = \SOCConfig::getAll();
            echo json_encode([
                'role'    => $role,
                'configs' => $configs,
            ]);
            return;
        }

        // Employé : pas de config SOC personnelle, accès refusé
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé pour ce rôle.']);
    }
}