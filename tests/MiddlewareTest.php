<?php

// Session AVANT tout output
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers/env.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/helpers/response.php';
require_once __DIR__ . '/../app/services/JWTService.php';
require_once __DIR__ . '/../app/middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../app/middlewares/CSRFMiddleware.php';
require_once __DIR__ . '/../app/middlewares/RateLimitMiddleware.php';

use App\Services\JWTService;

echo "========================================\n";
echo "       MIDDLEWARE TEST SUITE\n";
echo "========================================\n\n";

$passed = 0;
$failed = 0;

function assert_test(string $name, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        echo "✅ {$name}\n";
        $passed++;
    } else {
        echo "❌ {$name}\n";
        $failed++;
    }
}

// =============================================
// TEST 1 — CSRFMiddleware
// =============================================
echo "--- CSRFMiddleware ---\n";

$token = \App\Middlewares\CSRFMiddleware::generateToken();
assert_test("Token CSRF généré", !empty($token));
assert_test("Token CSRF stocké en session", isset($_SESSION['csrf_token']));
assert_test("Token longueur correcte (64 hex)", strlen($token) === 64);

$token2 = \App\Middlewares\CSRFMiddleware::generateToken();
assert_test("Token CSRF stable (même session)", $token === $token2);

// =============================================
// TEST 2 — RateLimitMiddleware
// =============================================
echo "\n--- RateLimitMiddleware ---\n";

$_SERVER['REMOTE_ADDR'] = '192.168.1.100';
\App\Middlewares\RateLimitMiddleware::reset();

$result = \App\Middlewares\RateLimitMiddleware::handle();
assert_test("Première tentative autorisée", $result === true);

for ($i = 0; $i < 5; $i++) {
    \App\Middlewares\RateLimitMiddleware::increment();
}

ob_start();
$blocked = \App\Middlewares\RateLimitMiddleware::handle();
$output  = ob_get_clean();
assert_test("Bloqué après 5 échecs", $blocked === false || !empty($output));

\App\Middlewares\RateLimitMiddleware::reset();
$afterReset = \App\Middlewares\RateLimitMiddleware::handle();
assert_test("Reset fonctionne", $afterReset === true);

// =============================================
// TEST 3 — AuthMiddleware
// =============================================
echo "\n--- AuthMiddleware ---\n";

unset($_SERVER['HTTP_AUTHORIZATION']);
ob_start();
$result = \App\Middlewares\AuthMiddleware::handle();
ob_get_clean();
assert_test("Rejeté sans token", $result === false);

$validToken = JWTService::generate(['user_id' => 1, 'role' => 'admin', 'user_type' => 'admin']);
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $validToken;
$payload = \App\Middlewares\AuthMiddleware::handle(['admin']);
assert_test("Accepté avec token valide", $payload !== false);
assert_test("Payload role correct", isset($payload['role']) && $payload['role'] === 'admin');

ob_start();
$wrongRole = \App\Middlewares\AuthMiddleware::handle(['client']);
ob_get_clean();
assert_test("Rejeté avec mauvais rôle", $wrongRole === false);

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer tokeninvalide';
ob_start();
$invalid = \App\Middlewares\AuthMiddleware::handle();
ob_get_clean();
assert_test("Token falsifié rejeté", $invalid === false);

echo "\n========================================\n";
echo "Résultat : {$passed} ✅  |  {$failed} ❌\n";
echo "========================================\n";
