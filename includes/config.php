<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Taipei');

$localConfig = __DIR__ . '/config-local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

defined('APP_NAME') || define('APP_NAME', '紀錄上班天數系統');
defined('APP_ENV') || define('APP_ENV', getenv('APP_ENV') ?: 'production');
defined('APP_DEBUG') || define('APP_DEBUG', APP_ENV !== 'production');

defined('DB_HOST') || define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
defined('DB_NAME') || define('DB_NAME', getenv('DB_NAME') ?: '');
defined('DB_USER') || define('DB_USER', getenv('DB_USER') ?: '');
defined('DB_PASS') || define('DB_PASS', getenv('DB_PASS') ?: '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

defined('GOVERNMENT_CALENDAR_CSV_URL') || define('GOVERNMENT_CALENDAR_CSV_URL', 'https://data.ntpc.gov.tw/api/datasets/308dcd75-6434-45bc-a95f-584da4fed251/csv/file');
