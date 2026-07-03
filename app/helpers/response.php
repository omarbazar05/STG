<?php

function jsonResponse(array $data, int $statusCode = 200): void
{
    if (php_sapi_name() !== 'cli') {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
        }
        echo json_encode($data);
        exit;
    }
    // En CLI (tests) : on affiche sans exit
    echo json_encode($data) . "\n";
}

function redirectTo(string $path): void
{
    if (php_sapi_name() !== 'cli' && !headers_sent()) {
        header("Location: {$path}");
        exit;
    }
}

function errorResponse(string $message, int $statusCode = 400): void
{
    jsonResponse(['error' => $message], $statusCode);
}