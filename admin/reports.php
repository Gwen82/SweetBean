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
$this_month = date('Y-m');

$today_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders
    WHERE DATE(order_date)='$today'
"))['total'];

$today_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount),0) AS total FROM orders
    WHERE DATE(order_date)='$today'
    AND status!='Cancelled'
"))['total'];

$monthly_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders
    WHERE DATE_FORMAT(order_date, '%Y-%m')='$this_month'
"))['total'];

$monthly_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount),0) AS total FROM orders
    WHERE DATE_FORMAT(order_date, '%Y-%m')='$this_month'
    AND status!='Cancelled'
"))['total'];

$pickup_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders
    WHERE order_type='Pickup'
"))['total'];

$delivery_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM orders
    WHERE order_type='Delivery'
"))['total'];

$status_report = mysqli_query($conn, "
    SELECT status, COUNT(*) AS total
    FROM orders
    GROUP BY status
");

$daily_report = mysqli_query($conn, "
    SELECT 
        DATE(order_date) AS report_date,
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount),0) AS revenue
    FROM orders
    WHERE status!='Cancelled'
    GROUP BY DATE(order_date)
    ORDER BY report_date DESC
    LIMIT 14
");

$monthly_report = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') AS report_month,
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount),0) AS revenue
    FROM orders
    WHERE status!='Cancelled'
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY report_month DESC
    LIMIT 12
");

$adminName = $_SESSION['user_name'] ?? 'Admin';
$avatar = strtoupper(substr($adminName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Reports | Sweet Bean Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { box-sizing: border-box; }

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

.admin-pill strong { color: #5a3825; }

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
    grid-template-columns: 1fr 1fr;
    gap: 22px;
}

.panel {
    background: white;
    border-radius: 28px;
    padding: 26px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
}

.panel h2 {
    color: #5a3825;
    margin: 0 0 20px;
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
}

.badge {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    background: #fff3cd;
    color: #8a5a00;
}

.report-box {
    background: #fff8ef;
    padding: 18px;
    border-radius: 20px;
    margin-bottom: 14px;
}

.report-title {
    color: #5a3825;
    font-weight: 900;
}

.report-text {
    color: #806e62;
    margin-top: 6px;
    font-size: 14px;
}

@media(max-width: 1100px) {
    .layout { grid-template-columns: 1fr; }
    .stats { grid-template-columns: repeat(2, 1fr); }
    .content { grid-template-columns: 1fr; }
}

@media(max-width: 700px) {
    .main { padding: 20px; }
    .topbar { flex-direction: column; align-items: flex-start; }
    .stats { grid-template-columns: 1fr; }
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
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_menu.php">Manage Menu</a>
            <a href="employees.php">Employees</a>
            <a href="reports.php" class="active">Sales Reports</a>
            <a href="reviews.php">Reviews</a>
            <a href="newsletter.php">Newsletter</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Sales Reports</h1>
                <p>View daily sales, monthly sales, order summary, and revenue summary.</p>
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
                <div class="stat-label">Today Orders</div>
                <div class="stat-number"><?php echo $today_orders; ?></div>
                <div class="stat-note">Orders made today</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Today Revenue</div>
                <div class="stat-number">NT$ <?php echo number_format($today_revenue); ?></div>
                <div class="stat-note">Excluding cancelled orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Monthly Orders</div>
                <div class="stat-number"><?php echo $monthly_orders; ?></div>
                <div class="stat-note">Orders this month</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Monthly Revenue</div>
                <div class="stat-number">NT$ <?php echo number_format($monthly_revenue); ?></div>
                <div class="stat-note">This month revenue</div>
            </div>
        </div>

        <div class="content">

            <section class="panel">
                <h2>Daily Sales Report</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if($daily_report && mysqli_num_rows($daily_report) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($daily_report)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['report_date']); ?></td>
                                        <td><?php echo $row['total_orders']; ?></td>
                                        <td>NT$ <?php echo number_format($row['revenue']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No daily report data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <h2>Monthly Sales Report</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Total Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if($monthly_report && mysqli_num_rows($monthly_report) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($monthly_report)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['report_month']); ?></td>
                                        <td><?php echo $row['total_orders']; ?></td>
                                        <td>NT$ <?php echo number_format($row['revenue']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No monthly report data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <h2>Order Type Summary</h2>

                <div class="report-box">
                    <div class="report-title">Pickup Orders</div>
                    <div class="report-text"><?php echo $pickup_orders; ?> orders selected pickup service.</div>
                </div>

                <div class="report-box">
                    <div class="report-title">Delivery Orders</div>
                    <div class="report-text"><?php echo $delivery_orders; ?> orders selected delivery service.</div>
                </div>
            </section>

            <section class="panel">
                <h2>Status Summary</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if($status_report && mysqli_num_rows($status_report) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($status_report)): ?>
                                    <tr>
                                        <td><span class="badge"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                        <td><?php echo $row['total']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2">No status report data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

    </main>

</div>

</body>
</html>