<?php

namespace App\Controllers;

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/../services/TwoFAService.php';
require_once __DIR__ . '/../middlewares/RateLimitMiddleware.php';
require_once __DIR__ . '/../middlewares/CSRFMiddleware.php';
require_once __DIR__ . '/../models/Session.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/response.php';

use App\Services\AuthService;
use App\Services\JWTService;
use App\Services\TwoFAService;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\CSRFMiddleware;
use App\Models\Session;

class AuthController
{
    /**
     * GET /login — affiche la page de login
     */
   public function showLogin(): void
{
    $pageTitle  = "Connexion";
    $isLoggedIn = false;
    $userRole   = null;

    ob_start();
    require __DIR__ . '/../../resources/views/pages/login.php';
    $content = ob_get_clean();

    require __DIR__ . '/../../resources/views/layouts/main.php';
}

    /**
     * POST /api/login — authentification 3 critères + déclenchement OTP
     */
    public function login(): void
    {
        // 1. Rate limiting
        RateLimitMiddleware::handle();

        // 2. Lire et valider les inputs
        $body = json_decode(file_get_contents('php://input'), true);

        $email     = sanitize($body['email']     ?? '');
        $password  = $body['password']            ?? '';
        $idRaw     = $body['id']                  ?? '';
        $userType  = sanitize($body['user_type']  ?? '');

        if (!validateEmail($email) || empty($password) || empty($idRaw)) {
            errorResponse('Champs requis manquants ou invalides.', 422);
            return;
        }

        if (!in_array($userType, ['admin', 'employee', 'client'])) {
            errorResponse('Type utilisateur invalide.', 422);
            return;
        }

        // 3. Tenter le login (3 vérifications bcrypt)
        $result = AuthService::login($email, $password, $idRaw, $userType);

        if (!$result['success']) {
            RateLimitMiddleware::increment();
            // Délai constant pour éviter les timing attacks
            usleep(random_int(200000, 400000));
            errorResponse('Identifiants invalides.', 401);
            return;
        }

        // 4. OTP envoyé → on ne retourne pas de token JWT ici
        // Le client doit passer par POST /api/verify-otp
        jsonResponse([
            'message'      => 'Code OTP envoyé par email.',
            'requires_otp' => true,
            'user_type'    => $userType,
            'user_id'      => $result['user']['id'],
        ]);
    }

    /**
     * POST /api/verify-otp — vérification OTP + génération JWT + création session
     */
    public function verifyOtp(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        $userId   = (int) ($body['user_id']   ?? 0);
        $userType = sanitize($body['user_type'] ?? '');
        $code     = sanitize($body['code']      ?? '');

        if (!$userId || empty($userType) || empty($code)) {
            errorResponse('Champs requis manquants.', 422);
            return;
        }

        // Vérifier l'OTP (hash_equals + TTL)
        if (!TwoFAService::verifyOTP($userId, $userType, $code)) {
            errorResponse('Code OTP invalide ou expiré.', 401);
            return;
        }

        // Récupérer les infos utilisateur pour le payload JWT
        $modelClass = match ($userType) {
            'admin'    => \App\Models\Admin::class,
            'employee' => \App\Models\Employee::class,
            'client'   => \App\Models\Client::class,
        };

        // Générer le JWT
        $token = JWTService::generate([
            'user_id'   => $userId,
            'user_type' => $userType,
            'role'      => $userType,
        ]);

        // Créer la session en base
        $ip         = $_SERVER['REMOTE_ADDR'] ?? null;
        $sessionToken = Session::create($userId, $userType, $ip);

        // Mettre à jour last_login si admin
        if ($userType === 'admin') {
            \App\Models\Admin::updateLastLogin($userId);
        }

        // Réinitialiser le rate limiter après succès
        RateLimitMiddleware::reset();

        jsonResponse([
            'message'       => 'Authentification réussie.',
            'token'         => $token,
            'session_token' => $sessionToken,
            'user_type'     => $userType,
            'user_id'       => $userId,
        ]);
    }

    /**
     * POST /api/logout — destruction de la session
     */
    public function logout(): void
    {
        $body         = json_decode(file_get_contents('php://input'), true);
        $sessionToken = $body['session_token'] ?? '';

        if (!empty($sessionToken)) {
            Session::destroy($sessionToken);
        }

        session_destroy();

        jsonResponse(['message' => 'Déconnexion réussie.']);
    }

    /**
     * POST /api/refresh — renouvellement du JWT
     */
    public function refresh(): void
    {
        $headers     = getallheaders();
        $authHeader  = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            errorResponse('Token manquant.', 401);
            return;
        }

        $token    = substr($authHeader, 7);
        $newToken = \App\Services\JWTService::refresh($token);

        if ($newToken === false) {
            errorResponse('Token invalide ou expiré.', 401);
            return;
        }

        jsonResponse(['token' => $newToken]);
    }
}