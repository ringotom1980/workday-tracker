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
$action = $_GET['action'] ?? '';
$method = request_method();

function valid_year_month(int $year, int $month): bool
{
    return $year >= 2000 && $year <= 2100 && $month >= 1 && $month <= 12;
}

function month_work_total(int $userId, int $year, int $month): float
{
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = (new DateTime($start))->modify('first day of next month')->format('Y-m-d');
    $stmt = db()->prepare(
        'SELECT COALESCE(SUM(work_value), 0) AS total
         FROM work_logs
         WHERE user_id = ? AND work_date >= ? AND work_date < ?'
    );
    $stmt->execute([$userId, $start, $end]);
    return (float) ($stmt->fetch()['total'] ?? 0);
}

function year_work_total(int $userId, int $year): float
{
    return work_total_for_range($userId, sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1));
}

function work_total_for_range(int $userId, string $start, string $end): float
{
    $stmt = db()->prepare(
        'SELECT COALESCE(SUM(work_value), 0) AS total
         FROM work_logs
         WHERE user_id = ? AND work_date >= ? AND work_date < ?'
    );
    $stmt->execute([$userId, $start, $end]);
    return (float) ($stmt->fetch()['total'] ?? 0);
}

function salary_settings(int $userId, int $year, int $month): array
{
    $stmt = db()->prepare(
        'SELECT daily_salary
         FROM salary_settings
         WHERE user_id = ? AND year = ? AND month = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $year, $month]);
    $settings = $stmt->fetch();

    return [
        'daily_salary' => (float) ($settings['daily_salary'] ?? 0),
    ];
}

function bonus_settings(int $userId, int $year): array
{
    $stmt = db()->prepare(
        'SELECT first_half_bonus_base, second_half_bonus_base
         FROM salary_bonus_settings
         WHERE user_id = ? AND year = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $year]);
    $settings = $stmt->fetch();

    return [
        'first_half_bonus_base' => (float) ($settings['first_half_bonus_base'] ?? 0),
        'second_half_bonus_base' => (float) ($settings['second_half_bonus_base'] ?? 0),
    ];
}

function salary_payload(int $userId, int $year, int $month): array
{
    $settings = salary_settings($userId, $year, $month);
    $bonusSettings = bonus_settings($userId, $year);
    $monthlyWorkDays = month_work_total($userId, $year, $month);
    $yearlyWorkDays = year_work_total($userId, $year);
    $firstHalfWorkDays = work_total_for_range($userId, sprintf('%04d-01-01', $year), sprintf('%04d-07-01', $year));
    $secondHalfWorkDays = work_total_for_range($userId, sprintf('%04d-07-01', $year), sprintf('%04d-01-01', $year + 1));

    return [
        'year' => $year,
        'month' => $month,
        'settings' => array_merge($settings, $bonusSettings),
        'monthly_work_days' => $monthlyWorkDays,
        'yearly_work_days' => $yearlyWorkDays,
        'first_half_work_days' => $firstHalfWorkDays,
        'second_half_work_days' => $secondHalfWorkDays,
        'monthly_salary' => $monthlyWorkDays * $settings['daily_salary'],
        'first_half_bonus' => $firstHalfWorkDays * $bonusSettings['first_half_bonus_base'],
        'second_half_bonus' => $secondHalfWorkDays * $bonusSettings['second_half_bonus_base'],
    ];
}

if ($action === 'settings' && $method === 'GET') {
    $year = (int) ($_GET['year'] ?? date('Y'));
    $month = (int) ($_GET['month'] ?? date('n'));
    if (!valid_year_month($year, $month)) {
        json_error(html_entity_decode('&#24180;&#26376;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
    }

    json_success(salary_payload($userId, $year, $month));
}

if ($action === 'settings' && $method === 'PUT') {
    $payload = input_json();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $payload['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        json_error('CSRF verification failed.', 419);
    }

    $year = (int) ($payload['year'] ?? 0);
    $month = (int) ($payload['month'] ?? 0);
    if (!valid_year_month($year, $month)) {
        json_error(html_entity_decode('&#24180;&#26376;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
    }

    $dailySalary = max(0, (float) ($payload['daily_salary'] ?? 0));
    $firstHalfBonusBase = max(0, (float) ($payload['first_half_bonus_base'] ?? 0));
    $secondHalfBonusBase = max(0, (float) ($payload['second_half_bonus_base'] ?? 0));

    $stmt = db()->prepare(
        'INSERT INTO salary_settings (user_id, year, month, daily_salary, bonus_base)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           daily_salary = VALUES(daily_salary)'
    );
    $stmt->execute([$userId, $year, $month, $dailySalary, 0]);

    $bonusStmt = db()->prepare(
        'INSERT INTO salary_bonus_settings (user_id, year, first_half_bonus_base, second_half_bonus_base)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           first_half_bonus_base = VALUES(first_half_bonus_base),
           second_half_bonus_base = VALUES(second_half_bonus_base)'
    );
    $bonusStmt->execute([$userId, $year, $firstHalfBonusBase, $secondHalfBonusBase]);

    json_success(salary_payload($userId, $year, $month), html_entity_decode('&#24050;&#20786;&#23384;', ENT_QUOTES, 'UTF-8'));
}

if ($action === 'monthly_estimate' && $method === 'GET') {
    $year = (int) ($_GET['year'] ?? date('Y'));
    $month = (int) ($_GET['month'] ?? date('n'));
    if (!valid_year_month($year, $month)) {
        json_error(html_entity_decode('&#24180;&#26376;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
    }

    $payload = salary_payload($userId, $year, $month);
    json_success([
        'monthly_work_days' => $payload['monthly_work_days'],
        'monthly_salary' => $payload['monthly_salary'],
    ]);
}

if ($action === 'yearly_bonus' && $method === 'GET') {
    $year = (int) ($_GET['year'] ?? date('Y'));
    if ($year < 2000 || $year > 2100) {
        json_error(html_entity_decode('&#24180;&#20221;&#26684;&#24335;&#37679;&#35492;', ENT_QUOTES, 'UTF-8'));
    }

    $month = (int) ($_GET['month'] ?? date('n'));
    $payload = salary_payload($userId, $year, max(1, min(12, $month)));
    json_success([
        'first_half_work_days' => $payload['first_half_work_days'],
        'second_half_work_days' => $payload['second_half_work_days'],
        'first_half_bonus' => $payload['first_half_bonus'],
        'second_half_bonus' => $payload['second_half_bonus'],
    ]);
}

json_error('Unknown action.', 404);
