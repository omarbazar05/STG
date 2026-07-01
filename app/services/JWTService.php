<?php

namespace App\Services;

class JWTService
{
    public static function generate(array $payload): string
    {
        $header = self::base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]));

        $payload['exp'] = time() + 28800; // 8h
        $payload['iat'] = time();

        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "{$header}.{$payloadEncoded}",
            (env('JWT_SECRET') ?? getenv('JWT_SECRET')),
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$header}.{$payloadEncoded}.{$signatureEncoded}";
    }

    public static function verify(string $token): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = self::base64UrlEncode(hash_hmac(
            'sha256',
            "{$header}.{$payload}",
            (env('JWT_SECRET') ?? getenv('JWT_SECRET')),
            true
        ));

        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        $decoded = json_decode(self::base64UrlDecode($payload), true);

        if (!$decoded || !isset($decoded['exp']) || $decoded['exp'] < time()) {
            return false;
        }

        return $decoded;
    }

    public static function refresh(string $token): string|false
    {
        $payload = self::verify($token);
        if ($payload === false) {
            return false;
        }

        unset($payload['exp'], $payload['iat']);
        return self::generate($payload);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}