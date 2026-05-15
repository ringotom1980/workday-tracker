<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/response.php';

if (is_logged_in()) {
    json_error(html_entity_decode('&#24050;&#30331;&#20837;', ENT_QUOTES, 'UTF-8'), 409);
}

if (request_method() !== 'POST') {
    json_error('Method not allowed', 405);
}

$payload = input_json();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
if (!verify_csrf($token)) {
    json_error('CSRF verification failed.', 419);
}

$username = trim((string) ($payload['username'] ?? ''));
$realName = trim((string) ($payload['real_name'] ?? ''));
$email = trim((string) ($payload['email'] ?? ''));
$password = (string) ($payload['password'] ?? '');
$confirmPassword = (string) ($payload['confirm_password'] ?? '');

if ($username === '' || $realName === '' || $email === '' || $password === '') {
    json_error(html_entity_decode('&#35531;&#22635;&#23531;&#24517;&#22635;&#27396;&#20301;', ENT_QUOTES, 'UTF-8'));
}

if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
    json_error(html_entity_decode('&#24115;&#34399;&#35531;&#20351;&#29992; 3-50 &#20491;&#33521;&#25991;&#12289;&#25976;&#23383;&#25110;&#24213;&#32218;', ENT_QUOTES, 'UTF-8'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Email format error.');
}

if (strlen($password) < 4) {
    json_error(html_entity_decode('&#23494;&#30908;&#33267;&#23569;&#38656; 4 &#30908;', ENT_QUOTES, 'UTF-8'));
}

if ($password !== $confirmPassword) {
    json_error(html_entity_decode('&#20841;&#27425;&#23494;&#30908;&#19981;&#19968;&#33268;', ENT_QUOTES, 'UTF-8'));
}

$stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    json_error(html_entity_decode('&#24115;&#34399;&#25110; Email &#24050;&#34987;&#20351;&#29992;', ENT_QUOTES, 'UTF-8'), 409);
}

db()->prepare(
    'INSERT INTO users (username, real_name, email, password_hash, role, status, email_verified_at)
     VALUES (?, ?, ?, ?, ?, ?, NOW())'
)->execute([
    $username,
    $realName,
    $email,
    password_hash($password, PASSWORD_DEFAULT),
    'user',
    'active',
]);

$userId = (int) db()->lastInsertId();
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;

json_success([
    'redirect' => '/work-log.php',
], html_entity_decode('&#35387;&#20874;&#25104;&#21151;', ENT_QUOTES, 'UTF-8'));
