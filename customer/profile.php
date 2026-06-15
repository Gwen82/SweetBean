<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Sweet Bean Coffee</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #fff8ef; color: #2a211b; font-family: "Poppins", Arial, sans-serif; }
        .profile-page { padding: 54px 22px 80px; }
        .profile-card { width: min(720px, 100%); margin: 0 auto; padding: 30px; border: 1px solid rgba(90, 56, 37, 0.14); border-radius: 8px; background: #fff; box-shadow: 0 14px 35px rgba(70, 42, 25, 0.06); }
        .eyebrow { margin: 0 0 8px; color: #6b8068; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
        h1 { margin: 0 0 20px; color: #2d1d16; font-family: "Playfair Display", Georgia, serif; font-size: clamp(2rem, 5vw, 3.6rem); }
        .detail-row { display: flex; justify-content: space-between; gap: 18px; padding: 16px 0; border-top: 1px solid rgba(90, 56, 37, 0.12); }
        .detail-row span { color: #806e62; }
        .detail-row strong { text-align: right; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 26px; }
        .actions a { display: inline-flex; align-items: center; min-height: 42px; padding: 0 16px; border-radius: 8px; background: #5a3825; color: #fff; font-weight: 700; text-decoration: none; }
        .actions a.secondary { background: #f7efe6; color: #5a3825; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../navbar.php'; ?>
    <main class="profile-page">
        <section class="profile-card">
            <p class="eyebrow">Signed in</p>
            <h1>Your Profile</h1>
            <div class="detail-row">
                <span>Name</span>
                <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></strong>
            </div>
            <div class="detail-row">
                <span>Role</span>
                <strong><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'customer'); ?></strong>
            </div>
            <div class="actions">
                <a href="<?php echo BASE_URL; ?>customer/cart.php">View Cart</a>
                <a class="secondary" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a>
            </div>
        </section>
    </main>
</body>
</html>
