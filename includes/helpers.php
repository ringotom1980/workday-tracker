<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function input_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function work_value_for_type(string $workType): float
{
    switch ($workType) {
        case 'full_day':
            return 1.0;
        case 'half_day':
            return 0.5;
        case 'night':
            return 1.5;
        default:
            return 0.0;
    }
}

function first_character(string $value): string
{
    if (preg_match('/./u', $value, $matches) === 1) {
        return $matches[0];
    }

    return substr($value, 0, 1);
}
