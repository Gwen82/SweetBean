<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$items = $conn->query("
    SELECT id, name, category, price, badge, icon, is_available, sort_order
    FROM menu_items
    ORDER BY sort_order, name
")->fetchAll();

admin_header('Menu', 'Create and tune the items customers see on the storefront.', '<a class="btn primary" href="' . BASE_URL . 'admin/add_menu.php"><i class="fa-solid fa-plus"></i> Add item</a>');
?>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Badge</th><th>Status</th><th>Sort</th><th class="right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><i class="fa-solid <?php echo h($item['icon']); ?>"></i> <?php echo h($item['name']); ?></td>
                        <td><?php echo h($item['category']); ?></td>
                        <td><?php echo money($item['price']); ?></td>
                        <td><?php echo h($item['badge']); ?></td>
                        <td><span class="badge <?php echo $item['is_available'] ? 'green' : 'red'; ?>"><?php echo $item['is_available'] ? 'Available' : 'Hidden'; ?></span></td>
                        <td><?php echo h($item['sort_order']); ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="<?php echo BASE_URL; ?>admin/edit_menu.php?id=<?php echo urlencode($item['id']); ?>"><i class="fa-solid fa-pen"></i></a>
                                <a class="btn danger" href="<?php echo BASE_URL; ?>admin/delete_menu.php?id=<?php echo urlencode($item['id']); ?>"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?><tr><td colspan="7" class="empty-state">No menu items yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
