<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = $_GET['id'] ?? $_POST['id'] ?? '';
$stmt = $conn->prepare("SELECT id, name FROM menu_items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    admin_flash('Menu item not found.', 'error');
    admin_redirect('menu.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        admin_flash('Menu item deleted.');
    } catch (Throwable $e) {
        admin_flash('This item has order history, so it was hidden from the customer menu instead of deleted.', 'error');
        $stmt = $conn->prepare("UPDATE menu_items SET is_available = false WHERE id = ?");
        $stmt->execute([$id]);
    }
    admin_redirect('menu.php');
}

admin_header('Delete Menu Item', 'Confirm before removing this item from menu records.');
?>
<form class="form-card" method="post">
    <input type="hidden" name="id" value="<?php echo h($item['id']); ?>">
    <p>Delete <strong><?php echo h($item['name']); ?></strong>?</p>
    <p class="muted">If this item has order history, PostgreSQL may prevent deletion. Hide it from the menu instead if you want to keep reports intact.</p>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/menu.php">Cancel</a>
        <button class="btn danger" type="submit">Delete item</button>
    </div>
</form>
<?php admin_footer(); ?>
