<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/response.php';

if (!is_admin()) {
    json_error('Forbidden', 403);
}

$method = request_method();
$action = $_GET['action'] ?? '';
$adminUserId = current_user_id();

function admin_log_action(int $adminUserId, ?int $targetUserId, string $action, ?string $description = null): void
{
    try {
        db()->prepare(
            'INSERT INTO admin_action_logs (admin_user_id, target_user_id, action, description)
             VALUES (?, ?, ?, ?)'
        )->execute([$adminUserId, $targetUserId, $action, $description]);
    } catch (Throwable) {
        // Admin action should not fail only because logging failed.
    }
}

function validate_admin_user_payload(array $payload, bool $creating): array
{
    $username = trim((string) ($payload['username'] ?? ''));
    $realName = trim((string) ($payload['real_name'] ?? ''));
    $email = trim((string) ($payload['email'] ?? ''));
    $role = (string) ($payload['role'] ?? 'user');
    $password = (string) ($payload['password'] ?? '');

    if ($username === '' || $realName === '' || $email === '') {
        json_error(html_entity_decode('&#35531;&#22635;&#23531;&#24517;&#22635;&#27396;&#20301;', ENT_QUOTES, 'UTF-8'));
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        json_error(html_entity_decode('&#24115;&#34399;&#35531;&#20351;&#29992; 3-50 &#20491;&#33521;&#25991;&#12289;&#25976;&#23383;&#25110;&#24213;&#32218;', ENT_QUOTES, 'UTF-8'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Email format error.');
    }

    if (!in_array($role, ['admin', 'user'], true)) {
        json_error('Role format error.');
    }

    if ($creating && strlen($password) < 4) {
        json_error(html_entity_decode('&#21021;&#22987;&#23494;&#30908;&#33267;&#23569;&#38656; 4 &#30908;', ENT_QUOTES, 'UTF-8'));
    }

    return compact('username', 'realName', 'email', 'role', 'password');
}

if ($method === 'GET') {
    $status = (string) ($_GET['status'] ?? '');
    $keyword = trim((string) ($_GET['q'] ?? ''));
    $where = [];
    $params = [];

    if (in_array($status, ['active', 'disabled'], true)) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    if ($keyword !== '') {
        $where[] = '(username LIKE ? OR real_name LIKE ? OR email LIKE ?)';
        $like = '%' . $keyword . '%';
        array_push($params, $like, $like, $like);
    }

    $sql = 'SELECT id, username, real_name, email, role, status, last_login_at, created_at FROM users';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    json_success(['users' => $stmt->fetchAll()]);
}

$payload = input_json();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
if (!verify_csrf($token)) {
    json_error('CSRF verification failed.', 419);
}

if ($method === 'POST') {
    $data = validate_admin_user_payload($payload, true);

    $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$data['username'], $data['email']]);
    if ($stmt->fetch()) {
        json_error(html_entity_decode('&#24115;&#34399;&#25110; Email &#24050;&#34987;&#20351;&#29992;', ENT_QUOTES, 'UTF-8'), 409);
    }

    db()->prepare(
        'INSERT INTO users (username, real_name, email, password_hash, role, status, email_verified_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    )->execute([
        $data['username'],
        $data['realName'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['role'],
        'active',
    ]);

    $newUserId = (int) db()->lastInsertId();
    admin_log_action($adminUserId, $newUserId, 'create_user', $data['username']);
    json_success(['id' => $newUserId], html_entity_decode('&#24050;&#26032;&#22686;&#20351;&#29992;&#32773;', ENT_QUOTES, 'UTF-8'));
}

if ($method === 'PUT') {
    $id = (int) ($payload['id'] ?? 0);
    if ($id <= 0) {
        json_error('Missing user id.');
    }

    $data = validate_admin_user_payload($payload, false);
    $stmt = db()->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
    $stmt->execute([$data['username'], $data['email'], $id]);
    if ($stmt->fetch()) {
        json_error(html_entity_decode('&#24115;&#34399;&#25110; Email &#24050;&#34987;&#20351;&#29992;', ENT_QUOTES, 'UTF-8'), 409);
    }

    db()->prepare(
        'UPDATE users SET username = ?, real_name = ?, email = ?, role = ? WHERE id = ?'
    )->execute([$data['username'], $data['realName'], $data['email'], $data['role'], $id]);

    admin_log_action($adminUserId, $id, 'update_user', $data['username']);
    json_success([], html_entity_decode('&#24050;&#20786;&#23384;&#20351;&#29992;&#32773;', ENT_QUOTES, 'UTF-8'));
}

if ($method === 'PATCH' && $action === 'status') {
    $id = (int) ($payload['id'] ?? 0);
    $status = (string) ($payload['status'] ?? '');
    if ($id <= 0 || !in_array($status, ['active', 'disabled'], true)) {
        json_error('Status format error.');
    }

    if ($id === $adminUserId && $status === 'disabled') {
        json_error(html_entity_decode('&#19981;&#33021;&#20572;&#29992;&#33258;&#24049;&#30340;&#24115;&#34399;', ENT_QUOTES, 'UTF-8'));
    }

    db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
    admin_log_action($adminUserId, $id, $status === 'active' ? 'enable_user' : 'disable_user');
    json_success([], html_entity_decode('&#29376;&#24907;&#24050;&#26356;&#26032;', ENT_QUOTES, 'UTF-8'));
}

if ($method === 'PATCH' && $action === 'reset_password') {
    $id = (int) ($payload['id'] ?? 0);
    $password = (string) ($payload['password'] ?? '');
    if ($id <= 0 || strlen($password) < 4) {
        json_error(html_entity_decode('&#26032;&#23494;&#30908;&#33267;&#23569;&#38656; 4 &#30908;', ENT_QUOTES, 'UTF-8'));
    }

    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([
        password_hash($password, PASSWORD_DEFAULT),
        $id,
    ]);

    admin_log_action($adminUserId, $id, 'reset_password');
    json_success([], html_entity_decode('&#23494;&#30908;&#24050;&#37325;&#35373;', ENT_QUOTES, 'UTF-8'));
}

json_error('Method not allowed', 405);
