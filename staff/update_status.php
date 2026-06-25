<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_POST['update_status'])) {
    header("Location: dashboard.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

mysqli_query($conn, "
    UPDATE orders
    SET status='$status'
    WHERE order_id='$order_id'
");

header("Location: order_detail.php?id=" . $order_id);
exit();