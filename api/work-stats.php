<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/response.php';

if (!is_logged_in()) {
    json_error(html_entity_decode('&#35531;&#20808;&#30331;&#20837;', ENT_QUOTES, 'UTF-8'), 401);
}

$year = (int) ($_GET['year'] ?? date('Y'));
$month = isset($_GET['month']) ? (int) $_GET['month'] : null;

if ($year < 2000 || $year > 2100) {
    json_error(html_entity_decode('&#24180;&#20221;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
}

if ($month !== null && ($month < 1 || $month > 12)) {
    json_error(html_entity_decode('&#26376;&#20221;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
}

$userId = current_user_id();

function stats_for_range(int $userId, string $start, string $end): array
{
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
        'summary' => $summary,
        'logs' => $logs,
    ];
}

if ($month !== null) {
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTime($start))->modify('first day of next month')->format('Y-m-d');
    $stats = stats_for_range($userId, $start, $end);

    json_success([
        'year' => $year,
        'month' => $month,
        'summary' => $stats['summary'],
        'logs' => $stats['logs'],
    ]);
}

$yearStats = [];
for ($targetMonth = 1; $targetMonth <= 12; $targetMonth++) {
    $start = sprintf('%04d-%02d-01', $year, $targetMonth);
    $end = (new DateTime($start))->modify('first day of next month')->format('Y-m-d');
    $stats = stats_for_range($userId, $start, $end);
    $yearStats[] = [
        'month' => $targetMonth,
        'summary' => $stats['summary'],
    ];
}

json_success([
    'year' => $year,
    'months' => $yearStats,
]);
