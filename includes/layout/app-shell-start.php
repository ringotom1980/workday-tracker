<?php
require_once __DIR__ . '/../assets.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../helpers.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#070B1A">
  <title><?= e($pageTitle ?? APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= css('/assets/css/01-tokens.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/02-reset.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/03-typography.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/04-layout.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/05-components.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/06-animations.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/07-utilities.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/08-pages.css') ?>">
</head>
<body>
  <div class="app-shell">
    <?php include __DIR__ . '/header.php'; ?>
    <main class="app-main" id="app-main">
