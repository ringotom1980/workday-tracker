<?php
declare(strict_types=1);
// Artifact is deployed only at DOCROOT/workday-runtime-v1.
$originalConfig = dirname(__DIR__, 2) . '/includes/config.php';
if (!is_file($originalConfig)) {
    throw new RuntimeException('Original root configuration is missing.');
}
require_once $originalConfig;
