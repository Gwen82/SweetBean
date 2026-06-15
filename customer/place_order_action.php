<?php
// place_order_action.php
require_once '../config/db.php'; // Targets your Supabase PostgreSQL connection file
require_once '../config/session.php'; // Targets session configurations

header('Content-Type: application/json');

// Validate user authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid or expired session parameters."]);
    exit;
}

// Validate cart data
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(["success" => false, "message" => "Your cart is empty."]);
    exit;
}

$inputData = json_decode(file_get_contents('php://input'), true);
if (!is_array($inputData)) {
    echo json_encode(["success" => false, "message" => "Invalid payload formats incoming."]);
    exit;
}

$requiredFields = ['method', 'address', 'subtotal', 'delivery_fee', 'total_price'];
foreach ($requiredFields as $field) {
    if (!array_key_exists($field, $inputData)) {
        echo json_encode(["success" => false, "message" => "Missing required order field: {$field}."]);
        exit;
    }
}

$user_id      = $_SESSION['user_id']; 
$method       = trim((string) $inputData['method']);
$address      = trim((string) $inputData['address']);
$subtotal     = (float) $inputData['subtotal'];
$delivery_fee = (float) $inputData['delivery_fee'];
$total_price  = (float) $inputData['total_price'];

if (!in_array($method, ['delivery', 'pickup'], true)) {
    echo json_encode(["success" => false, "message" => "Invalid fulfillment method."]);
    exit;
}

try {
    // Start PDO PostgreSQL transaction sequence
    $conn->beginTransaction();

    // 1. Insert structured transaction into parent 'orders' database matrix
    $stmtOrder = $conn->prepare("
        INSERT INTO orders (user_id, method, address, subtotal, delivery_fee, total_price, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        RETURNING id
    ");
    $stmtOrder->execute([$user_id, $method, $address, $subtotal, $delivery_fee, $total_price]);
    
    // Retrieve freshly generated primary key value
    $order_id = $stmtOrder->fetchColumn();

    // 2. Loop through session items and seed child relational matrix 'order_items'
    $stmtItem = $conn->prepare("
        INSERT INTO order_items (order_id, menu_id, price, qty) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($_SESSION['cart'] as $menu_id => $details) {
        $stmtItem->execute([
            $order_id,
            $menu_id,
            (float) $details['price'],
            (int) $details['qty']
        ]);
    }

    // Safely commit relational transactions
    $conn->commit();

    // Reset shopping cart array parameters
    unset($_SESSION['cart']);

    echo json_encode([
        "success" => true,
        "message" => "Your order has been successfully sent to Sweet Bean Cafe!",
        "order_id" => $order_id
    ]);

} catch (Exception $e) {
    // Rollback entries upon failure
    $conn->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Database failure sequence encountered: " . $e->getMessage()
    ]);
}
?>
