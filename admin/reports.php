<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$summary = $conn->query("
    SELECT
        COUNT(*) AS orders,
        COALESCE(SUM(total_price), 0) AS revenue,
        COALESCE(AVG(total_price), 0) AS average_order,
        COALESCE(SUM(CASE WHEN status = 'Completed' THEN total_price ELSE 0 END), 0) AS completed_revenue
    FROM orders
")->fetch();

$byStatus = $conn->query("
    SELECT status, COUNT(*) AS orders, COALESCE(SUM(total_price), 0) AS revenue
    FROM orders
    GROUP BY status
    ORDER BY status
")->fetchAll();

$daily = $conn->query("
    SELECT created_at::date AS day, COUNT(*) AS orders, COALESCE(SUM(total_price), 0) AS revenue
    FROM orders
    GROUP BY created_at::date
    ORDER BY day DESC
    LIMIT 14
")->fetchAll();

admin_header('Sales Reports', 'Review order volume, revenue, and recent sales activity.');
?>
<section class="stats-grid">
    <div class="stat-card"><span>Total orders</span><strong><?php echo h($summary['orders']); ?></strong></div>
    <div class="stat-card"><span>Total revenue</span><strong><?php echo money($summary['revenue']); ?></strong></div>
    <div class="stat-card"><span>Completed revenue</span><strong><?php echo money($summary['completed_revenue']); ?></strong></div>
    <div class="stat-card"><span>Average order</span><strong><?php echo money($summary['average_order']); ?></strong></div>
</section>

<section class="panel">
    <div class="panel-header"><h2>Orders by Status</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Status</th><th>Orders</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php foreach ($byStatus as $row): ?>
                    <tr><td><?php echo h($row['status']); ?></td><td><?php echo h($row['orders']); ?></td><td><?php echo money($row['revenue']); ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$byStatus): ?><tr><td colspan="3" class="empty-state">No report data yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-header"><h2>Recent Daily Sales</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php foreach ($daily as $row): ?>
                    <tr><td><?php echo h(date('M d, Y', strtotime($row['day']))); ?></td><td><?php echo h($row['orders']); ?></td><td><?php echo money($row['revenue']); ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$daily): ?><tr><td colspan="3" class="empty-state">No daily sales yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
