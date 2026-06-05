<?php
// Start the session to store our temporary "database" of users
session_start();

// Initialize our temporary user storage if it doesn't exist yet
if (!isset($_SESSION['mock_users'])) {
    $_SESSION['mock_users'] = [];
}

$message = "";
$message_type = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $birth_date = $_POST['birth_date'];
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($phone) || empty($birth_date) || empty($password)) {
        $message = "All fields are required!";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } else {
        $user_exists = false;
        foreach ($_SESSION['mock_users'] as $user) {
            if ($user['email'] === $email || $user['phone'] === $phone) {
                $user_exists = true;
                break;
            }
        }

        if ($user_exists) {
            $message = "This email or phone number is already registered.";
            $message_type = "error";
        } else {
            $_SESSION['mock_users'][] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'birth_date' => $birth_date,
                'password' => $password, 
                'role' => 'customer' 
            ];

            $message = "Account created successfully! Redirecting to login...";
            $message_type = "success";
            header("refresh:2;url=login.php");
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
    <!-- Importing modern clean fonts -->
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
            background-color: var(--bg-color); 
            color: var(--text-main);
            margin: 0; 
            padding: 0; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Elegant Navbar Grid */
        .navbar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 20px 40px; 
            background: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(90, 56, 37, 0.08); 
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .logo { 
            font-family: 'Playfair Display', serif;
            font-weight: 700; 
            font-size: 1.4rem; 
            color: var(--primary-color); 
            letter-spacing: 0.5px;
        }

        .nav-links a { 
            margin-left: 28px; 
            text-decoration: none; 
            color: var(--text-main); 
            font-size: 0.95rem;
            font-weight: 400;
            transition: color 0.3s ease;
        }

        .nav-links a:hover { 
            color: var(--accent-color); 
        }

        /* Container & Card Layout */
        .wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 100px 20px 40px 20px; /* offset for fixed navbar */
        }

        .register-box { 
            background: var(--card-bg); 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 10px 35px rgba(90, 56, 37, 0.06); 
            width: 100%;
            max-width: 420px; 
            text-align: center; 
            border: 1px solid rgba(90, 56, 37, 0.04);
            box-sizing: border-box;
        }

        h2 { 
            font-family: 'Playfair Display', serif;
            color: var(--primary-color); 
            font-size: 2.1rem;
            margin-top: 0;
            margin-bottom: 8px; 
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        /* Input Customizations */
        .form-group { 
            margin-bottom: 20px; 
            text-align: left; 
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input { 
            width: 100%; 
            padding: 14px 16px; 
            border: 1.5px solid #e6e2dc; 
            background-color: #faf9f6;
            color: var(--text-main);
            border-radius: 12px; 
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box; 
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(90, 56, 37, 0.1);
        }

        /* Subtle Fix for Native Date Picker Alignment */
        .form-group input[type="date"] {
            color: var(--text-main);
        }

        /* Buttons & Dynamic Messages */
        .btn-create { 
            width: 100%; 
            padding: 14px; 
            background: var(--primary-color); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-family: 'Poppins', sans-serif;
            font-size: 1rem; 
            font-weight: 500;
            cursor: pointer; 
            margin-top: 10px;
            transition: background 0.3s ease, transform 0.1s ease;
        }

        .btn-create:hover { 
            background: var(--primary-hover); 
        }

        .btn-create:active {
            transform: scale(0.98);
        }

        .msg { 
            margin-bottom: 24px; 
            font-size: 0.9rem;
            font-weight: 400; 
            padding: 12px 16px; 
            border-radius: 12px; 
            text-align: left;
            animation: fadeIn 0.4s ease;
        }
        
        .msg.error { 
            color: #842029; 
            background-color: #f8d7da; 
            border: 1px solid #f5c2c7; 
        }
        
        .msg.success { 
            color: #0f5132; 
            background-color: #d1e7dd; 
            border: 1px solid #badbcc; 
        }

        .login-link { 
            margin-top: 24px; 
            display: inline-block; 
            font-size: 0.9rem; 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 500;
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

    <!-- Header navigation layer matching wireframe criteria -->
    <div class="navbar">
        <div class="logo">Sweet Bean Cafe</div>
        <div class="nav-links">
            <a href="#">Contact</a>
            <a href="#">About us</a>
            <a href="#">Home</a>
        </div>
    </div>

    <!-- Main Registration Window -->
    <div class="wrapper">
        <div class="register-box">
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