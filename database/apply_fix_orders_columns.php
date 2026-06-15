<?php
require_once __DIR__ . '/../config/db.php';

$sqlPath = __DIR__ . '/fix_orders_columns.sql';
$sql = file_get_contents($sqlPath);

if ($sql === false) {
    fwrite(STDERR, "Could not read {$sqlPath}.\n");
    exit(1);
}

$conn->exec($sql);
echo "orders migration applied\n";
