<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $conn->prepare("SELECT id, comment FROM reviews WHERE id = ?");
$stmt->execute([$id]);
$review = $stmt->fetch();
if (!$review) {
    admin_flash('Review not found.', 'error');
    admin_redirect('reviews.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    admin_flash('Review deleted.');
    admin_redirect('reviews.php');
}

admin_header('Delete Review', 'Remove a customer review.');
?>
<form class="form-card" method="post">
    <input type="hidden" name="id" value="<?php echo h($review['id']); ?>">
    <p>Delete this review?</p>
    <p class="muted"><?php echo h($review['comment']); ?></p>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/reviews.php">Cancel</a>
        <button class="btn danger" type="submit">Delete review</button>
    </div>
</form>
<?php admin_footer(); ?>
