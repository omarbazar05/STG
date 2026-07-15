<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/Internship.php';
require_once __DIR__ . '/../models/Application.php';

class ApplicationController
{
    /**
     * GET/POST /apply/{id} — formulaire de candidature + traitement
     */
    public function apply(string $id): void
    {
        $pageTitle     = "Postuler";
        $isLoggedIn    = false;
        $userRole      = null;
        $internshipId  = (int) $id;

        ob_start();
        require __DIR__ . '/../../resources/views/pages/apply.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }
}