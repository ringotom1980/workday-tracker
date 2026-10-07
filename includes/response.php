<?php

declare(strict_types=1);

function json_response(bool $success, $data = null, ?string $message = null, int $statusCode = 200): void
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

function json_success($data = [], ?string $message = null): void
{
    json_response(true, $data, $message);
}

function json_error(string $message, int $statusCode = 400, $data = null): void
{
    json_response(false, $data, $message, $statusCode);
}
