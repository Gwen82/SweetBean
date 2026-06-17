<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$item = ['id' => '', 'name' => '', 'category' => 'Drinks', 'price' => '', 'description' => '', 'badge' => '', 'icon' => 'fa-mug-hot', 'is_available' => true, 'sort_order' => 0];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = [
        'id' => trim($_POST['id'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'price' => trim($_POST['price'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'badge' => trim($_POST['badge'] ?? ''),
        'icon' => trim($_POST['icon'] ?? 'fa-mug-hot'),
        'is_available' => isset($_POST['is_available']),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($item['id'] === '' || $item['name'] === '' || $item['category'] === '' || !is_numeric($item['price'])) {
        $error = 'Item ID, name, category, and a valid price are required.';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO menu_items (id, name, category, price, description, badge, icon, is_available, sort_order)
                VALUES (:id, :name, :category, :price, :description, :badge, :icon, :is_available, :sort_order)
            ");
            $stmt->execute($item);
            admin_flash('Menu item added.');
            admin_redirect('menu.php');
        } catch (Throwable $e) {
            $error = 'Could not add item. The ID may already exist.';
        }
    }
}

admin_header('Add Menu Item', 'New items appear on the customer menu when marked available.');
if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<form class="form-card" method="post">
    <div class="form-grid">
        <label>Item ID<input name="id" value="<?php echo h($item['id']); ?>" placeholder="iced-matcha-latte" required></label>
        <label>Name<input name="name" value="<?php echo h($item['name']); ?>" required></label>
        <label>Category<input name="category" value="<?php echo h($item['category']); ?>" required></label>
        <label>Price<input type="number" step="0.01" min="0" name="price" value="<?php echo h($item['price']); ?>" required></label>
        <label>Badge<input name="badge" value="<?php echo h($item['badge']); ?>" placeholder="Best Seller"></label>
        <label>Font Awesome icon<input name="icon" value="<?php echo h($item['icon']); ?>"></label>
        <label>Sort order<input type="number" name="sort_order" value="<?php echo h($item['sort_order']); ?>"></label>
        <label class="form-row full">Description<textarea name="description"><?php echo h($item['description']); ?></textarea></label>
    </div>
    <label style="margin-top:14px;"><input type="checkbox" name="is_available" checked> Show on customer menu</label>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/menu.php">Cancel</a>
        <button class="btn primary" type="submit">Save item</button>
    </div>
</form>
<?php admin_footer(); ?>
