<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: customer_cart.php");
    exit();
}

$total = 0;

foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}

$delivery_fee = 60;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - Sweet Bean Coffee</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
}

body {
    background: #f4eee7;
    color: #3d2a1f;
    min-height: 100vh;
    padding: 40px 15px;
}

.checkout-wrapper {
    max-width: 950px;
    margin: auto;
    background: #ffffff;
    border-radius: 28px;
    box-shadow: 0 20px 50px rgba(111,78,55,.12);
    overflow: hidden;
}

.checkout-header {
    padding: 35px;
    text-align: center;
    background: #6f4e37;
    color: white;
}

.checkout-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.checkout-body {
    padding: 35px;
}

.section {
    background: #faf7f2;
    padding: 24px;
    border-radius: 22px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 900;
    color: #6f4e37;
    margin-bottom: 18px;
}

.order-type {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.order-type input {
    display: none;
}

.order-type label {
    padding: 18px;
    text-align: center;
    border: 2px solid #e2d6cc;
    border-radius: 18px;
    cursor: pointer;
    font-weight: 900;
    background: white;
    transition: .25s;
}

.order-type input:checked + label {
    background: #6f4e37;
    color: white;
    border-color: #6f4e37;
}

.form-label {
    display: block;
    margin: 16px 0 8px;
    font-weight: 800;
}

textarea,
select {
    width: 100%;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 14px;
    font-size: 14px;
    background: white;
}

.pickup-info {
    line-height: 1.7;
}

.pickup-info strong {
    color: #6f4e37;
}

.map-box {
    margin-top: 16px;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #ddd;
}

.map-box iframe {
    width: 100%;
    height: 260px;
    border: 0;
}

.total-box {
    background: #fff;
    border: 2px solid #6f4e37;
    border-radius: 22px;
    padding: 24px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 14px;
    font-size: 16px;
}

.grand-total {
    display: flex;
    justify-content: space-between;
    border-top: 2px solid #6f4e37;
    padding-top: 18px;
    margin-top: 18px;
    font-size: 25px;
    font-weight: 900;
    color: #3d2a1f;
}

.btn-order {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 30px;
    background: #6f4e37;
    color: white;
    font-weight: 900;
    font-size: 15px;
    cursor: pointer;
    margin-top: 25px;
}

.btn-order:hover {
    background: #563925;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 16px;
    color: #6f4e37;
    text-decoration: none;
    font-weight: 800;
}

.note {
    font-size: 13px;
    color: #8a7568;
    margin-top: 8px;
}

@media(max-width: 700px) {
    .order-type {
        grid-template-columns: 1fr;
    }

    .checkout-body {
        padding: 24px;
    }
}
</style>
</head>

<body>

<div class="checkout-wrapper">

    <div class="checkout-header">
        <h1>Checkout</h1>
        <p>Choose your order method and payment</p>
    </div>

    <form action="process_order.php" method="POST">

        <div class="checkout-body">

            <div class="section">
                <div class="section-title">Order Method</div>

                <div class="order-type">
                    <input type="radio" id="delivery" name="order_type" value="Delivery" checked onchange="toggleOrderType()">
                    <label for="delivery">Delivery</label>

                    <input type="radio" id="pickup" name="order_type" value="Pickup" onchange="toggleOrderType()">
                    <label for="pickup">Pickup</label>
                </div>
            </div>

            <div class="section" id="deliveryBox">
                <div class="section-title">Delivery Address</div>

                <label class="form-label">Your Address</label>
                <textarea 
                    name="delivery_address" 
                    id="deliveryAddress" 
                    rows="4" 
                    placeholder="Enter your full delivery address">
                </textarea>

                <p class="note">Delivery fee will be added automatically.</p>
            </div>

            <div class="section" id="pickupBox" style="display:none;">
                <div class="section-title">Pickup Location</div>

                <div class="pickup-info">
                    <p>Please pick up your order at:</p>
                    <strong>National University of Kaohsiung</strong>
                    <p>No. 700, Kaohsiung University Rd, Nanzih District, Kaohsiung City, Taiwan</p>
                </div>

                <div class="map-box">
                    <iframe
                        loading="lazy"
                        allowfullscreen
                        src="https://www.google.com/maps?q=National%20University%20of%20Kaohsiung&output=embed">
                    </iframe>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Payment Method</div>

                <label class="form-label">Select Payment</label>
                <select name="payment_method" required>
                    <option value="COD">COD / Cash on Delivery</option>
                    <option value="Online Payment">Online Payment</option>
                </select>
            </div>

            <div class="total-box">
                <div class="total-row">
                    <span>Order Total</span>
                    <span>NT$ <?= number_format($total); ?></span>
                </div>

                <div class="total-row" id="deliveryFeeRow">
                    <span>Delivery Fee</span>
                    <span>NT$ <?= number_format($delivery_fee); ?></span>
                </div>

                <div class="grand-total">
                    <span>Total Payment</span>
                    <span id="grandTotal">NT$ <?= number_format($total + $delivery_fee); ?></span>
                </div>
            </div>

            <input type="hidden" name="delivery_fee" id="deliveryFeeInput" value="<?= $delivery_fee; ?>">

            <button type="submit" name="place_order" class="btn-order">
                Place Order
            </button>

            <a href="customer_cart.php" class="back-link">
                Back to Cart
            </a>

        </div>

    </form>

</div>

<script>
const subtotal = <?= $total; ?>;
const deliveryFee = <?= $delivery_fee; ?>;

function toggleOrderType() {
    const isDelivery = document.getElementById('delivery').checked;

    document.getElementById('deliveryBox').style.display = isDelivery ? 'block' : 'none';
    document.getElementById('pickupBox').style.display = isDelivery ? 'none' : 'block';

    document.getElementById('deliveryAddress').required = isDelivery;

    document.getElementById('deliveryFeeRow').style.display = isDelivery ? 'flex' : 'none';
    document.getElementById('deliveryFeeInput').value = isDelivery ? deliveryFee : 0;

    const finalTotal = isDelivery ? subtotal + deliveryFee : subtotal;

    document.getElementById('grandTotal').innerText =
        'NT$ ' + finalTotal.toLocaleString();
}

toggleOrderType();
</script>

</body>
</html>