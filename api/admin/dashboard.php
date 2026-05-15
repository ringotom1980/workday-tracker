<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

if (!is_admin()) {
    json_error('Forbidden', 403);
}

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

$stmt = db()->prepare(
    'SELECT job_name, status, message, records_processed, started_at, finished_at, created_at
     FROM system_jobs
     WHERE job_name = ? AND status = ?
     ORDER BY created_at DESC
     LIMIT 1'
);
$stmt->execute(['sync-government-calendar', 'success']);
$calendarJob = $stmt->fetch() ?: null;

json_success([
    'users' => $users,
    'calendar_job' => $calendarJob,
]);
