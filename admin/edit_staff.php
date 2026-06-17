<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/_layout.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM sweetbean_users WHERE id = ? AND role = 'staff'");
$stmt->execute([$id]);
$staff = $stmt->fetch();
if (!$staff) {
    admin_flash('Staff account not found.', 'error');
    admin_redirect('staff.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff = array_merge($staff, [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'birth_date' => $_POST['birth_date'] ?: null,
    ]);
    $password = $_POST['password'] ?? '';

    if ($staff['full_name'] === '' || !filter_var($staff['email'], FILTER_VALIDATE_EMAIL) || $staff['phone'] === '') {
        $error = 'Name, valid email, and phone are required.';
    } else {
        try {
            if ($password !== '') {
                $stmt = $conn->prepare("
                    UPDATE sweetbean_users
                    SET full_name = :full_name, email = :email, phone = :phone, birth_date = :birth_date, password = :password
                    WHERE id = :id AND role = 'staff'
                ");
                $stmt->execute([
                    'id' => $id,
                    'full_name' => $staff['full_name'],
                    'email' => $staff['email'],
                    'phone' => $staff['phone'],
                    'birth_date' => $staff['birth_date'],
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE sweetbean_users
                    SET full_name = :full_name, email = :email, phone = :phone, birth_date = :birth_date
                    WHERE id = :id AND role = 'staff'
                ");
                $stmt->execute([
                    'id' => $id,
                    'full_name' => $staff['full_name'],
                    'email' => $staff['email'],
                    'phone' => $staff['phone'],
                    'birth_date' => $staff['birth_date'],
                ]);
            }
            admin_flash('Staff account updated.');
            admin_redirect('staff.php');
        } catch (Throwable $e) {
            $error = 'Could not update staff. Email or phone may already be used.';
        }
    }
}

admin_header('Edit Staff', 'Update staff contact details or reset their password.');
if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<form class="form-card" method="post">
    <div class="form-grid">
        <label>Full name<input name="full_name" value="<?php echo h($staff['full_name']); ?>" required></label>
        <label>Email<input type="email" name="email" value="<?php echo h($staff['email']); ?>" required></label>
        <label>Phone<input name="phone" value="<?php echo h($staff['phone']); ?>" required></label>
        <label>Birth date<input type="date" name="birth_date" value="<?php echo h($staff['birth_date']); ?>"></label>
        <label class="form-row full">New password <span class="muted">Leave blank to keep current password.</span><input type="password" name="password"></label>
    </div>
    <div class="form-actions">
        <a class="btn" href="<?php echo BASE_URL; ?>admin/staff.php">Cancel</a>
        <button class="btn primary" type="submit">Update staff</button>
    </div>
</form>
<?php admin_footer(); ?>
