<?php
require_once __DIR__ . '/../config/db.php';

$stmt = $conn->query("
    select data_type
    from information_schema.columns
    where table_schema = 'public'
      and table_name = 'order_items'
      and column_name = 'menu_id'
");

$type = $stmt->fetchColumn();

if ($type === false) {
    fwrite(STDERR, "order_items.menu_id is missing\n");
    exit(1);
}

echo "order_items.menu_id -> {$type}\n";
