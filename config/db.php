<?php
$dbConfig = [
    'host' => 'db.wkmrjoqgjjjtulhryzwp.supabase.co',
    'port' => '5432',
    'dbname' => 'postgres',
    'user' => 'postgres',
    'password' => '',
];

$localConfigPath = __DIR__ . '/db.local.php';

if (file_exists($localConfigPath)) {
    $localConfig = require $localConfigPath;

    if (is_array($localConfig)) {
        $dbConfig = array_merge($dbConfig, $localConfig);
    }
}

if ($dbConfig['password'] === '') {
    die('Database password is missing. Create config/db.local.php from config/db.local.example.php.');
}

$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['dbname']
);

try {
    $conn = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>