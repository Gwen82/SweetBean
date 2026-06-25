<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";

$message = "";
$message_type = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // --- DI SINI PHP NYA DITARUH ---
    // Cek apakah checkbox subscribe dicentang. Jika iya set 1, jika tidak set 0.
    $is_subscribed = isset($_POST['subscribe']) ? 1 : 0;
    // --------------------------------

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $birth_date)) {
        $message = "Invalid birth date format!";
        $message_type = "error";
    } else {
        $birth_year = (int) substr($birth_date, 0, 4);
        $current_year = (int) date("Y");

        if ($birth_year < 1900 || $birth_year > $current_year) {
            $message = "Birth year must be between 1900 and " . $current_year;
            $message_type = "error";
        } else {
            $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

            if (mysqli_num_rows($check) > 0) {
                $message = "Email already exists!";
                $message_type = "error";
            } else {
                // --- DI SINI JUGA DIUBAH ---
                // Tambahkan kolom 'is_subscribed' dan variabel '$is_subscribed' ke dalam query SQL
                $sql = "INSERT INTO users (name, email, phone, birth_date, password, role, is_subscribed)
                        VALUES ('$name', '$email', '$phone', '$birth_date', '$password', 'customer', '$is_subscribed')";

                if (mysqli_query($conn, $sql)) {
                    $message = "Account created successfully!";
                    $message_type = "success";
                    header("Location: login.php?registered=success");
                    exit();
                } else {
                    $message = "Registration Failed: " . mysqli_error($conn);
                    $message_type = "error";
                }
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
            --primary-color: #5a3825;
            --primary-hover: #442a1c;
            --text-main: #2b1e16;
            --text-muted: #7f6a5f;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5efe6 0%, #e6dcd0 50%, #d7c4b7 100%);
            color: var(--text-main);
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 45px 20px 60px;
        }

        .register-box {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(16px);
            padding: 40px;
            border-radius: 32px;
            box-shadow: 0 20px 50px rgba(90,56,37,0.12);
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
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid rgba(140,118,107,0.25);
            border-radius: 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(90,56,37,0.12);
        }

        .form-group checkbox {
            .form-group input {
            width: 100%;
            padding: 14px 18px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }
        }

        .btn-create {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-create:hover {
            background: var(--primary-hover);
        }

        .msg {
            margin-bottom: 24px;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 0.9rem;
            text-align: left;
        }

        .msg.error {
            color: #842029;
            background-color: #f8d7da;
        }

        .login-link {
            margin-top: 26px;
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="wrapper">
    <div class="register-box">

        <div class="cafe-logo-container">
            <img src="../assets/LOGO.jpg" alt="Logo Cafe">
        </div>

        <h2>Create Account</h2>
        <div class="subtitle">Join us to explore fresh coffee & cakes</div>

        <?php if (!empty($message)): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Your Name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="Phone Number" required>
            </div>

            <div class="form-group">
                <label>Birth Date</label>
                <input 
                    type="date" 
                    name="birth_date" 
                    min="1900-01-01"
                    max="<?php echo date('Y-m-d'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group" style="margin-top: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding-left: 5px;">
                <input type="checkbox" id="subscribe" name="subscribe" value="1" style="width: 16px; height: 16px; cursor: pointer; flex-shrink: 0;">
                <checkbox for="subscribe" style="font-size: 10px; color: #333333; font-weight: 500; cursor: pointer; user-select: none; line-height: 1;">
                    Keep me updated with seasonal blends, pastry drops, and exclusive offers!
                </checkbox>
            </div>

            <button type="submit" name="register" class="btn-create">
                Create Account
            </button>
        </form>

        <a href="login.php" class="login-link">
            Already have an account? Log in
        </a>

    </div>
</div>

</body>
</html>