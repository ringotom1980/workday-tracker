<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/response.php';

if (!is_logged_in()) {
    json_error(html_entity_decode('&#35531;&#20808;&#30331;&#20837;', ENT_QUOTES, 'UTF-8'), 401);
}

$year = (int) ($_GET['year'] ?? date('Y'));
$month = (int) ($_GET['month'] ?? date('n'));
$period = (string) ($_GET['period'] ?? 'month');

if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
    json_error(html_entity_decode('&#24180;&#26376;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
}

if (!in_array($period, ['month', 'first_half', 'second_half', 'year'], true)) {
    json_error('Period format error.');
}

if ($period === 'month') {
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTime($start))->modify('first day of next month')->format('Y-m-d');
    $label = sprintf('%04d-%02d', $year, $month);
} elseif ($period === 'first_half') {
    $start = sprintf('%04d-01-01', $year);
    $end = sprintf('%04d-07-01', $year);
    $label = $year . ' H1';
} elseif ($period === 'second_half') {
    $start = sprintf('%04d-07-01', $year);
    $end = sprintf('%04d-01-01', $year + 1);
    $label = $year . ' H2';
} else {
    $start = sprintf('%04d-01-01', $year);
    $end = sprintf('%04d-01-01', $year + 1);
    $label = (string) $year;
}

$stmt = db()->prepare(
    'SELECT
       u.id,
       u.real_name,
       u.username,
       COALESCE(SUM(w.work_value), 0) AS total_days,
       COUNT(w.id) AS record_count
     FROM users u
     LEFT JOIN work_logs w
       ON w.user_id = u.id
      AND w.work_date >= ?
      AND w.work_date < ?
     WHERE u.status = ?
     GROUP BY u.id, u.real_name, u.username
     ORDER BY total_days DESC, record_count DESC, u.real_name ASC'
);
$stmt->execute([$start, $end, 'active']);
$rows = $stmt->fetchAll();

$ranked = [];
$rank = 0;
$previousTotal = null;
$position = 0;

foreach ($rows as $row) {
    $position++;
    $total = (float) $row['total_days'];
    if ($previousTotal === null || $total !== $previousTotal) {
        $rank = $position;
        $previousTotal = $total;
    }

    $ranked[] = [
        'rank' => $rank,
        'id' => (int) $row['id'],
        'real_name' => $row['real_name'],
        'username' => $row['username'],
        'total_days' => $total,
        'record_count' => (int) $row['record_count'],
    ];
}

json_success([
    'year' => $year,
    'month' => $month,
    'period' => $period,
    'label' => $label,
    'items' => $ranked,
]);
