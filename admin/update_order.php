<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) {
    admin_flash('Order not found.', 'error');
    admin_redirect('orders.php');
}

$statuses = admin_statuses();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    if (!in_array($status, $statuses, true)) {
        $error = 'Choose a valid order status.';
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        admin_flash('Order status updated.');
        admin_redirect('order_details.php?id=' . $id);
    }
}

admin_header('Update Order', 'Status changes sync to customer order tracking.');
if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<form class="form-card" method="post">
    <input type="hidden" name="id" value="<?php echo h($id); ?>">
    <label>Status
        <select name="status">
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo h($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo h($status); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/order_details.php?id=<?php echo h($id); ?>">Cancel</a>
        <button class="btn primary" type="submit">Update status</button>
    </div>
</form>
<?php admin_footer(); ?>
