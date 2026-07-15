<?php
namespace App\Controllers;

class HomeController
{
    /**
     * GET / — page d'accueil
     */
    public function index(): void
    {
        $pageTitle  = "Accueil";
        $isLoggedIn = false; // TODO: remplacer par vraie vérification session/JWT
        $userRole   = null;

        ob_start();
        require __DIR__ . '/../../resources/views/pages/home.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }

    /**
     * GET /pricing — page tarifs
     */
    public function pricing(): void
    {
        $pageTitle  = "Tarifs";
        $isLoggedIn = false; // TODO: remplacer par vraie vérification session/JWT
        $userRole   = null;

        ob_start();
        require __DIR__ . '/../../resources/views/pages/pricing.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }
}