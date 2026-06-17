<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$staff = ['full_name' => '', 'email' => '', 'phone' => '', 'birth_date' => ''];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'birth_date' => $_POST['birth_date'] ?: null,
    ];
    $password = $_POST['password'] ?? '';

    if ($staff['full_name'] === '' || !filter_var($staff['email'], FILTER_VALIDATE_EMAIL) || $staff['phone'] === '' || strlen($password) < 6) {
        $error = 'Name, valid email, phone, and a password of at least 6 characters are required.';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO sweetbean_users (full_name, email, phone, birth_date, password, role)
                VALUES (:full_name, :email, :phone, :birth_date, :password, 'staff')
            ");
            $stmt->execute($staff + ['password' => password_hash($password, PASSWORD_DEFAULT)]);
            admin_flash('Staff account added.');
            admin_redirect('staff.php');
        } catch (Throwable $e) {
            $error = 'Could not add staff. Email or phone may already be used.';
        }
    }
}

admin_header('Add Staff', 'Create a login for a cafe team member.');
if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<form class="form-card" method="post">
    <div class="form-grid">
        <label>Full name<input name="full_name" value="<?php echo h($staff['full_name']); ?>" required></label>
        <label>Email<input type="email" name="email" value="<?php echo h($staff['email']); ?>" required></label>
        <label>Phone<input name="phone" value="<?php echo h($staff['phone']); ?>" required></label>
        <label>Birth date<input type="date" name="birth_date" value="<?php echo h($staff['birth_date']); ?>"></label>
        <label class="form-row full">Temporary password<input type="password" name="password" required></label>
    </div>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/staff.php">Cancel</a>
        <button class="btn primary" type="submit">Save staff</button>
    </div>
</form>
<?php admin_footer(); ?>
