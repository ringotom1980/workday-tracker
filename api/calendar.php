<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/response.php';

if (!is_logged_in()) {
    json_error(html_entity_decode('&#35531;&#20808;&#30331;&#20837;', ENT_QUOTES, 'UTF-8'), 401);
}

$year = (int) ($_GET['year'] ?? date('Y'));
$month = (int) ($_GET['month'] ?? date('n'));

if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
    json_error(html_entity_decode('&#24180;&#26376;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
}

$stmt = db()->prepare(
    'SELECT calendar_date, is_holiday, is_makeup_workday, title, description, source_name
     FROM government_calendar
     WHERE year = ? AND month = ?
     ORDER BY calendar_date ASC'
);
$stmt->execute([$year, $month]);

json_success([
    'year' => $year,
    'month' => $month,
    'days' => $stmt->fetchAll(),
]);
