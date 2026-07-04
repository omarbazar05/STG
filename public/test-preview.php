<?php
$isLoggedIn = false;
$userRole = null;
$pageTitle = "Stages";

ob_start();
include __DIR__ . '/../resources/views/pages/internships.php';
$content = ob_get_clean();

include __DIR__ . '/../resources/views/layouts/main.php';