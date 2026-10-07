<?php

require_once __DIR__ . '/includes/auth.php';

header('Location: ' . (is_logged_in() ? '/work-log.php' : '/login.php'));
exit;
