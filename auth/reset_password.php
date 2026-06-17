<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

$message_status = "";
$message_type = "";
$show_form = false;

// Ambil token dan email baik dari GET (klik link) maupun POST (saat submit form)
$token = $_REQUEST['token'] ?? '';
$email = strtolower(trim($_REQUEST['email'] ?? ''));
$user = null;

if (empty($token) || empty($email)) {
    $message_status = "Invalid or expired reset link.";
    $message_type = "error";
} else {
    // DISESUAIKAN: Menggunakan MySQLi standar (bukan PDO)
    // Silakan ganti 'user' menjadi 'sweetbean_users' jika itu nama tabel aslimu
    $stmt = $conn->prepare("
        SELECT id
        FROM user
        WHERE email = ?
          AND reset_token = ?
          AND reset_token_expires_at > NOW()
        LIMIT 1
    ");
    
    $hashedToken = hash('sha256', $token);
    $stmt->bind_param("ss", $email, $hashedToken);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $show_form = true;
    } else {
        $message_status = "Invalid or expired reset link.";
        $message_type = "error";
    }
}

// Proses ganti password saat tombol ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$user) {
        $message_status = "Invalid or expired reset link.";
        $message_type = "error";
        $show_form = false;
    } elseif (strlen($new_password) < 6) {
        $message_status = "Password must be at least 6 characters long.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message_status = "Passwords do not match. Please try again.";
        $message_type = "error";
    } else {
        // DISESUAIKAN: Update menggunakan MySQLi
        $stmt = $conn->prepare("
            UPDATE user
            SET password = ?,
                reset_token = NULL,
                reset_token_expires_at = NULL
            WHERE id = ?
        ");
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($stmt->execute()) {
            $message_status = "Success! Your password has been updated. You can now log in.";
            $message_type = "success";
            $show_form = false;
        } else {
            $message_status = "Something went wrong. Please try again later.";
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Cafe - Reset Password</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f6f2ec; font-family: Arial, sans-serif; color: #2b1e16; padding: 24px; }
        .card { width: 100%; max-width: 430px; background: #fff; border: 1px solid #eadfd5; border-radius: 18px; padding: 32px; box-shadow: 0 8px 24px rgba(90, 56, 37, .08); }
        h1 { margin: 0 0 8px; color: #8B5A2B; font-size: 28px; }
        p { color: #735f54; }
        label { display: block; margin: 20px 0 8px; font-weight: 700; color: #8B5A2B; }
        input { width: 100%; padding: 14px; border: 1px solid #dacdc2; border-radius: 12px; font-size: 15px; }
        button { width: 100%; margin-top: 18px; padding: 14px; border: 0; border-radius: 12px; background: #8B5A2B; color: #fff; font-weight: 700; cursor: pointer; }
        .msg { padding: 12px; border-radius: 10px; margin: 16px 0; font-size: 14px; }
        .error { background: #f8d7da; color: #842029; }
        .success { background: #d1e7dd; color: #0f5132; }
        a { display: inline-block; margin-top: 18px; color: #8B5A2B; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Create New Password</h1>
        <p>Please enter your new password below.</p>

        <?php if ($message_status !== ""): ?>
            <div class="msg <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message_status); ?>
            </div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <form method="POST" action="reset_password.php?token=<?php echo urlencode($token); ?>&email=<?php echo urlencode($email); ?>">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Minimum 6 characters" required>

                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat your password" required>

                <button type="submit">Update Password</button>
            </form>
        <?php endif; ?>

        <a href="login.php">Go to Login Page</a>
    </main>
</body>
</html>