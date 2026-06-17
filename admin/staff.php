<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$staff = $conn->query("
    SELECT id, full_name, email, phone, birth_date, created_at
    FROM sweetbean_users
    WHERE role = 'staff'
    ORDER BY full_name
")->fetchAll();

admin_header('Staff', 'Manage staff accounts that can support cafe operations.', '<a class="btn primary" href="' . BASE_URL . 'admin/add_staff.php"><i class="fa-solid fa-plus"></i> Add staff</a>');
?>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Birth date</th><th>Joined</th><th class="right">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($staff as $member): ?>
                    <tr>
                        <td><?php echo h($member['full_name']); ?></td>
                        <td><?php echo h($member['email']); ?></td>
                        <td><?php echo h($member['phone']); ?></td>
                        <td><?php echo h($member['birth_date'] ?: ''); ?></td>
                        <td><?php echo h(date('M d, Y', strtotime($member['created_at']))); ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="<?php echo BASE_URL; ?>admin/edit_staff.php?id=<?php echo urlencode($member['id']); ?>"><i class="fa-solid fa-pen"></i></a>
                                <a class="btn danger" href="<?php echo BASE_URL; ?>admin/delete_staff.php?id=<?php echo urlencode($member['id']); ?>"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$staff): ?><tr><td colspan="6" class="empty-state">No staff accounts yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
