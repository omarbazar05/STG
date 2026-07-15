<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/Internship.php';

class InternshipController
{
    /**
     * GET /internships — liste des stages ouverts
     */
    public function index(): void
    {
        $pageTitle  = "Stages";
        $isLoggedIn = false; // TODO: vraie vérification session/JWT
        $userRole   = null;

        $openInternships = \Internship::getAllOpen();

        ob_start();
        require __DIR__ . '/../../resources/views/pages/internships.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }
}