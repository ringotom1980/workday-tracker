<?php

declare(strict_types=1);

function json_response(bool $success, mixed $data = null, ?string $message = null, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}

function json_success(mixed $data = [], ?string $message = null): never
{
    json_response(true, $data, $message);
}

function json_error(string $message, int $statusCode = 400, mixed $data = null): never
{
    json_response(false, $data, $message, $statusCode);
}
