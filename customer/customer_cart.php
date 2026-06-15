<?php
// customer/customer_cart.php
require_once '../config/session.php';

// AUTOMATIC LOGIN BYPASS FOR LOCAL XAMPP TESTING
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Please log in before updating your cart."]);
        exit;
    }

    header('Location: ../auth/login.php');
    exit;
}

// Handle AJAX POST to change item quantities or remove them
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $item_id = $_POST['item_id'];

    if (isset($_SESSION['cart'][$item_id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$item_id]['qty'] += 1;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$item_id]['qty'] -= 1;
            if ($_SESSION['cart'][$item_id]['qty'] <= 0) {
                unset($_SESSION['cart'][$item_id]);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    
    // Return updated cart structure back as a JSON string
    $updated_cart = [];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $details) {
            $updated_cart[] = [
                "id" => $id,
                "name" => $details['name'],
                "price" => $details['price'],
                "qty" => $details['qty']
            ];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($updated_cart);
    exit;
}

// Format session cart data for the initial JavaScript page render
$formatted_cart = [];
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $details) {
        $formatted_cart[] = [
            "id" => $id,
            "name" => $details['name'],
            "price" => $details['price'],
            "qty" => $details['qty']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Cafe - Shopping Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-cream: #E5DDD3;      
            --text-coffee: #5A4033;    
            --border-dark: #333333;    
            --white: #FFFFFF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        body {
            background-color: var(--bg-cream);
            color: var(--text-coffee);
        }

        .app-container {
            max-width: 1000px;
            margin: 30px auto;
            background-color: var(--white);
            border: 2px solid var(--border-dark);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .page-title-block {
            padding: 25px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.5);
            border-bottom: 2px solid var(--border-dark);
        }

        .page-title-block h1 {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
        }

        .left-column {
            border-right: 2px solid var(--border-dark);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: var(--white);
        }

        .right-column {
            padding: 25px;
            background-color: #FAF8F5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 2px solid var(--border-dark);
        }

        .item-image-placeholder {
            width: 70px;
            height: 70px;
            border: 2px solid var(--border-dark);
            background-color: #FFF;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-image-placeholder i {
            font-size: 22px;
            color: #C1B5A9;
        }

        .item-details {
            flex: 1;
            padding-left: 20px;
        }

        .item-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .item-price {
            font-size: 14px;
            color: #8C766B;
            font-weight: 600;
        }

        .qty-counter {
            display: flex;
            align-items: center;
            border: 2px solid var(--border-dark);
            border-radius: 4px;
            background: var(--white);
        }

        .qty-btn {
            background: none;
            border: none;
            padding: 6px 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            color: var(--text-coffee);
        }

        .qty-display {
            padding: 0 10px;
            font-size: 13px;
            font-weight: bold;
            min-width: 70px;
            text-align: center;
        }

        .left-subtotal-bar {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            font-size: 16px;
            font-weight: bold;
        }

        .method-tabs {
            display: flex;
            border: 2px solid var(--border-dark);
            border-radius: 4px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            background: var(--white);
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            color: #888;
        }

        .tab-btn.active {
            background-color: #EAE3DA;
            color: var(--text-coffee);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .section-header h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #7A6356;
        }

        .edit-btn {
            padding: 2px 10px;
            border: 2px solid var(--border-dark);
            background: var(--white);
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
        }

        .address-display {
            border: 2px solid var(--border-dark);
            padding: 12px;
            background: var(--white);
            min-height: 60px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .order-total-section h3 {
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #7A6356;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .summary-divider {
            border: 0;
            border-top: 2px solid var(--border-dark);
            margin: 10px 0;
        }

        .total-row {
            font-weight: bold;
            font-size: 16px;
        }

        .order-submit-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--white);
            border: 2px solid var(--border-dark);
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            color: var(--text-coffee);
            text-transform: uppercase;
        }

        .order-submit-btn:hover { background-color: var(--bg-cream); }

        @media (max-width: 768px) {
            .main-content { grid-template-columns: 1fr; }
            .left-column { border-right: none; border-bottom: 2px solid var(--border-dark); }
        }
    </style>
</head>
<body>

    <?php include '../navbar.php'; ?>

    <div class="app-container" style="margin-top: 40px;">
        <div class="page-title-block">
            <h1>Shopping Cart</h1>
        </div>

        <div class="main-content">
            <div class="left-column">
                <div id="cart-items-list"></div>
                <div class="left-subtotal-bar">
                    <span>Subtotal</span>
                    <span id="text-left-subtotal">NT$ 0</span>
                </div>
            </div>

            <div class="right-column">
                <div>
                    <div class="method-tabs">
                        <button id="tab-delivery" class="tab-btn active" onclick="switchMethod('delivery')">Delivery</button>
                        <button id="tab-pickup" class="tab-btn" onclick="switchMethod('pickup')">Pickup</button>
                    </div>

                    <div class="delivery-details-section">
                        <div class="section-header">
                            <h3>Delivery Details</h3>
                            <button class="edit-btn" onclick="triggerEditAddress()">Edit</button>
                        </div>
                        <div class="address-display" id="address-text">Address</div>
                    </div>

                    <div class="order-total-section">
                        <h3>Order Total</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="summary-subtotal">NT$ 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span id="summary-delivery">NT$ 0</span>
                        </div>
                        <hr class="summary-divider">
                        <div class="summary-row total-row">
                            <span>Total</span>
                            <span id="summary-total">NT$ 0</span>
                        </div>
                    </div>
                </div>

                <button class="order-submit-btn" onclick="submitOrder()">Order Now</button>
            </div>
        </div>
    </div>

    <script>
        let cartData = <?php echo json_encode($formatted_cart); ?>;
        let currentMethod = 'delivery';
        let baseDeliveryFee = 60; 
        let userAddress = "No. 1, Zhongxiao E. Rd., Taipei City"; 

        let calcSubtotal = 0;
        let calcDeliveryFee = 0;
        let calcTotal = 0;

        function renderCartPage() {
            const listContainer = document.getElementById('cart-items-list');
            listContainer.innerHTML = '';

            if (cartData.length === 0) {
                listContainer.innerHTML = '<div style="padding: 50px 20px; text-align: center; color: #a89285;">Your shopping cart is empty.</div>';
                updateCalculations();
                return;
            }

            cartData.forEach(item => {
                listContainer.innerHTML += `
                    <div class="cart-item">
                        <div class="item-image-placeholder"><i class="fa-solid fa-mug-hot"></i></div>
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">NT$ ${item.price}</div>
                        </div>
                        <div class="qty-counter">
                            <button class="qty-btn" onclick="updateQtyBackend('${item.id}', 'decrease')">-</button>
                            <span class="qty-display">QTY - ${item.qty}</span>
                            <button class="qty-btn" onclick="updateQtyBackend('${item.id}', 'increase')">+</button>
                        </div>
                    </div>
                `;
            });

            updateCalculations();
        }

        function updateQtyBackend(id, actionType) {
            let formData = new FormData();
            formData.append('item_id', id);
            formData.append('action', actionType);

            fetch('customer_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(updatedCart => {
                cartData = updatedCart;
                renderCartPage();
            })
            .catch(error => console.error('Error:', error));
        }

        function updateCalculations() {
            calcSubtotal = 0;
            cartData.forEach(item => {
                calcSubtotal += (item.price * item.qty);
            });

            calcDeliveryFee = (currentMethod === 'pickup' || calcSubtotal === 0) ? 0 : baseDeliveryFee;
            calcTotal = calcSubtotal + calcDeliveryFee;

            document.getElementById('text-left-subtotal').innerText = `NT$ ${calcSubtotal}`;
            document.getElementById('summary-subtotal').innerText = `NT$ ${calcSubtotal}`;
            document.getElementById('summary-delivery').innerText = `NT$ ${calcDeliveryFee}`;
            document.getElementById('summary-total').innerText = `NT$ ${calcTotal}`;
        }

        function switchMethod(method) {
            currentMethod = method;
            document.getElementById('tab-delivery').classList.remove('active');
            document.getElementById('tab-pickup').classList.remove('active');

            const addressDisplay = document.getElementById('address-text');

            if (method === 'delivery') {
                document.getElementById('tab-delivery').classList.add('active');
                addressDisplay.innerHTML = `Address:<br>${userAddress}`;
            } else {
                document.getElementById('tab-pickup').classList.add('active');
                addressDisplay.innerHTML = `<em>Pickup directly at Sweet Bean Cafe counter</em>`;
            }
            updateCalculations();
        }

        function triggerEditAddress() {
            if (currentMethod === 'pickup') {
                alert("Pickup mode is active. No delivery address needed.");
                return;
            }
            const newAddress = prompt("Enter New Delivery Address:", userAddress);
            if (newAddress !== null && newAddress.trim() !== "") {
                userAddress = newAddress;
                document.getElementById('address-text').innerHTML = `Address:<br>${userAddress}`;
            }
        }

        function submitOrder() {
            if (cartData.length === 0) {
                alert("Shopping cart is empty.");
                return;
            }

            let orderPayload = {
                method: currentMethod,
                address: currentMethod === 'delivery' ? userAddress : 'Counter Pickup',
                subtotal: calcSubtotal,
                delivery_fee: calcDeliveryFee,
                total_price: calcTotal
            };

            fetch('place_order_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderPayload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    window.location.href = 'order_tracking.php?order_id=' + data.order_id;
                } else {
                    alert("Failed to process order: " + data.message);
                }
            })
            .catch(err => console.error('Error:', err));
        }

        document.addEventListener("DOMContentLoaded", function() {
            switchMethod('delivery'); 
            renderCartPage();
        });
    </script>
</body>
</html>
