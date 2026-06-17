<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = $_GET['id'] ?? $_POST['id'] ?? '';
$stmt = $conn->prepare("SELECT id, full_name FROM sweetbean_users WHERE id = ? AND role = 'staff'");
$stmt->execute([$id]);
$staff = $stmt->fetch();
if (!$staff) {
    admin_flash('Staff account not found.', 'error');
    admin_redirect('staff.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM sweetbean_users WHERE id = ? AND role = 'staff'");
    $stmt->execute([$id]);
    admin_flash('Staff account deleted.');
    admin_redirect('staff.php');
}

admin_header('Delete Staff', 'Remove a staff login.');
?>
<form class="form-card" method="post">
    <input type="hidden" name="id" value="<?php echo h($staff['id']); ?>">
    <p>Delete staff account for <strong><?php echo h($staff['full_name']); ?></strong>?</p>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/staff.php">Cancel</a>
        <button class="btn danger" type="submit">Delete staff</button>
    </div>
</form>
<?php admin_footer(); ?>
