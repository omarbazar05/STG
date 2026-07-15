<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$isLoggedIn = false;
$userRole = null;
$pageTitle = "Connexion";

ob_start();
include __DIR__ . '/../resources/views/pages/login.php';
$content = ob_get_clean();

include __DIR__ . '/../resources/views/layouts/main.php';