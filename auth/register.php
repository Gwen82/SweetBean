<?php
session_start();
require_once "../config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $birthday = $_POST['birth_date'];
    $password = $_POST['password'];

    if (
        empty($name) ||
        empty($email) ||
        empty($phone) ||
        empty($birthday) ||
        empty($password)
    ) {

        $message = "All fields are required.";
        $message_type = "error";

    } else {

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM user WHERE email = ? OR phone = ?"
        );

        mysqli_stmt_bind_param(
            $check,
            "ss",
            $email,
            $phone
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {

            $message = "Email or phone already registered.";
            $message_type = "error";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO user
                (name,email,phone,birthday,password,role)
                VALUES (?,?,?,?,?,'customer')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $name,
                $email,
                $phone,
                $birthday,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Account created successfully!";
                $message_type = "success";

                header("Refresh:2; url=login.php");

            } else {

                $message = "Registration failed.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Cafe - Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #fcfbf7;
            --primary-color: #5a3825; /* Espresso Brown */
            --primary-hover: #442a1c;
            --accent-color: #ddb892;  /* Latte Gold */
            --text-main: #2b1e16;
            --text-muted: #7f6a5f;
            --card-bg: #ffffff;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
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
            background: #cb997e;
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

        /* Container & Card Layout */
        .wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 45px 20px 60px 20px; /* offset for fixed navbar */
            z-index: 1;
        }

        /* Frosted Glassmorphism Box */
        .register-box { 
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
            overflow: hidden; 
        }

        /* Style Gambar Logo di Form */
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

        /* Input Customizations */
        .form-group { 
            margin-bottom: 22px; 
            text-align: left; 
            position: relative;
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

        .form-group input[type="date"] {
            color: var(--text-main);
        }

        /* Buttons & Dynamic Messages */
        .btn-create { 
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

        .btn-create:hover { 
            background: var(--primary-hover); 
            box-shadow: 0 6px 20px rgba(90, 56, 37, 0.25);
            transform: translateY(-1px);
        }

        .btn-create:active {
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

        .login-link { 
            margin-top: 26px; 
            display: inline-block; 
            font-size: 0.9rem; 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link:hover {
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
    
    <?php include '../navbar.php'; ?>
    
    <div class="wrapper">
        <div class="register-box">
            <div class="cafe-logo-container">
                <img src="../assets/LOGO.jpg" alt="Logo Cafe" />
            </div>
            <h2>Create Account</h2>
            <div class="subtitle">Join us to explore fresh coffee & cakes</div>
            
            <?php if(!empty($message)): ?>
                <div class="msg <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Your Name" required autocomplete="name">
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="name@example.com" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="Phone Number" required autocomplete="tel">
                </div>
                
                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="date" name="birth_date" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn-create">Create Account</button>
            </form>

            <a href="login.php" class="login-link">Already have an account? Log in</a>
        </div>
    </div>

</body>
</html>