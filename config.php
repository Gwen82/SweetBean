<?php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostName = explode(':', $host)[0];
$projectFolder = basename(__DIR__);
$projectPath = in_array($hostName, ['localhost', '127.0.0.1'], true) ? '/' . rawurlencode($projectFolder) . '/' : '/';

define('BASE_URL', $scheme . '://' . $host . $projectPath);
?>
