<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare("
    SELECT o.*, u.full_name, u.email, u.phone
    FROM orders o
    LEFT JOIN sweetbean_users u ON u.id = o.user_id
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) {
    admin_flash('Order not found.', 'error');
    admin_redirect('orders.php');
}

$stmt = $conn->prepare("
    SELECT oi.*, COALESCE(mi.name, oi.menu_id) AS item_name
    FROM order_items oi
    LEFT JOIN menu_items mi ON mi.id = oi.menu_id
    WHERE oi.order_id = ?
    ORDER BY oi.id
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

admin_header('Order #' . $id, 'Customer details, fulfillment notes, and item summary.', '<a class="btn primary" href="' . BASE_URL . 'admin/update_order.php?id=' . h($id) . '"><i class="fa-solid fa-rotate"></i> Update status</a>');
?>
<section class="stats-grid">
    <div class="stat-card"><span>Status</span><strong><?php echo h($order['status']); ?></strong></div>
    <div class="stat-card"><span>Method</span><strong><?php echo h(ucfirst($order['method'])); ?></strong></div>
    <div class="stat-card"><span>Total</span><strong><?php echo money($order['total_price']); ?></strong></div>
    <div class="stat-card"><span>Placed</span><strong><?php echo h(date('M d', strtotime($order['created_at']))); ?></strong></div>
</section>

<section class="panel">
    <div class="panel-header"><h2>Customer</h2></div>
    <div style="padding:18px;">
        <p><strong><?php echo h($order['full_name'] ?? 'Customer'); ?></strong></p>
        <p class="muted"><?php echo h($order['email'] ?? ''); ?> <?php echo h($order['phone'] ?? ''); ?></p>
        <p><?php echo nl2br(h($order['address'])); ?></p>
    </div>
</section>

<section class="panel">
    <div class="panel-header"><h2>Items</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th class="right">Line total</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo h($item['item_name']); ?></td>
                        <td><?php echo h($item['qty']); ?></td>
                        <td><?php echo money($item['price']); ?></td>
                        <td class="right"><?php echo money($item['price'] * $item['qty']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr><td colspan="3" class="right">Subtotal</td><td class="right"><?php echo money($order['subtotal']); ?></td></tr>
                <tr><td colspan="3" class="right">Delivery fee</td><td class="right"><?php echo money($order['delivery_fee']); ?></td></tr>
                <tr><td colspan="3" class="right"><strong>Total</strong></td><td class="right"><strong><?php echo money($order['total_price']); ?></strong></td></tr>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
