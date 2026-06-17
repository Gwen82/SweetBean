<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login_input = trim($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare("
            SELECT id, full_name, email, phone, password, role
            FROM sweetbean_users
            WHERE email = :login_input OR phone = :login_input
            LIMIT 1
        ");
        $stmt->execute([':login_input' => $login_input]);
        $logged_in_user = $stmt->fetch();

        if ($logged_in_user && password_verify($password, $logged_in_user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $logged_in_user['id'];
            $_SESSION['user_name'] = $logged_in_user['full_name'];
            $_SESSION['user_role'] = $logged_in_user['role'];

            if ($logged_in_user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../customer/menu.php");
            }
            exit();

        } else {

            $message = "Invalid Email/Phone Number or Password.";
            $message_type = "error";
        }
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
            --primary-color: #5a3825; /* Espresso Brown */
            --primary-hover: #442a1c;
            --accent-gold: #ddb892;   /* Creamy Latte Gold */
            --text-main: #2b1e16;
            --text-muted: #8c766b;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            /* Rich Cafe Gradient Background with abstract subtle steam/coffee ambient blobs */
            background: linear-gradient(135deg, #f5efe6 0%, #e6dcd0 50%, #d7c4b7 100%);
            position: relative;
            color: var(--text-main);
            margin: 0; 
            padding: 0; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Decorative background elements for depth */
        body::before, body::after {
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

        /* Container Layout */
        .wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 60px 20px;
            z-index: 1;
        }

        /* Frosted Glassmorphism Login Card Box */
        .login-box { 
            background: rgba(255, 255, 255, 0.75); 
            backdrop-filter: blur(16px) saturate(120%);
            -webkit-backdrop-filter: blur(16px) saturate(120%);
            padding: 40px; 
            border-radius: 32px; 
            box-shadow: 0 20px 50px rgba(90, 56, 37, 0.12); 
            width: 100%;
            max-width: 420px; 
            text-align: center; 
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-sizing: border-box;
        }

        /* Lingkaran Luar Container Logo di Form */
        .cafe-logo-container {
            width: 70px;
            height: 70px;
            background: var(--primary-color);
            margin: 0 auto 16px auto;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 8px 20px rgba(90, 56, 37, 0.2);
            overflow: hidden; /* Memastikan gambar terpotong bulat rapi */
        }

        /* Style Gambar Logo di Form Login */
        .cafe-logo-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        h2 { 
            font-family: 'Playfair Display', serif;
            color: var(--primary-color); 
            font-size: 2.2rem;
            margin-top: 0;
            margin-bottom: 6px; 
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 32px;
            font-weight: 400;
        }

        /* Form Input Customizations */
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: #fff;
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
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        /* Buttons & Feedback States */
        .btn-login { 
            width: 100%; 
            padding: 15px; 
            background: var(--primary-color); 
            color: #faf9f6; 
            border: none; 
            border-radius: 14px; 
            font-family: 'Poppins', sans-serif;
            font-size: 1rem; 
            font-weight: 500;
            cursor: pointer; 
            box-shadow: 0 4px 12px rgba(90, 56, 37, 0.15);
            transition: all 0.3s ease;
        }

        .btn-login:hover { 
            background: var(--primary-hover); 
            box-shadow: 0 6px 20px rgba(90, 56, 37, 0.25);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(1px);
            box-shadow: 0 3px 8px rgba(90, 56, 37, 0.15);
        }

        .msg { 
            margin-bottom: 24px; 
            font-size: 0.9rem;
            padding: 12px 16px; 
            border-radius: 14px; 
            text-align: left;
            animation: fadeIn 0.4s ease;
        }
        
        .msg.error { color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; }
        .msg.success { color: #0f5132; background-color: #d1e7dd; border: 1px solid #badbcc; }

        .register-link { 
            margin-top: 26px; 
            display: inline-block; 
            font-size: 0.9rem; 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link:hover {
            color: var(--text-muted);
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../navbar.php'; ?>

    <div class="wrapper">
        <div class="login-box">
            
            <div class="cafe-logo-container">
                <img src="../assets/LOGO.jpg" alt="Logo Cafe" />
            </div>

            <h2>Welcome Back</h2>
            <div class="subtitle">Log in to manage your orders</div>
            
            <?php if(!empty($message)): ?>
                <div class="msg <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email or Phone Number</label>
                    <input type="text" name="login_input" placeholder="Enter your email or phone" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>

                <div class="form-actions">
                    <a href="forget_password.php" class="forgot-link">Forget password? Reset Password</a>
                </div>
                
                <button type="submit" class="btn-login">Log In</button>
            </form>

            <a href="register.php" class="register-link">Don't have any account? Create Account</a>
        </div>
    </div>

</body>
</html>
