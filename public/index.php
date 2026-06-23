<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Le router sera branché ici en Phase 5 (routes/web.php + api.php)
echo "App initialisée";