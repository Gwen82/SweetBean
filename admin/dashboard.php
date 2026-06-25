<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$today = date('Y-m-d');

$total_menu = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM menu
"))['total'];

$total_staff = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM users
    WHERE role='staff'
"))['total'];

$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM orders
"))['total'];

$completed_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount),0) AS total
    FROM orders
    WHERE status='Completed'
"))['total'];

$today_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount),0) AS total
    FROM orders
    WHERE DATE(order_date)='$today'
    AND status!='Cancelled'
"))['total'];

$today_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE DATE(order_date)='$today'
"))['total'];

$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status='Pending'
"))['total'];

$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM users
    WHERE role='customer'
"))['total'];

$recentOrders = mysqli_query($conn, "
    SELECT orders.*, users.name AS customer_name
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.order_date DESC
    LIMIT 6
");

$popularItems = mysqli_query($conn, "
    SELECT 
        menu.product_name,
        SUM(order_items.quantity) AS qty,
        SUM(order_items.quantity * order_items.price) AS total
    FROM order_items
    LEFT JOIN menu ON order_items.menu_id = menu.menu_id
    GROUP BY menu.product_name
    ORDER BY qty DESC
    LIMIT 5
");

$dailySales = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dailySales[$date] = 0;
}

$salesResult = mysqli_query($conn, "
    SELECT DATE(order_date) AS sales_date, COALESCE(SUM(total_amount),0) AS total
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    AND status!='Cancelled'
    GROUP BY DATE(order_date)
");

while ($row = mysqli_fetch_assoc($salesResult)) {
    if (isset($dailySales[$row['sales_date']])) {
        $dailySales[$row['sales_date']] = (float)$row['total'];
    }
}

$maxSales = max($dailySales);
if ($maxSales <= 0) {
    $maxSales = 1;
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

$adminName = $_SESSION['user_name'] ?? 'Admin';
$avatar = strtoupper(substr($adminName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Sweet Bean Coffee</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f6efe7;
    color: #2a211b;
    font-family: Arial, sans-serif;
}

.layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
}

.sidebar {
    background: linear-gradient(180deg, #2f1d14, #5a3825);
    color: white;
    padding: 28px 20px;
}

.logo-box {
    text-align: center;
    margin-bottom: 36px;
}

.logo-box img {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    object-fit: cover;
    background: white;
    margin-bottom: 10px;
}

.logo-box h2 {
    margin: 0;
    font-size: 20px;
}

.logo-box p {
    margin: 6px 0 0;
    color: #ead7c7;
    font-size: 13px;
}

.nav-menu {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.nav-menu a {
    text-decoration: none;
    color: white;
    padding: 15px 18px;
    border-radius: 16px;
    font-weight: 800;
    background: rgba(255,255,255,.08);
}

.nav-menu a.active,
.nav-menu a:hover {
    background: #fff8ef;
    color: #5a3825;
}

.logout {
    margin-top: 40px;
    background: rgba(255,255,255,.15)!important;
}

.main {
    padding: 34px;
}

.topbar {
    background: white;
    padding: 28px;
    border-radius: 28px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
}

.topbar h1 {
    margin: 0;
    color: #5a3825;
    font-size: 34px;
}

.topbar p {
    color: #806e62;
    margin: 8px 0 0;
}

.admin-pill {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff8ef;
    padding: 12px 16px;
    border-radius: 999px;
}

.avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
}

.admin-pill strong {
    color: #5a3825;
}

.admin-pill span {
    display: block;
    color: #806e62;
    font-size: 13px;
    margin-top: 2px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 24px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
}

.stat-label {
    color: #806e62;
    font-size: 14px;
    font-weight: 800;
}

.stat-number {
    margin-top: 10px;
    color: #5a3825;
    font-size: 30px;
    font-weight: 900;
}

.stat-note {
    margin-top: 6px;
    font-size: 13px;
    color: #9a8578;
}

.content {
    display: grid;
    grid-template-columns: 1.45fr .9fr;
    gap: 22px;
}

.panel {
    background: white;
    border-radius: 28px;
    padding: 26px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
    margin-bottom: 22px;
}

.panel h2 {
    color: #5a3825;
    margin: 0 0 20px;
}

.chart {
    height: 250px;
    display: flex;
    align-items: flex-end;
    gap: 16px;
    padding: 20px;
    background: #fff8ef;
    border-radius: 22px;
}

.bar-wrap {
    flex: 1;
    text-align: center;
}

.bar {
    width: 100%;
    max-width: 48px;
    margin: 0 auto 10px;
    background: linear-gradient(180deg, #d8b894, #8b5e3c);
    border-radius: 12px 12px 0 0;
}

.bar-label {
    font-size: 12px;
    color: #806e62;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    color: #806e62;
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eadfd6;
}

td {
    padding: 16px 10px;
    border-bottom: 1px solid #f1e8df;
    vertical-align: top;
}

.order-id {
    font-weight: 900;
    color: #5a3825;
}

.small {
    font-size: 13px;
    color: #806e62;
    margin-top: 4px;
}

.badge {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
}

.pending { background: #fff3cd; color: #8a5a00; }
.preparing { background: #dbeafe; color: #1d4ed8; }
.ready { background: #dcfce7; color: #15803d; }
.delivering { background: #ede9fe; color: #6d28d9; }
.completed { background: #d1fae5; color: #047857; }
.cancelled { background: #fee2e2; color: #b91c1c; }

.quick-grid {
    display: grid;
    gap: 14px;
}

.quick-card {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
    background: #fff8ef;
    padding: 18px;
    border-radius: 20px;
    text-decoration: none;
    color: #2a211b;
}

.quick-card:hover {
    background: #f1e5d9;
}

.quick-card strong {
    color: #5a3825;
}

.quick-card span {
    display: block;
    color: #806e62;
    font-size: 13px;
    margin-top: 5px;
}

.quick-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
}

.empty {
    padding: 24px;
    text-align: center;
    background: #fff8ef;
    border-radius: 18px;
    color: #806e62;
}

@media(max-width: 1100px) {
    .layout {
        grid-template-columns: 1fr;
    }

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .content {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 700px) {
    .main {
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="layout">

    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/LOGO.jpg" alt="Sweet Bean Logo">
            <h2>Sweet Bean Admin</h2>
            <p>Operation Control Panel</p>
        </div>

        <nav class="nav-menu">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="manage_menu.php">Manage Menu</a>
            <a href="employees.php">Employees</a>
            <a href="reports.php">Sales Reports</a>
            <a href="reviews.php">Reviews</a>
            <a href="newsletter.php">Newsletter</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Admin Dashboard</h1>
                <p>A synced view of cafe activity, sales, staff, menu health, and recent orders.</p>
            </div>

            <div class="admin-pill">
                <div class="avatar"><?php echo htmlspecialchars($avatar); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($adminName); ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Menu Items</div>
                <div class="stat-number"><?php echo $total_menu; ?></div>
                <div class="stat-note">Available cafe products</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Staff Members</div>
                <div class="stat-number"><?php echo $total_staff; ?></div>
                <div class="stat-note">Registered staff accounts</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Orders Today</div>
                <div class="stat-number"><?php echo $today_orders; ?></div>
                <div class="stat-note">Today customer orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Revenue Today</div>
                <div class="stat-number">NT$ <?php echo number_format($today_revenue); ?></div>
                <div class="stat-note">Not including cancelled orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-note">All order records</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Completed Revenue</div>
                <div class="stat-number">NT$ <?php echo number_format($completed_revenue); ?></div>
                <div class="stat-note">Completed orders only</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-number"><?php echo $pending_orders; ?></div>
                <div class="stat-note">Need staff action</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Customers</div>
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div class="stat-note">Registered customers</div>
            </div>
        </div>

        <div class="content">

            <section>

                <div class="panel">
                    <h2>Sales Overview - Last 7 Days</h2>

                    <div class="chart">
                        <?php foreach ($dailySales as $date => $amount): ?>
                            <?php $height = max(12, ($amount / $maxSales) * 185); ?>

                            <div class="bar-wrap">
                                <div class="bar" style="height: <?php echo $height; ?>px;"></div>
                                <div class="bar-label"><?php echo date('M d', strtotime($date)); ?></div>
                                <div class="bar-label">NT$ <?php echo number_format($amount); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="panel">
                    <h2>Recent Orders</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                                    <?php while($order = mysqli_fetch_assoc($recentOrders)): ?>
                                        <tr>
                                            <td>
                                                <div class="order-id">#<?php echo $order['order_id']; ?></div>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($order['customer_name'] ?? 'Customer'); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($order['order_type']); ?>
                                            </td>

                                            <td>
                                                <span class="badge <?php echo badgeClass($order['status']); ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </td>

                                            <td>
                                                NT$ <?php echo number_format($order['total_amount']); ?>
                                            </td>

                                            <td>
                                                <div class="small">
                                                    <?php echo htmlspecialchars($order['order_date']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty">No orders yet.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>

            <aside>

                <div class="panel">
                    <h2>Quick Actions</h2>

                    <div class="quick-grid">
                        <a href="manage_menu.php" class="quick-card">
                            <div>
                                <strong>Manage Menu</strong>
                                <span>Add, edit, or remove menu items.</span>
                            </div>
                            <div class="quick-icon">M</div>
                        </a>

                        <a href="employees.php" class="quick-card">
                            <div>
                                <strong>Employees</strong>
                                <span>Manage staff and employee records.</span>
                            </div>
                            <div class="quick-icon">E</div>
                        </a>

                        <a href="reports.php" class="quick-card">
                            <div>
                                <strong>Sales Reports</strong>
                                <span>View daily and monthly revenue.</span>
                            </div>
                            <div class="quick-icon">R</div>
                        </a>

                        <a href="reviews.php" class="quick-card">
                            <div>
                                <strong>Reviews</strong>
                                <span>Monitor customer feedback.</span>
                            </div>
                            <div class="quick-icon">★</div>
                        </a>
                    </div>
                </div>

                <div class="panel">
                    <h2>Popular Items</h2>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($popularItems && mysqli_num_rows($popularItems) > 0): ?>
                                    <?php while($item = mysqli_fetch_assoc($popularItems)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Item'); ?></td>
                                            <td><?php echo (int)$item['qty']; ?></td>
                                            <td>NT$ <?php echo number_format($item['total']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="empty">No item sales yet.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </aside>

        </div>

    </main>

</div>

</body>
</html>