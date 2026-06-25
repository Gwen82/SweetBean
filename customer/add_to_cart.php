<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$menu_id = $_POST['menu_id'] ?? '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($menu_id === '' || $quantity < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid menu item'
    ]);
    exit();
}

$menu_id_safe = mysqli_real_escape_string($conn, $menu_id);

$result = mysqli_query($conn, "
    SELECT * FROM menu
    WHERE menu_id = '$menu_id_safe'
    LIMIT 1
");

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Menu item not found'
    ]);
    exit();
}

$item = mysqli_fetch_assoc($result);

$isDrink = (($item['item_type'] ?? 'Food') === 'Drink');

$ice_level = $isDrink ? ($_POST['ice_level'] ?? 'Normal Ice') : 'N/A';
$sugar_level = $isDrink ? ($_POST['sugar_level'] ?? '100%') : 'N/A';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_key = $menu_id . '_' . $ice_level . '_' . $sugar_level;

if (isset($_SESSION['cart'][$cart_key])) {
    $_SESSION['cart'][$cart_key]['qty'] += $quantity;
} else {
    $_SESSION['cart'][$cart_key] = [
        'menu_id' => $item['menu_id'],
        'name' => $item['product_name'],
        'price' => $item['price'],
        'image' => $item['image'] ?? '',
        'ice_level' => $ice_level,
        'sugar_level' => $sugar_level,
        'qty' => $quantity
    ];
}

$cart_count = 0;
$cart_total = 0;

foreach ($_SESSION['cart'] as $cart_item) {
    $cart_count += (int)($cart_item['qty'] ?? 0);
    $cart_total += $cart_item['price'] * $cart_item['qty'];
}

echo json_encode([
    'success' => true,
    'cart_count' => $cart_count,
    'cart_total' => $cart_total
]);
exit();