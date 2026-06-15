<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/menu_repository.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in before updating your cart.']);
    exit;
}

$action = $_POST['action'] ?? '';
$itemId = trim((string) ($_POST['item_id'] ?? ''));

if (!in_array($action, ['increase', 'decrease', 'remove'], true) || $itemId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid cart request.']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action === 'increase') {
    $menuItem = sweetbean_find_menu_item($itemId, $conn);

    if (!$menuItem) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Menu item was not found.']);
        exit;
    }

    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId]['qty'] += 1;
    } else {
        $_SESSION['cart'][$itemId] = [
            'name' => $menuItem['name'],
            'price' => (float) $menuItem['price'],
            'qty' => 1,
            'category' => $menuItem['category'],
            'icon' => $menuItem['icon'],
        ];
    }
} elseif (isset($_SESSION['cart'][$itemId])) {
    if ($action === 'decrease') {
        $_SESSION['cart'][$itemId]['qty'] -= 1;

        if ($_SESSION['cart'][$itemId]['qty'] <= 0) {
            unset($_SESSION['cart'][$itemId]);
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$itemId]);
    }
}

$cart = [];
foreach ($_SESSION['cart'] as $id => $details) {
    $cart[] = [
        'id' => $id,
        'name' => $details['name'],
        'price' => (float) $details['price'],
        'qty' => (int) $details['qty'],
        'category' => $details['category'] ?? '',
        'icon' => $details['icon'] ?? 'fa-mug-hot',
    ];
}

echo json_encode(['success' => true, 'cart' => $cart]);
