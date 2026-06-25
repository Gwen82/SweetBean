<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = $_GET['id'] ?? '';

$order = mysqli_query($conn, "
    SELECT orders.*, users.name AS customer_name, users.email, users.phone
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    WHERE orders.order_id='$order_id'
    LIMIT 1
");

$order = mysqli_fetch_assoc($order);

if (!$order) {
    header("Location: dashboard.php");
    exit();
}

$items = mysqli_query($conn, "
    SELECT order_items.*, menu.product_name
    FROM order_items
    LEFT JOIN menu ON order_items.menu_id = menu.menu_id
    WHERE order_items.order_id='$order_id'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Detail | Staff</title>
<style>
body {
    margin: 0;
    background: #fff8ef;
    font-family: Arial, sans-serif;
    color: #2a211b;
}

.page {
    padding: 45px 25px;
}

.container {
    max-width: 900px;
    margin: auto;
}

.card {
    background: white;
    border-radius: 24px;
    padding: 30px;
    box-shadow: 0 12px 30px rgba(90,56,37,0.08);
    margin-bottom: 22px;
}

h1, h2 {
    color: #5a3825;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.info-box {
    background: #fff8ef;
    padding: 16px;
    border-radius: 16px;
}

.label {
    color: #806e62;
    font-size: 13px;
    margin-bottom: 5px;
}

.value {
    font-weight: 800;
}

.item-row {
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid #eee;
}

.item-row:last-child {
    border-bottom: none;
}

select {
    width: 100%;
    padding: 14px;
    border-radius: 14px;
    border: 1px solid #ddd;
    margin-top: 8px;
}

.btn {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 16px;
    background: #5a3825;
    color: white;
    font-weight: 900;
    margin-top: 18px;
    cursor: pointer;
}

.back {
    display: inline-block;
    margin-top: 20px;
    color: #5a3825;
    font-weight: 800;
    text-decoration: none;
}

@media(max-width: 700px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="page">
    <div class="container">

        <div class="card">
            <h1>Order #<?= $order['order_id']; ?></h1>

            <div class="info-grid">
                <div class="info-box">
                    <div class="label">Customer</div>
                    <div class="value"><?= htmlspecialchars($order['customer_name']); ?></div>
                </div>

                <div class="info-box">
                    <div class="label">Phone</div>
                    <div class="value"><?= htmlspecialchars($order['phone'] ?? '-'); ?></div>
                </div>

                <div class="info-box">
                    <div class="label">Order Type</div>
                    <div class="value"><?= htmlspecialchars($order['order_type']); ?></div>
                </div>

                <div class="info-box">
                    <div class="label">Payment</div>
                    <div class="value"><?= htmlspecialchars($order['payment_method'] ?? 'COD'); ?></div>
                </div>

                <div class="info-box">
                    <div class="label">Status</div>
                    <div class="value"><?= htmlspecialchars($order['status']); ?></div>
                </div>

                <div class="info-box">
                    <div class="label">Total</div>
                    <div class="value">NT$ <?= number_format($order['total_amount']); ?></div>
                </div>
            </div>

            <br>

            <div class="info-box">
                <div class="label">Address / Pickup Location</div>
                <div class="value"><?= htmlspecialchars($order['delivery_address'] ?? '-'); ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Order Items</h2>

            <?php while($item = mysqli_fetch_assoc($items)): ?>
                <div class="item-row">
                    <div>
                        <strong><?= htmlspecialchars($item['product_name']); ?> × <?= $item['quantity']; ?></strong>
                        <br>
                        <small>
                            Ice: <?= htmlspecialchars($item['ice_level'] ?? 'N/A'); ?> |
                            Sugar: <?= htmlspecialchars($item['sugar_level'] ?? 'N/A'); ?>
                        </small>
                    </div>

                    <div>
                        NT$ <?= number_format($item['price'] * $item['quantity']); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="card">
            <h2>Update Order Status</h2>

            <form action="update_status.php" method="POST">
                <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">

                <select name="status" required>
                    <option value="Pending" <?= $order['status']=='Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Preparing" <?= $order['status']=='Preparing' ? 'selected' : ''; ?>>Preparing</option>
                    <option value="Ready" <?= $order['status']=='Ready' ? 'selected' : ''; ?>>Ready</option>

                    <?php if($order['order_type'] == 'Delivery'): ?>
                        <option value="Delivering" <?= $order['status']=='Delivering' ? 'selected' : ''; ?>>Delivering</option>
                    <?php endif; ?>

                    <option value="Completed" <?= $order['status']=='Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?= $order['status']=='Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <button type="submit" name="update_status" class="btn">
                    Save Status
                </button>
            </form>

            <a href="dashboard.php" class="back">Back to Dashboard</a>
        </div>

    </div>
</div>

</body>
</html>