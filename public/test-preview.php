<?php
// FICHIER TEMPORAIRE DE TEST — à supprimer une fois le vrai routeur de P1 prêt

$isLoggedIn = true;
$userRole = "client"; // changez en "admin" ou "employee" pour tester les autres vues
$pageTitle = "Accueil";

ob_start();
include __DIR__ . '/../resources/views/pages/home.php';
$content = ob_get_clean();

include __DIR__ . '/../resources/views/layouts/main.php';