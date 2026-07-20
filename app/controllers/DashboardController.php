<?php

namespace App\Controllers;

class DashboardController
{
    /**
     * GET /dashboard — affiche le squelette HTML (pas de vérif JWT ici)
     */
    public function index(): void
    {
        $pageTitle  = "Dashboard";
        $isLoggedIn = true; // on suppose connecté, le JS vérifiera vraiment
        $userRole   = null; // sera rempli côté JS après fetch

        ob_start();
        require __DIR__ . '/../../resources/views/pages/dashboard.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }

    /**
     * GET /api/dashboard-data — retourne les vraies données (protégé par AuthMiddleware via le routeur)
     */
    public function data(): void
    {
        // Si on arrive ici, AuthMiddleware a déjà validé le token (géré par index.php)
        // Mais il ne nous transmet pas $user, donc on revérifie nous-mêmes :
        require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
        $user = \App\Middlewares\AuthMiddleware::handle();

        if ($user === false) {
            return; // AuthMiddleware a déjà renvoyé l'erreur 401 JSON
        }

        header('Content-Type: application/json');
        echo json_encode([
            'user_id'   => $user['user_id']   ?? null,
            'user_type' => $user['user_type'] ?? null,
            'role'      => $user['role']      ?? null,
            'message'   => 'Bienvenue sur votre dashboard.',
        ]);
    }
}