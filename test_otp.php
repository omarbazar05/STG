<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers/env.php';
require_once __DIR__ . '/app/services/TwoFAService.php';
require_once __DIR__ . '/config/database.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\TwoFAService;

// Test : envoie un OTP factice à une adresse test
$result = TwoFAService::sendOTP('test@example.com', '123456');

if ($result) {
    echo "✅ Email envoyé avec succès (vérifie ta boîte Mailtrap)\n";
} else {
    echo "❌ Échec de l'envoi (vérifie les logs PHP / identifiants SMTP)\n";
}
