<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/response.php';

$action = $_GET['action'] ?? '';

function login_log(?int $userId, ?string $email, bool $success, string $message): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO login_logs (user_id, email, ip_address, user_agent, success, message)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $email,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $success ? 1 : 0,
            $message,
        ]);
    } catch (Throwable) {
        // Login should not fail only because logging failed.
    }
}

if ($action === 'me') {
    json_success(['user' => current_user()]);
}

if ($action === 'logout') {
    if (request_method() !== 'POST') {
        json_error('Method not allowed', 405);
    }

    $payload = input_json();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        json_error('CSRF 驗證失敗', 419);
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    json_success([], '已登出');
}

if ($action !== 'login') {
    json_error('Unknown action', 404);
}

if (request_method() !== 'POST') {
    json_error('Method not allowed', 405);
}

$payload = input_json();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;

if (!verify_csrf($token)) {
    json_error('CSRF 驗證失敗', 419);
}

$identifier = trim((string) ($payload['identifier'] ?? ''));
$password = (string) ($payload['password'] ?? '');

if ($identifier === '' || $password === '') {
    json_error('請輸入帳號與密碼');
}

$stmt = db()->prepare(
    'SELECT id, username, real_name, email, password_hash, role, status
     FROM users
     WHERE username = ? OR email = ?
     LIMIT 1'
);
$stmt->execute([$identifier, $identifier]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    login_log(isset($user['id']) ? (int) $user['id'] : null, $user['email'] ?? $identifier, false, '帳號或密碼錯誤');
    json_error('帳號或密碼錯誤', 401);
}

if ($user['status'] !== 'active') {
    login_log((int) $user['id'], $user['email'], false, '帳號已停用');
    json_error('帳號已停用', 403);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];

db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([(int) $user['id']]);
login_log((int) $user['id'], $user['email'], true, '登入成功');

json_success([
    'redirect' => '/work-log.php',
    'user' => [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'real_name' => $user['real_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ],
], '登入成功');
