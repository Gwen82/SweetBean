<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_POST['place_order'])) {
    header("Location: checkout.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: customer_cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_type = $_POST['order_type'];
$delivery_address = $_POST['delivery_address'] ?? '';
$payment_method = $_POST['payment_method'];

$total = 0;

foreach($cart as $item) {
    $total += $item['price'] * $item['qty'];
}

$order_type = mysqli_real_escape_string($conn, $order_type);
$delivery_address = mysqli_real_escape_string($conn, $delivery_address);

$delivery_fee = isset($_POST['delivery_fee']) ? (int)$_POST['delivery_fee'] : 0;
$total += $delivery_fee;

mysqli_query($conn, "
    INSERT INTO orders
    (user_id, order_type, delivery_address, total_amount, status)
    VALUES
    ('$user_id', '$order_type', '$delivery_address', '$total', 'Pending')
");

$order_id = mysqli_insert_id($conn);

foreach($cart as $item) {
    $menu_id = $item['menu_id'];
    $quantity = $item['qty'];
    $price = $item['price'];

    mysqli_query($conn, "
        INSERT INTO order_items
        (order_id, menu_id, quantity, price)
        VALUES
        ('$order_id', '$menu_id', '$quantity', '$price')
    ");
}

unset($_SESSION['cart']);

header("Location: order_success.php?id=" . $order_id);
exit();