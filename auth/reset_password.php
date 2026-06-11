<?php
session_start();

/* =================================================
DATABASE TEMPLATE (BELUM DIAKTIFKAN)
=================================================
include '../db.php'; 
=================================================
*/

$message_status = "";
$message_type = "";
$show_form = false;

// 1. AMBIL DATA DARI URL (GET)
// Saat user klik link dari email, URL pasti membawa ?token=... dan &email=...
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($token) || empty($email)) {
    $message_status = "Invalid or expired reset link.";
    $message_type = "error";
} else {
    // Karena ini masih DUMMY, kita anggap tokennya selalu valid
    // Nanti kalau pakai database, kamu harus cek apakah token ini cocok dengan yang ada di tabel user
    $show_form = true;
}

// 2. PROSES SAAT USER SUBMIT PASSWORD BARU (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $message_status = "Password must be at least 6 characters long.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message_status = "Passwords do not match. Please try again.";
        $message_type = "error";
    } else {
        
        // ===== SIMULASI BERHASIL =====
        $message_status = "Success! Your password has been updated. You can now log in.";
        $message_type = "success";
        $show_form = false; // Sembunyikan form karena sudah sukses ganti password

        /*
        =================================================
        DATABASE TEMPLATE UPDATE PASSWORD (AKTIFKAN NANTI)
        =================================================
        // Amankan password baru dengan Bcrypt hash
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Update password di DB dan hapus tokennya agar tidak bisa dipakai lagi
        $stmt = mysqli_prepare($conn, "UPDATE user SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
        mysqli_stmt_execute($stmt);
        mysqli_close($conn);
        =================================================
        */
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Cafe - Reset Password</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f6f2ec;
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px 20px 20px;
        }

        /* ===== NAVBAR ===== */
        .navbar { 
            width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; 
            background: rgba(255, 255, 255, 0.9); border-bottom: 1px solid #ddd; position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .logo-nav { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .brand-name { font-weight: bold; font-size: 1.2rem; color: #8B5A2B; }
        .nav-links a { margin-left: 20px; text-decoration: none; color: #333; font-size: 14px; font-weight: bold; }
        .nav-links a:hover { color: #8B5A2B; text-decoration: underline; }

        /* ===== CARD ===== */
        .wrapper { width: 100%; max-width: 450px; margin-top: 40px; }
        .card { background: white; border-radius: 18px; padding: 35px; border: 1px solid #eee; box-shadow: 0 8px 25px rgba(0,0,0,.06); text-align: center; }
        .logo { display: flex; flex-direction: column; align-items: center; margin-bottom: 25px; }
        .logo-placeholder { width: 65px; height: 65px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }
        .logo h1 { color: #8B5A2B; font-size: 24px; margin-bottom: 6px; }
        .logo p { color: #777; font-size: 14px; }

        /* ===== FORM ===== */
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; color: #8B5A2B; font-weight: bold; margin-bottom: 8px; font-size: 14px; }
        input[type=password] {
            width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; background: #fafafa; transition: .2s; font-size: 15px;
        }
        input[type=password]:focus { outline: none; border-color: #8B5A2B; background: white; }
        button {
            width: 100%; padding: 15px; border: none; border-radius: 12px; background: #8B5A2B; color: white; font-size: 16px; font-weight: bold; cursor: pointer; transition: .2s; margin-bottom: 20px;
        }
        button:hover { background: #704723; transform: translateY(-2px); }

        /* ===== MSG & LINK ===== */
        .msg { padding: 12px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; text-align: left; }
        .msg.error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .msg.success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .back-link { text-decoration: none; color: #8B5A2B; font-weight: bold; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="header-left">
        <a href="home.php">
            <img src="../assets/LOGO.jpg" alt="Sweet Bean Coffee Logo" class="logo-nav" />
        </a>
        <span class="brand-name">Sweet Bean Coffee</span>
    </div>
    <div class="nav-links">
        <a href="#">Contact</a>
        <a href="#">About us</a>
        <a href="index.php">Home</a>
    </div>
</div>

<div class="wrapper">
    <div class="card">
        <div class="logo">
            <img src="../assets/LOGO.jpg" alt="Sweet Bean Coffee Logo" class="logo-placeholder" />
            <h1>Create New Password</h1>
            <p>Please enter your new password below</p>
        </div>

        <?php if ($message_status !== ""): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo $message_status; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <form method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Minimum 6 characters" required>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat your password" required>
                </div>

                <button type="submit">Update Password</button>
            </form>
        <?php endif; ?>

        <a href="login.php" class="back-link">Go to Login Page</a>
    </div>
</div>

</body>
</html>