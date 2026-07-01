<?php

/**
 * Hash un mot de passe (ou tout identifiant sensible) avec bcrypt.
 */
function hashPassword(string $plain): string
{
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Vérifie un texte brut contre un hash bcrypt.
 * Toujours utiliser cette fonction — jamais de comparaison ==.
 */
function verifyHash(string $plain, string $hash): bool
{
    return password_verify($plain, $hash);
}

/**
 * Génère un token aléatoire cryptographiquement sûr (CSRF, sessions...).
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}