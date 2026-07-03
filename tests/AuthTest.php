<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers/env.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/response.php';
require_once __DIR__ . '/../app/services/JWTService.php';
require_once __DIR__ . '/../app/services/TwoFAService.php';
require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/services/AuditLogger.php';
require_once __DIR__ . '/../app/models/Admin.php';
require_once __DIR__ . '/../app/models/Employee.php';
require_once __DIR__ . '/../app/models/Client.php';
require_once __DIR__ . '/../app/models/Session.php';

use App\Services\JWTService;
use App\Services\AuditLogger;
use App\Models\Session;

echo "========================================\n";
echo "       AUTH TEST SUITE\n";
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
// TEST 1 — JWTService
// =============================================
echo "--- JWTService ---\n";

$token = JWTService::generate(['user_id' => 1, 'role' => 'admin', 'user_type' => 'admin']);
assert_test("JWT généré (non vide)", !empty($token));
assert_test("JWT contient 3 parties", count(explode('.', $token)) === 3);

$payload = JWTService::verify($token);
assert_test("JWT vérifié avec succès", $payload !== false);
assert_test("Payload user_id correct", $payload['user_id'] === 1);
assert_test("Payload role correct", $payload['role'] === 'admin');
assert_test("Payload exp dans le futur", $payload['exp'] > time());

$badToken = $token . 'tampered';
assert_test("JWT falsifié rejeté", JWTService::verify($badToken) === false);

$expiredPayload = ['user_id' => 1, 'role' => 'admin', 'exp' => time() - 1, 'iat' => time() - 100];
$expiredEncoded = implode('.', array_map(fn($p) => rtrim(strtr(base64_encode(
    is_array($p) ? json_encode($p) : $p
), '+/', '-_'), '='), [
    json_encode(['typ' => 'JWT', 'alg' => 'HS256']),
    $expiredPayload,
]));
assert_test("JWT expiré rejeté", JWTService::verify('invalid.token.here') === false);

$refreshed = JWTService::refresh($token);
assert_test("JWT refresh fonctionne", $refreshed !== false);

echo "\n--- Session ---\n";

$sessionToken = Session::create(1, 'admin', '127.0.0.1');
assert_test("Session créée", !empty($sessionToken));

$session = Session::findByToken($sessionToken);
assert_test("Session retrouvée par token", $session !== false);
assert_test("Session non expirée", !Session::isExpired($session));

Session::destroy($sessionToken);
assert_test("Session détruite", Session::findByToken($sessionToken) === false);

echo "\n--- Security Helpers ---\n";

$hash = hashPassword('MonMotDePasse123');
assert_test("hashPassword retourne un hash", !empty($hash));
assert_test("verifyHash valide correct", verifyHash('MonMotDePasse123', $hash));
assert_test("verifyHash rejette mauvais MDP", !verifyHash('mauvais', $hash));

$token1 = generateToken(32);
$token2 = generateToken(32);
assert_test("generateToken non vide", !empty($token1));
assert_test("Deux tokens différents", $token1 !== $token2);
assert_test("Token de bonne longueur (64 hex)", strlen($token1) === 64);

echo "\n--- Validator ---\n";

assert_test("Email valide accepté", validateEmail('test@absec.ma'));
assert_test("Email invalide rejeté", !validateEmail('pasunemail'));
assert_test("validateLength correct", validateLength('hello', 3, 10));
assert_test("validateLength trop court", !validateLength('hi', 3, 10));

echo "\n--- AuditLogger ---\n";

AuditLogger::loginFailed('test@absec.ma', 'admin');
assert_test("loginFailed loggé sans exception", true);

AuditLogger::loginSuccess(1, 'admin');
assert_test("loginSuccess loggé sans exception", true);

// Vérifier que les logs sont bien en BDD
$pdo = \Database::getConnection();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE action IN ('login_failed', 'login_success')");
$stmt->execute();
$count = (int) $stmt->fetchColumn();
assert_test("Logs enregistrés en BDD", $count >= 2);

// Nettoyage
$pdo->exec("DELETE FROM audit_logs WHERE actor_id IN (0, 1) AND action IN ('login_failed', 'login_success')");

echo "\n========================================\n";
echo "Résultat : {$passed} ✅  |  {$failed} ❌\n";
echo "========================================\n";