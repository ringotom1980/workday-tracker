<?php

declare(strict_types=1);

function asset_version(string $path): int
{
    $documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)), DIRECTORY_SEPARATOR);
    $fullPath = $documentRoot . str_replace('/', DIRECTORY_SEPARATOR, $path);

    if (file_exists($fullPath)) {
        return (int) filemtime($fullPath);
    }

    return time();
}

function css(string $path): string
{
    return $path . '?v=' . asset_version($path);
}

function js(string $path): string
{
    return $path . '?v=' . asset_version($path);
}
