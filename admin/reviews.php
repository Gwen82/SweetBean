<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$reviews = $conn->query("
    SELECT r.id, r.rating, r.comment, r.is_visible, r.created_at, u.full_name, r.order_id
    FROM reviews r
    LEFT JOIN sweetbean_users u ON u.id = r.user_id
    ORDER BY r.created_at DESC
")->fetchAll();

admin_header('Reviews', 'Moderate customer feedback and keep only helpful reviews visible.');
?>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Rating</th><th>Review</th><th>Order</th><th>Status</th><th class="right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?php echo h($review['full_name'] ?? 'Customer'); ?></td>
                        <td><?php echo str_repeat('<i class="fa-solid fa-star" style="color:#c79652"></i>', (int) $review['rating']); ?></td>
                        <td><?php echo h($review['comment']); ?><br><span class="muted"><?php echo h(date('M d, Y', strtotime($review['created_at']))); ?></span></td>
                        <td><?php echo $review['order_id'] ? '#' . h($review['order_id']) : ''; ?></td>
                        <td><span class="badge <?php echo $review['is_visible'] ? 'green' : 'red'; ?>"><?php echo $review['is_visible'] ? 'Visible' : 'Hidden'; ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn danger" href="<?php echo BASE_URL; ?>admin/delete_review.php?id=<?php echo h($review['id']); ?>"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$reviews): ?><tr><td colspan="6" class="empty-state">No reviews yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
