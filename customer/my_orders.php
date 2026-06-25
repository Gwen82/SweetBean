<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$orders = mysqli_query($conn, "
    SELECT *
    FROM orders
    WHERE user_id = '$user_id'
    ORDER BY order_date DESC
");

function statusClass($status) {
    switch ($status) {
        case 'Pending': return 'pending';
        case 'Preparing': return 'preparing';
        case 'Ready': return 'ready';
        case 'Delivering': return 'delivering';
        case 'Completed': return 'completed';
        case 'Cancelled': return 'cancelled';
        default: return 'pending';
    }
}

function statusText($status, $orderType) {
    if ($status === 'Ready' && $orderType === 'Pickup') {
        return 'Ready for Pickup';
    }

    if ($status === 'Delivering') {
        return 'Out for Delivery';
    }

    return $status;
}

function stepDone($currentStatus, $step) {
    $order = [
        'Pending' => 1,
        'Preparing' => 2,
        'Ready' => 3,
        'Delivering' => 4,
        'Completed' => 5
    ];

    return ($order[$currentStatus] ?? 1) >= ($order[$step] ?? 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders | Sweet Bean Coffee</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    margin: 0;
    background: #fff8ef;
    color: #2a211b;
    font-family: Arial, sans-serif;
}

.orders-page {
    padding: 50px 22px 80px;
}

.orders-wrapper {
    max-width: 1000px;
    margin: auto;
}

.hero {
    background: #5a3825;
    color: white;
    padding: 34px;
    border-radius: 26px;
    box-shadow: 0 14px 35px rgba(70, 42, 25, 0.15);
    margin-bottom: 32px;
}

.hero h1 {
    margin: 0 0 8px;
    font-size: 34px;
}

.hero p {
    margin: 0;
    opacity: 0.9;
}

.section-title {
    margin: 35px 0 18px;
    color: #5a3825;
    font-size: 24px;
}

.order-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 12px 30px rgba(90, 56, 37, 0.08);
    border: 1px solid rgba(90, 56, 37, 0.1);
}

.order-top {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
    margin-bottom: 18px;
}

.order-id {
    font-size: 20px;
    font-weight: 900;
    color: #5a3825;
}

.order-date {
    font-size: 13px;
    color: #806e62;
    margin-top: 4px;
}

.total {
    font-size: 20px;
    font-weight: 900;
    color: #2a211b;
}

.status-badge {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 900;
    margin-bottom: 14px;
}

.pending {
    background: #fff3cd;
    color: #8a5a00;
}

.preparing {
    background: #dbeafe;
    color: #1d4ed8;
}

.ready {
    background: #dcfce7;
    color: #15803d;
}

.delivering {
    background: #ede9fe;
    color: #6d28d9;
}

.completed {
    background: #d1fae5;
    color: #047857;
}

.cancelled {
    background: #fee2e2;
    color: #b91c1c;
}

.order-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin: 16px 0;
}

.info-box {
    background: #fff8ef;
    padding: 14px;
    border-radius: 16px;
}

.info-label {
    font-size: 12px;
    color: #806e62;
    margin-bottom: 5px;
}

.info-value {
    font-weight: 800;
}

.items {
    margin-top: 16px;
    background: #faf7f2;
    padding: 16px;
    border-radius: 18px;
}

.item-row {
    display: flex;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid rgba(90,56,37,0.1);
}

.item-row:last-child {
    border-bottom: none;
}

.item-name {
    font-weight: 800;
}

.item-detail {
    font-size: 12px;
    color: #806e62;
    margin-top: 3px;
}

.timeline {
    margin-top: 20px;
    padding: 18px;
    background: #fff8ef;
    border-radius: 18px;
}

.timeline-title {
    font-weight: 900;
    color: #5a3825;
    margin-bottom: 14px;
}

.steps {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
}

.step {
    text-align: center;
    font-size: 12px;
    color: #9a8578;
}

.step-circle {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e8ded4;
    margin: 0 auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.step.done .step-circle {
    background: #5a3825;
}

.step.done {
    color: #5a3825;
    font-weight: 800;
}

.empty {
    text-align: center;
    background: white;
    padding: 60px 25px;
    border-radius: 24px;
    box-shadow: 0 12px 30px rgba(90, 56, 37, 0.08);
}

.empty-icon {
    font-size: 58px;
    margin-bottom: 18px;
}

.empty h2 {
    color: #5a3825;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 20px;
    border-radius: 12px;
    background: #5a3825;
    color: white;
    text-decoration: none;
    font-weight: 800;
    margin-top: 20px;
}

@media(max-width: 700px) {
    .order-top {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-info {
        grid-template-columns: 1fr;
    }

    .steps {
        grid-template-columns: 1fr;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }

    .step-circle {
        margin: 0;
    }
}
</style>
</head>

<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="orders-page">
    <div class="orders-wrapper">

        <div class="hero">
            <h1>My Orders</h1>
            <p>Track your coffee orders and view your order history.</p>
        </div>

        <?php if (!$orders || mysqli_num_rows($orders) == 0): ?>

            <div class="empty">
                <div class="empty-icon">☕</div>
                <h2>No Orders Yet</h2>
                <p>You have not placed any orders yet.</p>
                <a href="menu.php" class="btn">Browse Menu</a>
            </div>

        <?php else: ?>

            <h2 class="section-title">Current Orders</h2>

            <?php
            mysqli_data_seek($orders, 0);
            $hasCurrent = false;
            while ($order = mysqli_fetch_assoc($orders)):
                if (in_array($order['status'], ['Completed', 'Cancelled'])) {
                    continue;
                }

                $hasCurrent = true;
                $order_id = $order['order_id'];

                $items = mysqli_query($conn, "
                    SELECT order_items.*, menu.product_name
                    FROM order_items
                    LEFT JOIN menu ON order_items.menu_id = menu.menu_id
                    WHERE order_items.order_id = '$order_id'
                ");
            ?>

                <div class="order-card">
                    <div class="order-top">
                        <div>
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div class="order-date"><?php echo $order['order_date']; ?></div>
                        </div>

                        <div class="total">
                            NT$ <?php echo number_format($order['total_amount']); ?>
                        </div>
                    </div>

                    <span class="status-badge <?php echo statusClass($order['status']); ?>">
                        <?php echo statusText($order['status'], $order['order_type']); ?>
                    </span>

                    <div class="order-info">
                        <div class="info-box">
                            <div class="info-label">Order Type</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['order_type']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Payment</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></div>
                        </div>
                    </div>

                    <div class="items">
                        <?php while ($item = mysqli_fetch_assoc($items)): ?>
                            <div class="item-row">
                                <div>
                                    <div class="item-name">
                                        <?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?>
                                    </div>

                                    <div class="item-detail">
                                        Ice: <?php echo htmlspecialchars($item['ice_level'] ?? 'N/A'); ?> |
                                        Sugar: <?php echo htmlspecialchars($item['sugar_level'] ?? 'N/A'); ?>
                                    </div>
                                </div>

                                <div>
                                    NT$ <?php echo number_format($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php if ($order['status'] !== 'Cancelled'): ?>
                        <div class="timeline">
                            <div class="timeline-title">Order Progress</div>

                            <div class="steps">
                                <div class="step <?php echo stepDone($order['status'], 'Pending') ? 'done' : ''; ?>">
                                    <div class="step-circle">✓</div>
                                    Received
                                </div>

                                <div class="step <?php echo stepDone($order['status'], 'Preparing') ? 'done' : ''; ?>">
                                    <div class="step-circle">✓</div>
                                    Preparing
                                </div>

                                <div class="step <?php echo stepDone($order['status'], 'Ready') ? 'done' : ''; ?>">
                                    <div class="step-circle">✓</div>
                                    Ready
                                </div>

                                <div class="step <?php echo stepDone($order['status'], 'Delivering') ? 'done' : ''; ?>">
                                    <div class="step-circle">✓</div>
                                    Delivering
                                </div>

                                <div class="step <?php echo stepDone($order['status'], 'Completed') ? 'done' : ''; ?>">
                                    <div class="step-circle">✓</div>
                                    Completed
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

            <?php if (!$hasCurrent): ?>
                <p style="color:#806e62;">No current orders.</p>
            <?php endif; ?>

            <h2 class="section-title">Order History</h2>

            <?php
            mysqli_data_seek($orders, 0);
            $hasPast = false;
            while ($order = mysqli_fetch_assoc($orders)):
                if (!in_array($order['status'], ['Completed', 'Cancelled'])) {
                    continue;
                }

                $hasPast = true;
                $order_id = $order['order_id'];

                $items = mysqli_query($conn, "
                    SELECT order_items.*, menu.product_name
                    FROM order_items
                    LEFT JOIN menu ON order_items.menu_id = menu.menu_id
                    WHERE order_items.order_id = '$order_id'
                ");
            ?>

                <div class="order-card">
                    <div class="order-top">
                        <div>
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div class="order-date"><?php echo $order['order_date']; ?></div>
                        </div>

                        <div class="total">
                            NT$ <?php echo number_format($order['total_amount']); ?>
                        </div>
                    </div>

                    <span class="status-badge <?php echo statusClass($order['status']); ?>">
                        <?php echo statusText($order['status'], $order['order_type']); ?>
                    </span>

                    <div class="order-info">
                        <div class="info-box">
                            <div class="info-label">Order Type</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['order_type']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Payment</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></div>
                        </div>
                    </div>

                    <div class="items">
                        <?php while ($item = mysqli_fetch_assoc($items)): ?>
                            <div class="item-row">
                                <div>
                                    <div class="item-name">
                                        <?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?>
                                    </div>

                                    <div class="item-detail">
                                        Ice: <?php echo htmlspecialchars($item['ice_level'] ?? 'N/A'); ?> |
                                        Sugar: <?php echo htmlspecialchars($item['sugar_level'] ?? 'N/A'); ?>
                                    </div>
                                </div>

                                <div>
                                    NT$ <?php echo number_format($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

            <?php endwhile; ?>

            <?php if (!$hasPast): ?>
                <p style="color:#806e62;">No past orders yet.</p>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

</body>
</html>