<?php
// FICHIER TEMPORAIRE DE TEST — à supprimer une fois le vrai routeur de P1 prêt

$isLoggedIn = false;
$userRole = "null"; // changez en "admin" ou "employee" pour tester les autres vues
$pageTitle = "Blog";

ob_start();
include __DIR__ . '/../resources/views/pages/blog.php';
$content = ob_get_clean();

include __DIR__ . '/../resources/views/layouts/main.php';