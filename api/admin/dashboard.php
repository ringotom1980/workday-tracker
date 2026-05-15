<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

if (!is_admin()) {
    json_error('Forbidden', 403);
}

$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$nextMonthStart = (new DateTime($monthStart))->modify('first day of next month')->format('Y-m-d');

function scalar_query(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

$users = [
    'total' => scalar_query('SELECT COUNT(*) FROM users'),
    'active' => scalar_query("SELECT COUNT(*) FROM users WHERE status = 'active'"),
    'disabled' => scalar_query("SELECT COUNT(*) FROM users WHERE status = 'disabled'"),
];

$workLogs = [
    'today' => scalar_query('SELECT COUNT(*) FROM work_logs WHERE work_date = ?', [$today]),
    'month' => scalar_query('SELECT COUNT(*) FROM work_logs WHERE work_date >= ? AND work_date < ?', [$monthStart, $nextMonthStart]),
];

$stmt = db()->prepare(
    'SELECT job_name, status, message, records_processed, started_at, finished_at, created_at
     FROM system_jobs
     WHERE job_name = ?
     ORDER BY created_at DESC
     LIMIT 1'
);
$stmt->execute(['sync-government-calendar']);
$calendarJob = $stmt->fetch() ?: null;

json_success([
    'users' => $users,
    'work_logs' => $workLogs,
    'calendar_job' => $calendarJob,
]);
