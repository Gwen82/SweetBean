<?php
require __DIR__ . '/../config/db.php';

echo "Connection: OK\n";
echo "Host: db.wkmrjoqgjjjtulhryzwp.supabase.co\n\n";

$tables = $conn->query(
    "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name"
)->fetchAll(PDO::FETCH_COLUMN);

echo "Tables (" . count($tables) . "):\n";
foreach ($tables as $table) {
    $count = $conn->query('SELECT COUNT(*) FROM public.' . $table)->fetchColumn();
    echo "  - {$table}: {$count} rows\n";
}

echo "\nExtensions:\n";
foreach (['pdo_pgsql', 'pgsql'] as $ext) {
    echo "  - {$ext}: " . (extension_loaded($ext) ? 'loaded' : 'MISSING') . "\n";
}
