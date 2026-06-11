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

                // Content HTML bergaya Cafe
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password - Sweet Bean Cafe';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; color: #2b1e16; max-width: 500px; border: 1px solid #e6dcd0; padding: 30px; border-radius: 16px; background-color: #fafafa;'>
                        <h2 style='color: #8B5A2B; margin-bottom: 5px;'>☕ Sweet Bean Cafe</h2>
                        <p style='font-size: 14px; color: #555; margin-top:0;'>Password Reset Request (Dummy Mode)</p>
                        <hr style='border: 0; border-top: 1px solid #e6dcd0; margin: 20px 0;'>
                        <p>Hello,</p>
                        <p>We received a request to reset your password for your Sweet Bean Cafe account. Click the button below to set up a new password:</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='$reset_link' style='background-color: #8B5A2B; color: white; padding: 12px 28px; text-decoration: none; border-radius: 12px; font-weight: bold; display: inline-block; box-shadow: 0 4px 10px rgba(139,90,43,0.2);'>Reset Password</a>
                        </p>
                        <p style='font-size: 12px; color: #777;'>This link will expire in 1 hour. If you did not make this request, you can safely ignore this email.</p>
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

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Cafe - Forgot Password</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Arial, sans-serif;
            background: #f6f2ec;
            min-height: 100vh;
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            padding: 80px 20px 20px 20px; /* Ditambah padding top agar tidak tertutup navbar */
        }

        /* ===== NAVIGATION BAR ===== */
        .navbar { 
            width: 100%;
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 40px; 
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid #ddd;
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            z-index: 1000;
        }

        .header-left { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }

        .logo-nav { 
            width: 35px; 
            height: 35px; 
            border-radius: 50%; 
            object-fit: cover; 
        }

        .brand-name { 
            font-weight: bold; 
            font-size: 1.2rem; 
            color: #8B5A2B; 
        }

        .nav-links a { 
            margin-left: 20px; 
            text-decoration: none; 
            color: #333; 
            font-size: 14px; 
            font-weight: bold; 
        }

        .nav-links a:hover { 
            color: #8B5A2B; 
            text-decoration: underline;
        }

        /* ===== MAIN CARD CONFIG ===== */
        .wrapper { 
            width: 100%; 
            max-width: 450px; 
            margin-top: 40px;
        }

        .card {
            background: white; border-radius: 18px; padding: 35px;
            border: 1px solid #eee; box-shadow: 0 8px 25px rgba(0,0,0,.06); text-align: center;
        }

        /* Wrapper Logo dalam Card */
        .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 25px;
        }

        .logo-placeholder {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .logo h1 { 
            color: #8B5A2B; 
            font-size: 24px;
            margin-bottom: 6px; 
        }

        .logo p { 
            color: #777; 
            font-size: 14px; 
        }

        /* ===== FORM INPUT & BUTTON ===== */
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; color: #8B5A2B; font-weight: bold; margin-bottom: 8px; font-size: 14px; }
        
        input[type=email] {
            width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px;
            background: #fafafa; transition: .2s; font-size: 15px;
        }
        input[type=email]:focus { outline: none; border-color: #8B5A2B; background: white; }
        
        button {
            width: 100%; padding: 15px; border: none; border-radius: 12px;
            background: #8B5A2B; color: white; font-size: 16px; font-weight: bold;
            cursor: pointer; transition: .2s; margin-bottom: 20px;
        }
        button:hover { background: #704723; transform: translateY(-2px); }
        
        /* ===== MESSAGES & LINKS ===== */
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
            <h1>Sweet Bean Coffee</h1>
            <p>Enter your email to receive a password reset link</p>
        </div>

        <?php if ($message_status !== ""): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo $message_status; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your-email@example.com" required>
            </div>

            <button type="submit">Send Reset Link</button>
        </form>

        <a href="login.php" class="back-link">Back to Login</a>
    </div>
</div>

</body>
</html>