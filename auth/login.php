<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../navbar.php";

$message = "";
$message_type = "";

if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $message = "Registration successful. Please login.";
    $message_type = "success";
}

if (isset($_POST['login'])) {

    $login_input = mysqli_real_escape_string($conn, trim($_POST['login_input']));
    $password = $_POST['password'];

    $sql = mysqli_query($conn, "
        SELECT *
        FROM users
        WHERE email = '$login_input'
        OR phone = '$login_input'
        LIMIT 1
    ");

    if ($sql && mysqli_num_rows($sql) == 1) {

        $user = mysqli_fetch_assoc($sql);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = strtolower($user['role']);

            if ($_SESSION['user_role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            }

            if ($_SESSION['user_role'] === 'staff') {
                header("Location: ../staff/dashboard.php");
                exit();
            }

            header("Location: ../customer/menu.php");
            exit();

        } else {
            $message = "Wrong password.";
            $message_type = "error";
        }

    } else {
        $message = "User not found.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sweet Bean Cafe - Log In</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-color: #5a3825;
    --primary-hover: #442a1c;
    --text-main: #2b1e16;
    --text-muted: #8c766b;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f5efe6 0%, #e6dcd0 50%, #d7c4b7 100%);
    color: var(--text-main);
    margin: 0;
    min-height: 100vh;
    overflow-x: hidden;
}

body::before,
body::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    z-index: 0;
    opacity: 0.5;
}

body::before {
    width: 300px;
    height: 300px;
    background: #eddcd2;
    top: -50px;
    left: -50px;
}

body::after {
    width: 400px;
    height: 400px;
    background: #cb997e;
    bottom: -100px;
    right: -50px;
}

.wrapper {
    min-height: calc(100vh - 80px);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 80px 20px 60px;
    position: relative;
    z-index: 1;
}

.login-box {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(16px);
    padding: 40px;
    border-radius: 32px;
    box-shadow: 0 20px 50px rgba(90, 56, 37, 0.12);
    width: 100%;
    max-width: 420px;
    text-align: center;
    box-sizing: border-box;
}

.cafe-logo-container {
    width: 70px;
    height: 70px;
    margin: 0 auto 16px;
    border-radius: 50%;
    overflow: hidden;
}

.cafe-logo-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

h2 {
    font-family: 'Playfair Display', serif;
    color: var(--primary-color);
    font-size: 2.2rem;
    margin: 0 0 6px;
}

.subtitle {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 32px;
}

.form-group {
    margin-bottom: 22px;
    text-align: left;
}

.form-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.form-group input {
    width: 100%;
    padding: 14px 18px;
    border: 1.5px solid rgba(140, 118, 107, 0.25);
    background-color: rgba(255, 255, 255, 0.6);
    color: var(--text-main);
    border-radius: 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    background-color: white;
    box-shadow: 0 0 0 4px rgba(90, 56, 37, 0.12);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: -12px;
    margin-bottom: 24px;
}

.forgot-link {
    font-size: 0.85rem;
    color: var(--text-muted);
    text-decoration: none;
}

.forgot-link:hover {
    color: var(--primary-color);
    text-decoration: underline;
}

.btn-login {
    width: 100%;
    padding: 15px;
    background: var(--primary-color);
    color: #faf9f6;
    border: none;
    border-radius: 14px;
    font-size: 1rem;
    cursor: pointer;
}

.btn-login:hover {
    background: var(--primary-hover);
}

.msg {
    margin-bottom: 24px;
    font-size: 0.9rem;
    padding: 12px 16px;
    border-radius: 14px;
    text-align: left;
}

.msg.error {
    color: #842029;
    background-color: #f8d7da;
}

.msg.success {
    color: #0f5132;
    background-color: #d1e7dd;
}

.register-link {
    margin-top: 26px;
    display: inline-block;
    font-size: 0.9rem;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="wrapper">
    <div class="login-box">

        <div class="cafe-logo-container">
            <img src="../assets/LOGO.jpg" alt="Logo Cafe">
        </div>

        <h2>Welcome Back</h2>
        <div class="subtitle">Log in to continue to Sweet Bean Coffee</div>

        <?php if (!empty($message)): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">

            <div class="form-group">
                <label>Email or Phone Number</label>
                <input type="text" name="login_input" placeholder="Enter your email or phone" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-actions">
                <a href="forget_password.php" class="forgot-link">
                    Forget password? Reset Password
                </a>
            </div>

            <button type="submit" name="login" class="btn-login">
                Log In
            </button>

        </form>

        <a href="register.php" class="register-link">
            Don't have an account? Create Account
        </a>

    </div>
</div>

</body>
</html>