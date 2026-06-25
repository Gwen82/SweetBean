<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$today = date('Y-m-d');
$staffName = $_SESSION['user_name'] ?? 'Staff Member';

$total_orders_today = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders WHERE DATE(order_date)='$today'
"))['total'];

$revenue_today = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount),0) AS total 
    FROM orders 
    WHERE DATE(order_date)='$today' AND status!='Cancelled'
"))['total'];

$new_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders WHERE status='Pending'
"))['total'];

$processing_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders WHERE status IN ('Preparing','Ready','Delivering')
"))['total'];

$hourData = [];
for ($i = 8; $i <= 18; $i += 2) {
    $hourData[$i] = 0;
}

$hourResult = mysqli_query($conn, "
    SELECT HOUR(order_date) AS order_hour, COUNT(*) AS total
    FROM orders
    WHERE DATE(order_date)='$today'
    GROUP BY HOUR(order_date)
");

while ($row = mysqli_fetch_assoc($hourResult)) {
    $hour = (int)$row['order_hour'];
    foreach ($hourData as $key => $value) {
        if ($hour >= $key && $hour < $key + 2) {
            $hourData[$key] += (int)$row['total'];
        }
    }
}

$maxHour = max($hourData);
if ($maxHour <= 0) $maxHour = 1;

$recent_orders = mysqli_query($conn, "
    SELECT orders.*, users.name AS customer_name
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    WHERE orders.status='Pending'
    ORDER BY orders.order_date DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Staff Main Menu</title>
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
.topbar{background:white;padding:28px;border-radius:28px;box-shadow:0 14px 35px rgba(90,56,37,.08);margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;gap:20px}
.topbar h1{margin:0;color:#5a3825;font-size:34px}
.topbar p{color:#806e62;margin:8px 0 0}
.staff-profile{display:flex;align-items:center;gap:14px;background:#fff8ef;padding:12px 16px;border-radius:999px}
.staff-avatar{width:46px;height:46px;border-radius:50%;background:#5a3825;color:white;display:flex;align-items:center;justify-content:center;font-weight:900}
.staff-name{font-weight:900;color:#5a3825}
.staff-role{font-size:13px;color:#806e62}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px}
.stat-card{background:white;padding:24px;border-radius:24px;box-shadow:0 14px 35px rgba(90,56,37,.08)}
.stat-label{color:#806e62;font-size:14px;font-weight:800}
.stat-number{margin-top:10px;color:#5a3825;font-size:32px;font-weight:900}
.stat-note{margin-top:6px;font-size:13px;color:#9a8578}
.content{display:grid;grid-template-columns:1.5fr .9fr;gap:22px}
.panel{background:white;border-radius:28px;padding:26px;box-shadow:0 14px 35px rgba(90,56,37,.08);margin-bottom:22px}
.panel h2{color:#5a3825;margin:0 0 20px}
.chart{height:240px;display:flex;align-items:flex-end;gap:18px;padding:20px;background:#fff8ef;border-radius:22px}
.bar-wrap{flex:1;text-align:center}
.bar{width:100%;max-width:45px;margin:0 auto 10px;background:linear-gradient(180deg,#d8b894,#8b5e3c);border-radius:12px 12px 0 0}
.bar-label{font-size:12px;color:#806e62}
.order-row{display:flex;justify-content:space-between;gap:14px;padding:15px 0;border-bottom:1px solid #eee}
.order-row:last-child{border-bottom:none}
.order-id{font-weight:900;color:#5a3825}
.small{font-size:13px;color:#806e62;margin-top:4px}
.badge{background:#fff3cd;color:#8a5a00;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:900}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 16px;border-radius:12px;background:#5a3825;color:white;text-decoration:none;font-weight:900}
.alert-card{display:flex;gap:14px;padding:16px;border-radius:18px;background:#fff8ef;margin-bottom:14px}
.alert-icon{width:42px;height:42px;border-radius:50%;background:#f1dfcd;display:flex;align-items:center;justify-content:center;color:#5a3825;font-weight:900}
.alert-title{font-weight:900;color:#5a3825}
.alert-text{font-size:13px;color:#806e62;margin-top:4px}
@media(max-width:1100px){.layout{grid-template-columns:1fr}.stats{grid-template-columns:repeat(2,1fr)}.content{grid-template-columns:1fr}}
@media(max-width:700px){.main{padding:20px}.topbar{flex-direction:column;align-items:flex-start}.stats{grid-template-columns:1fr}}
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
            <a href="dashboard.php" class="active">Main Menu</a>
            <a href="manage_orders.php">Manage Order</a>
            <a href="settings.php">Settings</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h1>Welcome Staff!</h1>
                <p>Today’s cafe operations, revenue, new order alerts, and staff notes.</p>
            </div>

            <div class="staff-profile">
                <div class="staff-avatar"><?= strtoupper(substr($staffName,0,1)); ?></div>
                <div>
                    <div class="staff-name"><?= htmlspecialchars($staffName); ?></div>
                    <div class="staff-role">Staff Member</div>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Orders Today</div>
                <div class="stat-number"><?= $total_orders_today; ?></div>
                <div class="stat-note">Total orders today</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Revenue Today</div>
                <div class="stat-number">NT$ <?= number_format($revenue_today); ?></div>
                <div class="stat-note">Today income</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">AVG Prep Time</div>
                <div class="stat-number">10m</div>
                <div class="stat-note">Estimated preparation</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">New Orders</div>
                <div class="stat-number"><?= $new_orders; ?></div>
                <div class="stat-note">Need staff action</div>
            </div>
        </div>

        <div class="content">
            <section>
                <div class="panel">
                    <h2>Hourly Order Distribution</h2>

                    <div class="chart">
                        <?php foreach($hourData as $hour => $count): ?>
                            <?php $height = max(12, ($count / $maxHour) * 180); ?>
                            <div class="bar-wrap">
                                <div class="bar" style="height:<?= $height; ?>px"></div>
                                <div class="bar-label"><?= $hour; ?>:00</div>
                                <div class="bar-label"><?= $count; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="panel">
                    <h2>Recent New Orders</h2>

                    <?php if(!$recent_orders || mysqli_num_rows($recent_orders) == 0): ?>
                        <p class="small">No pending orders right now.</p>
                    <?php else: ?>
                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <div class="order-row">
                                <div>
                                    <div class="order-id">Order #<?= $order['order_id']; ?></div>
                                    <div class="small">
                                        <?= htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?> |
                                        <?= htmlspecialchars($order['order_type']); ?> |
                                        <?= $order['order_date']; ?>
                                    </div>
                                </div>

                                <div>
                                    <span class="badge">Pending</span>
                                    <br><br>
                                    <a class="btn" href="manage_orders.php?selected=<?= $order['order_id']; ?>">View</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </section>

            <aside>
                <div class="panel">
                    <h2>New Order Notification</h2>

                    <?php if($new_orders > 0): ?>
                        <div class="alert-card">
                            <div class="alert-icon">!</div>
                            <div>
                                <div class="alert-title">New Order Alert</div>
                                <div class="alert-text">
                                    You have <?= $new_orders; ?> new order(s) waiting to be processed.
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="small">No new order notification.</p>
                    <?php endif; ?>
                </div>

                <div class="panel">
                    <h2>Staff Alerts</h2>

                    <div class="alert-card">
                        <div class="alert-icon">☕</div>
                        <div>
                            <div class="alert-title">Preparation Reminder</div>
                            <div class="alert-text">Update each order status after preparing.</div>
                        </div>
                    </div>

                    <div class="alert-card">
                        <div class="alert-icon">🚚</div>
                        <div>
                            <div class="alert-title">Delivery Reminder</div>
                            <div class="alert-text">Delivery orders should be marked as Delivering before Completed.</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</div>

</body>
</html>