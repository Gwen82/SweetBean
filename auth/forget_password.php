<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$message_status = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if ($email === '') {
        $message_status = "Email address is required.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message_status = "Please enter a valid email address.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("
            SELECT id 
            FROM user
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            $reset_link = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/reset_password.php?token=' . urlencode($token) . '&email=' . urlencode($email);

            $hashedToken = hash('sha256', $token);

            $stmt = $conn->prepare("
                UPDATE user
                SET reset_token = ?,
                    reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                WHERE id = ?
            ");

            $stmt->bind_param(
                "si",
                $hashedToken,
                $user['id']
            );

            $stmt->execute();

            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'a1123328@mail.nuk.edu.tw';
                $mail->Password   = 'v j s r q h q u a l s c w f l y';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('a1123328@mail.nuk.edu.tw', 'Sweet Bean Cafe');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password - Sweet Bean Cafe';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #2b1e16; max-width: 520px; padding: 28px; border: 1px solid #e6dcd0; border-radius: 16px;'>
                        <h2 style='color: #8B5A2B;'>Sweet Bean Cafe</h2>
                        <p>Click the button below to reset your password. This link expires in 1 hour.</p>
                        <p style='margin: 28px 0;'>
                            <a href='$reset_link' style='background: #8B5A2B; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none;'>Reset Password</a>
                        </p>
                        <p style='font-size: 12px; color: #777;'>If you did not request this, you can ignore this email.</p>
                    </div>
                ";
                $mail->AltBody = "Reset your password: " . $reset_link;

                $mail->send();
                $message_status = "A password reset link has been sent to your email.";
                $message_type = "success";
            } catch (Exception $e) {
                error_log('Password reset email failed: ' . $mail->ErrorInfo);
                $message_status = "Email could not be sent. Please check SMTP settings.";
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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Cafe - Forgot Password</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f6f2ec; font-family: Arial, sans-serif; color: #2b1e16; padding: 24px; }
        .card { width: 100%; max-width: 430px; background: #fff; border: 1px solid #eadfd5; border-radius: 18px; padding: 32px; box-shadow: 0 8px 24px rgba(90, 56, 37, .08); }
        h1 { margin: 0 0 8px; color: #8B5A2B; font-size: 28px; }
        p { color: #735f54; }
        label { display: block; margin: 24px 0 8px; font-weight: 700; color: #8B5A2B; }
        input { width: 100%; padding: 14px; border: 1px solid #dacdc2; border-radius: 12px; font-size: 15px; }
        button { width: 100%; margin-top: 18px; padding: 14px; border: 0; border-radius: 12px; background: #8B5A2B; color: #fff; font-weight: 700; cursor: pointer; }
        .msg { padding: 12px; border-radius: 10px; margin: 16px 0; font-size: 14px; }
        .error { background: #f8d7da; color: #842029; }
        .success { background: #d1e7dd; color: #0f5132; }
        a { display: inline-block; margin-top: 18px; color: #8B5A2B; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Forgot Password</h1>
        <p>Enter your registered email to receive a reset link.</p>

        <?php if ($message_status !== ""): ?>
            <div class="msg <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message_status); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="your-email@example.com" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <a href="login.php">Back to Login</a>
    </main>
</body>
</html>
