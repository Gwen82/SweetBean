<?php
require_once __DIR__ . '/../config/db.php';

$stmt = $conn->query("
    select
        conname,
        confrelid::regclass::text as references_table
    from pg_constraint
    where conrelid = 'public.orders'::regclass
      and contype = 'f'
      and conname = 'orders_user_id_fkey'
");

$row = $stmt->fetch();

if (!$row) {
    fwrite(STDERR, "orders_user_id_fkey is missing\n");
    exit(1);
}

echo $row['conname'] . ' -> ' . $row['references_table'] . "\n";
