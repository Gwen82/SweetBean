<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$stats = [
    'menu_items' => (int) $conn->query("SELECT COUNT(*) FROM menu_items")->fetchColumn(),
    'staff' => (int) $conn->query("SELECT COUNT(*) FROM sweetbean_users WHERE role = 'staff'")->fetchColumn(),
    'orders' => (int) $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'revenue' => (float) $conn->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'Completed'")->fetchColumn(),
];

$recentOrders = $conn->query("
    SELECT o.id, o.method, o.total_price, o.status, o.created_at, u.full_name
    FROM orders o
    LEFT JOIN sweetbean_users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 6
")->fetchAll();

$popularItems = $conn->query("
    SELECT COALESCE(mi.name, oi.menu_id) AS name, SUM(oi.qty) AS qty, SUM(oi.qty * oi.price) AS total
    FROM order_items oi
    LEFT JOIN menu_items mi ON mi.id = oi.menu_id
    GROUP BY COALESCE(mi.name, oi.menu_id)
    ORDER BY qty DESC
    LIMIT 5
")->fetchAll();

admin_header('Dashboard', 'A synced view of cafe activity, sales, staff, and menu health.');
?>
<section class="stats-grid">
    <div class="stat-card"><span>Menu items</span><strong><?php echo $stats['menu_items']; ?></strong></div>
    <div class="stat-card"><span>Staff members</span><strong><?php echo $stats['staff']; ?></strong></div>
    <div class="stat-card"><span>Total orders</span><strong><?php echo $stats['orders']; ?></strong></div>
    <div class="stat-card"><span>Completed revenue</span><strong><?php echo money($stats['revenue']); ?></strong></div>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Recent Orders</h2>
        <a class="btn ghost" href="<?php echo BASE_URL; ?>admin/orders.php">View all</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Method</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><a href="<?php echo BASE_URL; ?>admin/order_details.php?id=<?php echo h($order['id']); ?>">#<?php echo h($order['id']); ?></a></td>
                        <td><?php echo h($order['full_name'] ?? 'Customer'); ?></td>
                        <td><?php echo h(ucfirst($order['method'])); ?></td>
                        <td><span class="badge gold"><?php echo h($order['status']); ?></span></td>
                        <td><?php echo money($order['total_price']); ?></td>
                        <td><?php echo h(date('M d, Y H:i', strtotime($order['created_at']))); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentOrders): ?><tr><td colspan="6" class="empty-state">No orders yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-header"><h2>Popular Items</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Quantity sold</th><th>Sales</th></tr></thead>
            <tbody>
                <?php foreach ($popularItems as $item): ?>
                    <tr>
                        <td><?php echo h($item['name']); ?></td>
                        <td><?php echo h($item['qty']); ?></td>
                        <td><?php echo money($item['total']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$popularItems): ?><tr><td colspan="3" class="empty-state">No item sales yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
