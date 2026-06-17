<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    admin_flash('Menu item not found.', 'error');
    admin_redirect('menu.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = array_merge($item, [
        'name' => trim($_POST['name'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'price' => trim($_POST['price'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'badge' => trim($_POST['badge'] ?? ''),
        'icon' => trim($_POST['icon'] ?? 'fa-mug-hot'),
        'is_available' => isset($_POST['is_available']),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
    ]);

    if ($item['name'] === '' || $item['category'] === '' || !is_numeric($item['price'])) {
        $error = 'Name, category, and a valid price are required.';
    } else {
        $stmt = $conn->prepare("
            UPDATE menu_items
            SET name = :name, category = :category, price = :price, description = :description,
                badge = :badge, icon = :icon, is_available = :is_available, sort_order = :sort_order
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'name' => $item['name'],
            'category' => $item['category'],
            'price' => $item['price'],
            'description' => $item['description'],
            'badge' => $item['badge'],
            'icon' => $item['icon'],
            'is_available' => $item['is_available'],
            'sort_order' => $item['sort_order'],
        ]);
        admin_flash('Menu item updated.');
        admin_redirect('menu.php');
    }
}

admin_header('Edit Menu Item', 'Changes sync to the customer menu immediately.');
if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<form class="form-card" method="post">
    <div class="form-grid">
        <label>Item ID<input value="<?php echo h($item['id']); ?>" disabled></label>
        <label>Name<input name="name" value="<?php echo h($item['name']); ?>" required></label>
        <label>Category<input name="category" value="<?php echo h($item['category']); ?>" required></label>
        <label>Price<input type="number" step="0.01" min="0" name="price" value="<?php echo h($item['price']); ?>" required></label>
        <label>Badge<input name="badge" value="<?php echo h($item['badge']); ?>"></label>
        <label>Font Awesome icon<input name="icon" value="<?php echo h($item['icon']); ?>"></label>
        <label>Sort order<input type="number" name="sort_order" value="<?php echo h($item['sort_order']); ?>"></label>
        <label class="form-row full">Description<textarea name="description"><?php echo h($item['description']); ?></textarea></label>
    </div>
    <label style="margin-top:14px;"><input type="checkbox" name="is_available" <?php echo $item['is_available'] ? 'checked' : ''; ?>> Show on customer menu</label>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/menu.php">Cancel</a>
        <button class="btn primary" type="submit">Update item</button>
    </div>
</form>
<?php admin_footer(); ?>
