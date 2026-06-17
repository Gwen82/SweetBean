<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$status = $_GET['status'] ?? '';
$statuses = admin_statuses();
$params = [];
$where = '';
if (in_array($status, $statuses, true)) {
    $where = 'WHERE o.status = ?';
    $params[] = $status;
}

$stmt = $conn->prepare("
    SELECT o.id, o.method, o.address, o.total_price, o.status, o.created_at, u.full_name, u.email
    FROM orders o
    LEFT JOIN sweetbean_users u ON u.id = o.user_id
    $where
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$actions = '<form method="get"><select name="status" onchange="this.form.submit()"><option value="">All statuses</option>';
foreach ($statuses as $option) {
    $actions .= '<option value="' . h($option) . '"' . ($status === $option ? ' selected' : '') . '>' . h($option) . '</option>';
}
$actions .= '</select></form>';

admin_header('Orders', 'Track and update customer order progress.', $actions);
?>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Method</th><th>Status</th><th>Total</th><th>Placed</th><th class="right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo h($order['id']); ?></td>
                        <td><?php echo h($order['full_name'] ?? 'Customer'); ?><br><span class="muted"><?php echo h($order['email'] ?? ''); ?></span></td>
                        <td><?php echo h(ucfirst($order['method'])); ?></td>
                        <td><span class="badge gold"><?php echo h($order['status']); ?></span></td>
                        <td><?php echo money($order['total_price']); ?></td>
                        <td><?php echo h(date('M d, Y H:i', strtotime($order['created_at']))); ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="<?php echo BASE_URL; ?>admin/order_details.php?id=<?php echo h($order['id']); ?>"><i class="fa-solid fa-eye"></i></a>
                                <a class="btn" href="<?php echo BASE_URL; ?>admin/update_order.php?id=<?php echo h($order['id']); ?>"><i class="fa-solid fa-rotate"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?><tr><td colspan="7" class="empty-state">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
