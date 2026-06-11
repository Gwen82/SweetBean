<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Sesuaikan path ini dengan letak folder PHPMailer kamu
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/* =================================================
DATABASE TEMPLATE (BELUM DIAKTIFKAN)
=================================================
include '../db.php'; 
=================================================
*/

$message_status = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $message_status = "Email address is required.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message_status = "Please enter a valid email address.";
        $message_type = "error";
    } else {
        
        // ===== MOCK / DUMMY DATABASE CHECK =====
        $dummy_users = [
            'admin@sweetbean.com',
            'user@example.com',
            'priscilliagwen@gmail.com' 
        ];

        $email_found = false;
        if (in_array($email, $dummy_users)) {
            $email_found = true;
        }

        if ($email_found) {
            
            // Generate Token Unik untuk Reset Link
            $token = bin2hex(random_bytes(16));

            // Link reset password (arahkan ke file reset_password.php nanti)
            $reset_link = "http://localhost/SweetBean/auth/reset_password.php?token=" . $token . "&email=" . $email;

            // ===== EXECUTE PHPMAILER =====
            $mail = new PHPMailer(true);

            try {
                // Server Settings
                $mail->SMTPDebug = SMTP::DEBUG_OFF; 
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'a1123328@mail.nuk.edu.tw'; 
                $mail->Password   = 'v j s r q h q u a l s c w f l y'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Sender & Recipient
                $mail->setFrom('a1123328@mail.nuk.edu.tw', 'Sweet Bean Cafe');
                $mail->addAddress($email); 

                // Content HTML bergaya Cafe Premium sesuai warna patokan
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password - Sweet Bean Cafe';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; color: #2d2219; max-width: 500px; border: 1px solid rgba(111, 78, 55, 0.15); padding: 30px; border-radius: 16px; background-color: #ffffff;'>
                        <h2 style='color: #6f4e37; margin-bottom: 5px;'>☕ Sweet Bean Cafe</h2>
                        <p style='font-size: 14px; color: #8c7a6b; margin-top:0;'>Password Reset Request</p>
                        <hr style='border: 0; border-top: 1px solid rgba(111, 78, 55, 0.1); margin: 20px 0;'>
                        <p>Hello,</p>
                        <p>We received a request to reset your password for your Sweet Bean Cafe account. Click the button below to set up a new password:</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='$reset_link' style='background-color: #6f4e37; color: white; padding: 12px 28px; text-decoration: none; border-radius: 12px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(111,78,55,0.15);'>Reset Password</a>
                        </p>
                        <p style='font-size: 12px; color: #8c7a6b;'>This link will expire in 1 hour. If you did not make this request, you can safely ignore this email.</p>
                    </div>
                ";
                $mail->AltBody = "Hello, reset your password by opening this link: " . $reset_link;

                $mail->send();
                $message_status = "A password reset link has been successfully sent to your email!";
                $message_type = "success";

            } catch (Exception $e) {
                $message_status = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
                $message_type = "error";
            }

        } else {
            $message_status = "This email address is not registered in our system.";
            $message_type = "error";
        }
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Coffee - Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Menggunakan Variabel Warna Persis Sesuai Patokan UI */
        :root {
            --primary-color: #6f4e37;
            --primary-hover: #5a3d2a;
            --bg-color: #fdfaf7;
            --card-bg: #ffffff;
            --text-main: #2d2219;
            --accent-color: #e6ccb2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 15px 30px 15px;
        }

        /* --- Header / Navbar Sesuai Patokan --- */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 50px;
            border-bottom: 1px solid rgba(111, 78, 55, 0.1);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            width: 100%;
        }

        .header-left, .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-placeholder-nav {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: -0.5px;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 35px;
        }

        nav a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s, transform 0.2s;
        }

        nav a:hover {
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        /* --- Card Wrapper Bergaya Premium Kontainer --- */
        .wrapper {
            width: 100%;
            max-width: 450px;
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(111, 78, 55, 0.08);
            border: 1px solid rgba(111, 78, 55, 0.05);
            padding: 40px 35px;
            text-align: center;
        }

        /* Efek Dekorasi Grid Mirip Placeholder Box Patokan */
        .image-container {
            width: 75px;
            height: 75px;
            background: #faf6f2;
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dcd0c4;
            margin: 0 auto 20px auto;
            border: 1px solid rgba(111, 78, 55, 0.08);
        }

        .image-container i.coffee-icon {
            font-size: 2.2rem;
            color: var(--primary-color);
            opacity: 0.8;
            z-index: 1;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .page-subtitle {
            color: #8c7a6b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        /* --- Input Form Premium --- */
        .form-group {
            text-align: left;
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--text-main);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        input[type=email] {
            width: 100%;
            padding: 14px;
            border: 1px solid rgba(111, 78, 55, 0.15);
            border-radius: 12px;
            background: #fcf8f5;
            transition: all 0.2s ease;
            font-size: 15px;
            color: var(--text-main);
        }

        input[type=email]:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 4px 12px rgba(111, 78, 55, 0.05);
        }

        /* --- Elegant Action Button Mengikuti Gaya Add-to-Cart --- */
        .add-cart-btn {
            width: 100%;
            padding: 14px;
            background: #fcf8f5;
            border: 1px solid rgba(111, 78, 55, 0.15);
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-color);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            margin-bottom: 25px;
        }

        .add-cart-btn:hover {
            background: var(--primary-color);
            color: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(111, 78, 55, 0.15);
        }

        /* --- Alert Messages Custom Box --- */
        .msg {
            padding: 14px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid;
        }

        .msg.error {
            background: #fff5f5;
            color: #c92a2a;
            border-color: #ffe3e3;
        }

        .msg.success {
            background: #f4fbf7;
            color: #2b8a3e;
            border-color: #e6f4ea;
        }

        .back-link {
            text-decoration: none;
            color: #8c7a6b;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <a href="../index.php">
                <img src="../assets/LOGO.jpg" alt="Sweet Bean Coffee Logo" class="logo-placeholder-nav">
            </a>
            <span class="brand-name">Sweet Bean Coffee</span>
        </div>
        <div class="header-right">
            <nav>
                <a href="../index.php">Home</a>
                <a href="#">About us</a>
                <a href="#">Contact</a>
            </nav>
        </div>
    </header>

    <div class="wrapper">
        <div class="image-container">
            <i class="fa-solid fa-mug-hot coffee-icon"></i>
        </div>
        
        <h1 class="page-title">Forgot Password</h1>
        <p class="page-subtitle">Enter your registered email to receive a secure recovery link.</p>

        <?php if ($message_status !== ""): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php if ($message_type === 'success'): ?>
                    <i class="fa-solid fa-circle-check"></i>
                <?php else: ?>
                    <i class="fa-solid fa-circle-exclamation"></i>
                <?php endif; ?>
                <span><?php echo $message_status; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your-email@example.com" required>
            </div>

            <button type="submit" class="add-cart-btn">
                <i class="fa-solid fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <a href="login.php" class="back-link">Back to Login Page</a>
    </div>

</body>
</html>