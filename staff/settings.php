<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

if (isset($_POST['save_changes'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $update = mysqli_query($conn, "
        UPDATE users
        SET name='$name',
            phone='$phone'
        WHERE user_id='$staff_id'
    ");

    if ($update) {
        $_SESSION['user_name'] = $name;
        $message = "Account information updated successfully.";
        $message_type = "success";
    } else {
        $message = "Failed to update account information.";
        $message_type = "error";
    }
}

$staff = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE user_id='$staff_id'
    LIMIT 1
"));

$staffName = $_SESSION['user_name'] ?? ($staff['name'] ?? 'Staff Member');
$avatar = strtoupper(substr($staffName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Settings | Sweet Bean Coffee</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f6efe7;
    color: #2a211b;
    font-family: Arial, sans-serif;
}

.layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    min-height: 100vh;
}

.sidebar {
    background: linear-gradient(180deg, #3b2418, #5a3825);
    color: white;
    padding: 28px 20px;
}

.logo-box {
    text-align: center;
    margin-bottom: 36px;
}

.logo-box img {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    object-fit: cover;
    background: white;
    margin-bottom: 10px;
}

.logo-box h2 {
    margin: 0;
    font-size: 20px;
}

.nav-menu {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.nav-menu a {
    text-decoration: none;
    color: white;
    padding: 15px 18px;
    border-radius: 16px;
    font-weight: 800;
    background: rgba(255,255,255,.08);
}

.nav-menu a.active,
.nav-menu a:hover {
    background: #fff8ef;
    color: #5a3825;
}

.logout {
    margin-top: 40px;
    background: rgba(255,255,255,.15)!important;
}

.main {
    padding: 34px;
}

.topbar {
    background: white;
    padding: 28px;
    border-radius: 28px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
}

.topbar h1 {
    margin: 0;
    color: #5a3825;
    font-size: 34px;
}

.topbar p {
    color: #806e62;
    margin: 8px 0 0;
}

.staff-pill {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff8ef;
    padding: 12px 16px;
    border-radius: 999px;
}

.avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
}

.staff-pill strong {
    color: #5a3825;
}

.staff-pill span {
    display: block;
    color: #806e62;
    font-size: 13px;
    margin-top: 2px;
}

.msg {
    padding: 14px 18px;
    border-radius: 16px;
    margin-bottom: 22px;
    font-weight: 800;
}

.msg.success {
    background: #d1fae5;
    color: #047857;
}

.msg.error {
    background: #fee2e2;
    color: #b91c1c;
}

.account-card {
    background: white;
    border-radius: 28px;
    padding: 34px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
    width: 100%;
    min-height: calc(100vh - 210px);
}

.account-card h2 {
    margin: 0 0 8px;
    color: #5a3825;
    font-size: 30px;
}

.card-subtitle {
    color: #806e62;
    margin-bottom: 32px;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 22px;
    background: #fff8ef;
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 28px;
}

.profile-avatar-large {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    font-weight: 900;
}

.profile-name {
    font-size: 26px;
    font-weight: 900;
    color: #5a3825;
}

.profile-role {
    color: #806e62;
    margin-top: 6px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.form-row {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 900;
    color: #806e62;
}

input {
    width: 100%;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 16px;
    background: white;
    font-size: 15px;
}

input[readonly] {
    background: #f7efe6;
    color: #806e62;
}

.info-note {
    background: #fff8ef;
    border-radius: 20px;
    padding: 20px;
    color: #806e62;
    line-height: 1.6;
    margin-top: 10px;
}

.btn-wrap {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 0 26px;
    border: none;
    border-radius: 16px;
    background: #5a3825;
    color: white;
    font-weight: 900;
    cursor: pointer;
    font-size: 15px;
}

@media(max-width: 1100px) {
    .layout {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 800px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .profile-header {
        flex-direction: column;
        text-align: center;
    }
}

@media(max-width: 700px) {
    .main {
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .account-card {
        padding: 24px;
    }
}
</style>
</head>

<body>

<div class="layout">

    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/LOGO.jpg" alt="Sweet Bean Logo">
            <h2>Sweet Bean Staff</h2>
        </div>

        <nav class="nav-menu">
            <a href="dashboard.php">Main Menu</a>
            <a href="manage_orders.php">Manage Order</a>
            <a href="settings.php" class="active">Settings</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Staff Settings</h1>
                <p>Manage your staff account information.</p>
            </div>

            <div class="staff-pill">
                <div class="avatar"><?php echo htmlspecialchars($avatar); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($staffName); ?></strong>
                    <span>Staff Member</span>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <section class="account-card">
            <h2>Account Information</h2>
            <div class="card-subtitle">
                Update your staff profile details.
            </div>

            <div class="profile-header">
                <div class="profile-avatar-large">
                    <?php echo htmlspecialchars($avatar); ?>
                </div>

                <div>
                    <div class="profile-name">
                        <?php echo htmlspecialchars($staff['name'] ?? 'Staff Member'); ?>
                    </div>
                    <div class="profile-role">
                        Sweet Bean Coffee Staff Account
                    </div>
                </div>
            </div>

            <form method="POST">

                <div class="form-grid">
                    <div>
                        <div class="form-row">
                            <label>Staff Name</label>
                            <input 
                                type="text" 
                                name="name" 
                                value="<?php echo htmlspecialchars($staff['name'] ?? ''); ?>" 
                                required
                            >
                        </div>

                        <div class="form-row">
                            <label>Email</label>
                            <input 
                                type="email" 
                                value="<?php echo htmlspecialchars($staff['email'] ?? ''); ?>" 
                                readonly
                            >
                        </div>
                    </div>

                    <div>
                        <div class="form-row">
                            <label>Phone Number</label>
                            <input 
                                type="text" 
                                name="phone" 
                                value="<?php echo htmlspecialchars($staff['phone'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-row">
                            <label>Role</label>
                            <input 
                                type="text" 
                                value="<?php echo htmlspecialchars($staff['role'] ?? 'staff'); ?>" 
                                readonly
                            >
                        </div>
                    </div>
                </div>

                <div class="info-note">
                    Staff accounts are used only for managing incoming orders, updating order status, and checking daily staff dashboard information.
                </div>

                <div class="btn-wrap">
                    <button type="submit" name="save_changes" class="btn">
                        Save Information
                    </button>
                </div>

            </form>
        </section>

    </main>

</div>

</body>
</html>