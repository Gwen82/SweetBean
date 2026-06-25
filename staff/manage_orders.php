<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_POST['quick_update'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn, "
        UPDATE orders
        SET status='$status'
        WHERE order_id='$order_id'
    ");

    header("Location: manage_orders.php?selected=" . $order_id);
    exit();
}

$filter = $_GET['filter'] ?? 'Active';
$where = "WHERE 1";

if ($filter === 'Active') {
    $where .= " AND orders.status NOT IN ('Completed','Cancelled')";
} elseif ($filter !== 'All') {
    $filter_safe = mysqli_real_escape_string($conn, $filter);
    $where .= " AND orders.status='$filter_safe'";
}

$orders = mysqli_query($conn, "
    SELECT orders.*, users.name AS customer_name, users.phone
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    $where
    ORDER BY orders.order_date DESC
");

$selectedOrder = null;
$selectedItems = null;
$selected_id = $_GET['selected'] ?? '';

if ($selected_id !== '') {
    $selected_id_safe = mysqli_real_escape_string($conn, $selected_id);

    $selectedResult = mysqli_query($conn, "
        SELECT orders.*, users.name AS customer_name, users.phone
        FROM orders
        LEFT JOIN users ON orders.user_id = users.user_id
        WHERE orders.order_id='$selected_id_safe'
        LIMIT 1
    ");

    $selectedOrder = mysqli_fetch_assoc($selectedResult);

    if ($selectedOrder) {
        $selectedItems = mysqli_query($conn, "
            SELECT order_items.*, menu.product_name
            FROM order_items
            LEFT JOIN menu ON order_items.menu_id = menu.menu_id
            WHERE order_items.order_id='$selected_id_safe'
        ");
    }
}

function badgeClass($status) {
    if ($status == 'Pending') return 'pending';
    if ($status == 'Preparing') return 'preparing';
    if ($status == 'Ready') return 'ready';
    if ($status == 'Delivering') return 'delivering';
    if ($status == 'Completed') return 'completed';
    if ($status == 'Cancelled') return 'cancelled';
    return 'pending';
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manage Orders | Staff</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box}
body{margin:0;background:#f6efe7;color:#2a211b;font-family:Arial,sans-serif}
.layout{display:grid;grid-template-columns:250px 1fr;min-height:100vh}
.sidebar{background:linear-gradient(180deg,#3b2418,#5a3825);color:white;padding:28px 20px}
.logo-box{text-align:center;margin-bottom:36px}
.logo-box img{width:76px;height:76px;border-radius:50%;object-fit:cover;background:white;margin-bottom:10px}
.logo-box h2{margin:0;font-size:20px}
.nav-menu{display:flex;flex-direction:column;gap:14px}
.nav-menu a{text-decoration:none;color:white;padding:15px 18px;border-radius:16px;font-weight:800;background:rgba(255,255,255,.08)}
.nav-menu a.active,.nav-menu a:hover{background:#fff8ef;color:#5a3825}
.logout{margin-top:40px;background:rgba(255,255,255,.15)!important}
.main{padding:34px}
.topbar{background:white;padding:28px;border-radius:28px;box-shadow:0 14px 35px rgba(90,56,37,.08);margin-bottom:24px}
.topbar h1{margin:0;color:#5a3825;font-size:34px}
.topbar p{color:#806e62;margin:8px 0 0}
.content{display:grid;grid-template-columns:1.5fr .9fr;gap:22px}
.panel{background:white;border-radius:28px;padding:26px;box-shadow:0 14px 35px rgba(90,56,37,.08)}
.panel h2{color:#5a3825;margin:0 0 20px}
.filter-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.filter-tabs a{text-decoration:none;padding:10px 15px;border-radius:999px;background:#fff8ef;color:#5a3825;font-weight:900;font-size:13px}
.filter-tabs a.active{background:#5a3825;color:white}
.order-card{display:grid;grid-template-columns:1fr auto;gap:16px;padding:18px;border-radius:20px;background:#fff8ef;border:1px solid rgba(90,56,37,.08);margin-bottom:14px}
.order-id{font-weight:900;color:#5a3825;font-size:18px}
.small{font-size:13px;color:#806e62;margin-top:5px}
.price{font-weight:900;font-size:18px;text-align:right;margin-bottom:10px}
.badge{display:inline-block;margin-top:10px;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:900}
.pending{background:#fff3cd;color:#8a5a00}.preparing{background:#dbeafe;color:#1d4ed8}.ready{background:#dcfce7;color:#15803d}.delivering{background:#ede9fe;color:#6d28d9}.completed{background:#d1fae5;color:#047857}.cancelled{background:#fee2e2;color:#b91c1c}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 16px;border-radius:12px;background:#5a3825;color:white;text-decoration:none;font-weight:900;border:none;cursor:pointer}
.detail-card{background:#fff8ef;border-radius:22px;padding:20px}
.detail-title{font-size:24px;font-weight:900;color:#5a3825}
.detail-row{display:flex;justify-content:space-between;border-bottom:1px solid #eadfd6;padding:12px 0;gap:15px}
.detail-row span{color:#806e62}.detail-row strong{text-align:right}
.items{margin-top:16px;background:white;padding:16px;border-radius:18px}
.item-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1e8df}
.item-row:last-child{border-bottom:none}
.item-name{font-weight:900}
.item-options{font-size:12px;color:#806e62;margin-top:4px}
.status-form{margin-top:18px}
.status-form select{width:100%;padding:14px;border-radius:14px;border:1px solid #ddd;margin-bottom:12px}
.empty{text-align:center;color:#806e62;padding:24px;background:#fff8ef;border-radius:18px}
@media(max-width:1100px){.layout{grid-template-columns:1fr}.content{grid-template-columns:1fr}}
@media(max-width:700px){.main{padding:20px}.order-card{grid-template-columns:1fr}.price{text-align:left}}
</style>
</head>

<body>

<div class="layout">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/LOGO.jpg">
            <h2>Sweet Bean Staff</h2>
        </div>

        <nav class="nav-menu">
            <a href="dashboard.php">Main Menu</a>
            <a href="manage_orders.php" class="active">Manage Order</a>
            <a href="settings.php">Settings</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <h1>Manage Orders</h1>
            <p>View incoming orders, update order status, and manage delivery progress.</p>
        </div>

        <div class="content">
            <section class="panel">
                <h2>Order Queue</h2>

                <div class="filter-tabs">
                    <?php $tabs = ['Active','All','Pending','Preparing','Ready','Delivering','Completed','Cancelled']; ?>
                    <?php foreach($tabs as $tab): ?>
                        <a href="manage_orders.php?filter=<?= urlencode($tab); ?>" class="<?= $filter===$tab ? 'active' : ''; ?>">
                            <?= $tab; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if(!$orders || mysqli_num_rows($orders) == 0): ?>
                    <div class="empty">No orders found.</div>
                <?php else: ?>
                    <?php while($order = mysqli_fetch_assoc($orders)): ?>
                        <div class="order-card">
                            <div>
                                <div class="order-id">Order #<?= $order['order_id']; ?></div>
                                <div class="small">
                                    Customer: <?= htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?> |
                                    <?= htmlspecialchars($order['order_type']); ?> |
                                    <?= $order['order_date']; ?>
                                </div>
                                <div class="small">
                                    Address/Pickup: <?= htmlspecialchars($order['delivery_address'] ?? '-'); ?>
                                </div>
                                <span class="badge <?= badgeClass($order['status']); ?>">
                                    <?= htmlspecialchars($order['status']); ?>
                                </span>
                            </div>

                            <div>
                                <div class="price">NT$ <?= number_format($order['total_amount']); ?></div>
                                <a class="btn" href="manage_orders.php?selected=<?= $order['order_id']; ?>&filter=<?= urlencode($filter); ?>">
                                    View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </section>

            <aside class="panel">
                <h2>Selected Order</h2>

                <?php if(!$selectedOrder): ?>
                    <div class="empty">Select an order to view details and update status.</div>
                <?php else: ?>
                    <div class="detail-card">
                        <div class="detail-title">Order #<?= $selectedOrder['order_id']; ?></div>

                        <div class="detail-row"><span>Customer</span><strong><?= htmlspecialchars($selectedOrder['customer_name'] ?? 'Unknown'); ?></strong></div>
                        <div class="detail-row"><span>Phone</span><strong><?= htmlspecialchars($selectedOrder['phone'] ?? '-'); ?></strong></div>
                        <div class="detail-row"><span>Type</span><strong><?= htmlspecialchars($selectedOrder['order_type']); ?></strong></div>
                        <div class="detail-row"><span>Payment</span><strong><?= htmlspecialchars($selectedOrder['payment_method'] ?? 'COD'); ?></strong></div>
                        <div class="detail-row"><span>Status</span><strong><?= htmlspecialchars($selectedOrder['status']); ?></strong></div>
                        <div class="detail-row"><span>Total</span><strong>NT$ <?= number_format($selectedOrder['total_amount']); ?></strong></div>

                        <div class="items">
                            <?php if($selectedItems && mysqli_num_rows($selectedItems) > 0): ?>
                                <?php while($item = mysqli_fetch_assoc($selectedItems)): ?>
                                    <div class="item-row">
                                        <div>
                                            <div class="item-name"><?= htmlspecialchars($item['product_name']); ?> × <?= $item['quantity']; ?></div>
                                            <div class="item-options">
                                                Ice: <?= htmlspecialchars($item['ice_level'] ?? 'N/A'); ?> |
                                                Sugar: <?= htmlspecialchars($item['sugar_level'] ?? 'N/A'); ?>
                                            </div>
                                        </div>
                                        <div>NT$ <?= number_format($item['price'] * $item['quantity']); ?></div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                No item data.
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $selectedOrder['order_id']; ?>">

                            <select name="status" required>
                                <option value="Pending" <?= $selectedOrder['status']=='Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Preparing" <?= $selectedOrder['status']=='Preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="Ready" <?= $selectedOrder['status']=='Ready' ? 'selected' : ''; ?>>Ready</option>
                                <?php if($selectedOrder['order_type'] == 'Delivery'): ?>
                                    <option value="Delivering" <?= $selectedOrder['status']=='Delivering' ? 'selected' : ''; ?>>Delivering</option>
                                <?php endif; ?>
                                <option value="Completed" <?= $selectedOrder['status']=='Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?= $selectedOrder['status']=='Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>

                            <button type="submit" name="quick_update" class="btn">Update Status</button>
                        </form>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </main>
</div>

</body>
</html>