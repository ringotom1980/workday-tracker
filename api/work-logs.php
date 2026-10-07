<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/response.php';

if (!is_logged_in()) {
    json_error('請先登入', 401);
}

$userId = current_user_id();
$method = request_method();

function valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed instanceof DateTime && $parsed->format('Y-m-d') === $date;
}

function fetch_day_log(int $userId, string $date): ?array
{
    $stmt = db()->prepare(
        'SELECT work_date, work_type, work_value, note
         FROM work_logs
         WHERE user_id = ? AND work_date = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $date]);
    $log = $stmt->fetch();

    return $log ?: null;
}

function fetch_month_summary(int $userId, int $year, int $month): array
{
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTime($start))->modify('first day of next month')->format('Y-m-d');

    $stmt = db()->prepare(
        'SELECT work_date, work_type, work_value, note
         FROM work_logs
         WHERE user_id = ? AND work_date >= ? AND work_date < ?
         ORDER BY work_date ASC'
    );
    $stmt->execute([$userId, $start, $end]);
    $logs = $stmt->fetchAll();

    $summary = [
        'total_value' => 0.0,
        'record_count' => count($logs),
        'full_day' => 0,
        'half_day' => 0,
        'night' => 0,
    ];

    foreach ($logs as $log) {
        $summary['total_value'] += (float) $log['work_value'];
        if (isset($summary[$log['work_type']])) {
            $summary[$log['work_type']]++;
        }
    }

    return [
        'year' => $year,
        'month' => $month,
        'summary' => $summary,
        'logs' => $logs,
    ];
}

if ($method === 'GET') {
    if (!empty($_GET['date'])) {
        $date = (string) $_GET['date'];
        if (!valid_date($date)) {
            json_error('日期格式錯誤');
        }

        json_success(['log' => fetch_day_log($userId, $date)]);
    }

    $year = (int) ($_GET['year'] ?? date('Y'));
    $month = (int) ($_GET['month'] ?? date('n'));

    if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
        json_error('年月格式錯誤');
    }

    json_success(fetch_month_summary($userId, $year, $month));
}

if ($method === 'PUT') {
    $payload = input_json();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        json_error('CSRF 驗證失敗', 419);
    }

    $date = (string) ($payload['date'] ?? '');
    $workType = (string) ($payload['work_type'] ?? '');

    if (!valid_date($date)) {
        json_error('日期格式錯誤');
    }

    if (!in_array($workType, ['full_day', 'half_day', 'night'], true)) {
        json_error('班別格式錯誤');
    }

    $workValue = work_value_for_type($workType);
    $note = trim((string) ($payload['note'] ?? ''));

    $stmt = db()->prepare(
        'INSERT INTO work_logs (user_id, work_date, work_type, work_value, note)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           work_type = VALUES(work_type),
           work_value = VALUES(work_value),
           note = VALUES(note)'
    );
    $stmt->execute([$userId, $date, $workType, $workValue, $note !== '' ? $note : null]);

    json_success([
        'log' => fetch_day_log($userId, $date),
    ], '已更新紀錄');
}

if ($method === 'DELETE') {
    $payload = input_json();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        json_error('CSRF 驗證失敗', 419);
    }

    $date = (string) ($_GET['date'] ?? $payload['date'] ?? '');
    if (!valid_date($date)) {
        json_error('日期格式錯誤');
    }

    $stmt = db()->prepare('DELETE FROM work_logs WHERE user_id = ? AND work_date = ?');
    $stmt->execute([$userId, $date]);

    json_success(['log' => null], '已取消紀錄');
}

json_error('Method not allowed', 405);
