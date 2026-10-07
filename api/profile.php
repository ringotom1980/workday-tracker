<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/response.php';

if (!is_logged_in()) {
    json_error(html_entity_decode('&#35531;&#20808;&#30331;&#20837;', ENT_QUOTES, 'UTF-8'), 401);
}

$userId = current_user_id();
$action = $_GET['action'] ?? 'profile';
$method = request_method();

if ($action === 'profile' && $method === 'GET') {
    json_success(['user' => current_user()]);
}

if ($action === 'profile' && $method === 'PUT') {
    $payload = input_json();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        json_error('CSRF verification failed.', 419);
    }

    $realName = trim((string) ($payload['real_name'] ?? ''));
    $username = trim((string) ($payload['username'] ?? ''));

    if ($realName === '' || $username === '') {
        json_error(html_entity_decode('&#35531;&#22635;&#23531;&#30495;&#23526;&#22995;&#21517;&#33287;&#24115;&#34399;', ENT_QUOTES, 'UTF-8'));
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        json_error(html_entity_decode('&#24115;&#34399;&#35531;&#20351;&#29992; 3-50 &#20491;&#33521;&#25991;&#12289;&#25976;&#23383;&#25110;&#24213;&#32218;', ENT_QUOTES, 'UTF-8'));
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
    $stmt->execute([$username, $userId]);
    if ($stmt->fetch()) {
        json_error(html_entity_decode('&#24115;&#34399;&#24050;&#34987;&#20351;&#29992;', ENT_QUOTES, 'UTF-8'), 409);
    }

    db()->prepare('UPDATE users SET real_name = ?, username = ? WHERE id = ?')->execute([$realName, $username, $userId]);
    json_success(['user' => current_user()], html_entity_decode('&#24050;&#20786;&#23384;', ENT_QUOTES, 'UTF-8'));
}

if ($action === 'password' && $method === 'PUT') {
    $payload = input_json();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        json_error('CSRF verification failed.', 419);
    }

    $currentPassword = (string) ($payload['current_password'] ?? '');
    $newPassword = (string) ($payload['new_password'] ?? '');
    $confirmPassword = (string) ($payload['confirm_password'] ?? '');

    if (strlen($newPassword) < 4) {
        json_error(html_entity_decode('&#26032;&#23494;&#30908;&#33267;&#23569;&#38656; 4 &#30908;', ENT_QUOTES, 'UTF-8'));
    }

    if ($newPassword !== $confirmPassword) {
        json_error(html_entity_decode('&#20841;&#27425;&#26032;&#23494;&#30908;&#19981;&#19968;&#33268;', ENT_QUOTES, 'UTF-8'));
    }

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        json_error(html_entity_decode('&#30446;&#21069;&#23494;&#30908;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'), 401);
    }

    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([
        password_hash($newPassword, PASSWORD_DEFAULT),
        $userId,
    ]);

    json_success([], html_entity_decode('&#23494;&#30908;&#24050;&#26356;&#26032;', ENT_QUOTES, 'UTF-8'));
}

json_error('Unknown action.', 404);
