<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user(): ?array
{
    $userId = current_user_id();
    if ($userId === null) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, real_name, email, role, status, email_verified_at, last_login_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function is_logged_in(): bool
{
    $user = current_user();
    return $user !== null && $user['status'] === 'active';
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin' && $user['status'] === 'active';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        http_response_code(403);
        exit('Forbidden');
    }
}
