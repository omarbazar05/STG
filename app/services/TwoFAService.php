<?php

namespace App\Services;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;

class TwoFAService
{
    public static function generateOTP(int $userId, string $userType): string
    {
        $code = (string) random_int(100000, 999999);
        $hash = hash('sha256', $code);
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes

        $pdo = \Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO otp_codes (user_id, user_type, otp_hash, expires_at)
             VALUES (:user_id, :user_type, :otp_hash, :expires_at)"
        );
        $stmt->execute([
            'user_id'    => $userId,
            'user_type'  => $userType,
            'otp_hash'   => $hash,
            'expires_at' => $expiresAt,
        ]);

        return $code;
    }

    public static function sendOTP(string $email, string $code): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->Port       = $_ENV['MAIL_PORT'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = 'tls';

            $mail->setFrom('no-reply@absec.ma', 'ABSec');
            $mail->addAddress($email);
            $mail->Subject = 'Votre code de vérification';
            $mail->isHTML(true);
            $mail->Body = "<p>Votre code de vérification est : <b>{$code}</b></p>
                           <p>Ce code expire dans 5 minutes.</p>";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("Erreur envoi OTP : {$e->getMessage()}");
            return false;
        }
    }

    public static function verifyOTP(int $userId, string $userType, string $code): bool
    {
        $pdo = \Database::getConnection();
        $hash = hash('sha256', $code);

        $stmt = $pdo->prepare(
            "SELECT id, otp_hash FROM otp_codes
             WHERE user_id = :user_id AND user_type = :user_type
             AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId, 'user_type' => $userType]);
        $row = $stmt->fetch();

        if (!$row || !hash_equals($row['otp_hash'], $hash)) {
            return false;
        }

        // Usage unique : on supprime après vérification
        $deleteStmt = $pdo->prepare("DELETE FROM otp_codes WHERE id = :id");
        $deleteStmt->execute(['id' => $row['id']]);

        return true;
    }
}