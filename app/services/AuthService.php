<?php

namespace App\Services;

require_once __DIR__ . '/../helpers/security.php';

class AuthService
{
    /**
     * @return array{success: bool, user: ?array, requiresOtp: bool}
     */
    public static function login(string $email, string $password, string $idRaw, string $userType): array
    {
        $modelClass = match ($userType) {
            'admin'    => \App\Models\Admin::class,
            'employee' => \App\Models\Employee::class,
            'client'   => \App\Models\Client::class,
            default    => null,
        };

        if ($modelClass === null) {
            return ['success' => false, 'user' => null, 'requiresOtp' => false];
        }

        $user = $modelClass::findByEmail($email);

        if (!$user) {
            return ['success' => false, 'user' => null, 'requiresOtp' => false];
        }

        $idHashField = match ($userType) {
            'admin'    => 'admin_id_hash',
            'employee' => 'employee_id_hash',
            'client'   => 'client_id_hash',
        };

        $passwordOk = verifyHash($password, $user['password_hash']);
        $idOk       = verifyHash($idRaw, $user[$idHashField]);

        if (!$passwordOk || !$idOk) {
            return ['success' => false, 'user' => null, 'requiresOtp' => false];
        }

        // Identifiants valides → déclenche la 2FA
        $code = TwoFAService::generateOTP($user['id'], $userType);
        TwoFAService::sendOTP($user['email'], $code);

        return ['success' => true, 'user' => $user, 'requiresOtp' => true];
    }

    public static function logout(string $tokenHash): void
    {
        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE token_hash = :token_hash");
        $stmt->execute(['token_hash' => $tokenHash]);
    }

    public static function getUser(string $jwt): array|false
    {
        return JWTService::verify($jwt);
    }
}