<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Menggunakan __DIR__ agar path folder absolut dan aman dari error XAMPP
require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";
$message_type = "";

// LOGIKA UNTUK REMOVE SUBSCRIBER (Ganti status jadi 0, jangan hapus akun usernya!)
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Mengubah status is_subscribed menjadi 0 berdasarkan user_id
    mysqli_query($conn, "
        UPDATE users 
        SET is_subscribed = 0 
        WHERE user_id = '$id'
    ");

    header("Location: newsletter.php?deleted=success");
    exit();
}

if (isset($_POST['send_newsletter'])) {

    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);

    if ($subject == "" || $content == "") {
        $message = "Please fill in subject and content.";
        $message_type = "error";
    } else {

        // AMBIL DATA HANYA DARI USER YANG BERLANGGANAN (is_subscribed = 1)
        $subscriberQuery = mysqli_query($conn,"
            SELECT email
            FROM users
            WHERE is_subscribed = 1
        ");

        $successCount = 0;
        $failedCount = 0;

        while($subscriber = mysqli_fetch_assoc($subscriberQuery)) {

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'a1123328@mail.nuk.edu.tw';
                $mail->Password = 'mgis hfak drxg ofud';

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('a1123328@mail.nuk.edu.tw', 'Sweet Bean Coffee');
                $mail->addAddress($subscriber['email']);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = "
                <div style='font-family:Arial;padding:20px'>
                    <h2 style='color:#6f4e37'>Sweet Bean Coffee</h2>
                    <hr>
                    <p>".nl2br(htmlspecialchars($content))."</p>
                    <br>
                    <p style='color:gray;font-size:12px'>Thank you for subscribing to Sweet Bean Coffee.</p>
                </div>
                ";

                $mail->send();
                $successCount++;

            } catch (Exception $e) {
                $failedCount++;
                error_log("Newsletter Error: " . $mail->ErrorInfo);
            }
        }

        $message = "Newsletter sent successfully. " . $successCount . " delivered, " . $failedCount . " failed.";
        $message_type = "success";
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] === 'success') {
    $message = "Subscriber removed successfully.";
    $message_type = "success";
}

// HITUNG JUMLAH YANG BERLANGGANAN DARI TABEL USERS
$total_subscribers = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM users WHERE is_subscribed = 1
"))['total'];

// AMBIL LIST USER YANG BERLANGGANAN UNTUK DITAMPILKAN DI TABEL
$subscribers = mysqli_query($conn, "
    SELECT user_id, email, birth_date AS created_at 
    FROM users 
    WHERE is_subscribed = 1 
    ORDER BY user_id DESC
");

$adminName = $_SESSION['user_name'] ?? 'Admin';
$avatar = strtoupper(substr($adminName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Newsletter | Sweet Bean Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { box-sizing: border-box; }
body { margin: 0; background: #f6efe7; color: #2a211b; font-family: Arial, sans-serif; }
.layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
.sidebar { background: linear-gradient(180deg, #2f1d14, #5a3825); color: white; padding: 28px 20px; }
.logo-box { text-align: center; margin-bottom: 36px; }
.logo-box img { width: 78px; height: 78px; border-radius: 50%; object-fit: cover; background: white; margin-bottom: 10px; }
.logo-box h2 { margin: 0; font-size: 20px; }
.logo-box p { margin: 6px 0 0; color: #ead7c7; font-size: 13px; }
.nav-menu { display: flex; flex-direction: column; gap: 14px; }
.nav-menu a { text-decoration: none; color: white; padding: 15px 18px; border-radius: 16px; font-weight: 800; background: rgba(255,255,255,.08); }
.nav-menu a.active, .nav-menu a:hover { background: #fff8ef; color: #5a3825; }
.logout { margin-top: 40px; background: rgba(255,255,255,.15)!important; }
.main { padding: 34px; }
.topbar { background: white; padding: 28px; border-radius: 28px; box-shadow: 0 14px 35px rgba(90,56,37,.08); margin-bottom: 24px; display: flex; justify-content: space-between; gap: 18px; align-items: center; }
.topbar h1 { margin: 0; color: #5a3825; font-size: 34px; }
.topbar p { color: #806e62; margin: 8px 0 0; }
.admin-pill { display: flex; align-items: center; gap: 12px; background: #fff8ef; padding: 12px 16px; border-radius: 999px; }
.avatar { width: 44px; height: 44px; border-radius: 50%; background: #5a3825; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; }
.admin-pill strong { color: #5a3825; }
.admin-pill span { display: block; color: #806e62; font-size: 13px; margin-top: 2px; }
.msg { padding: 14px 18px; border-radius: 16px; margin-bottom: 22px; font-weight: 800; }
.msg.success { background: #d1fae5; color: #047857; }
.msg.error { background: #fee2e2; color: #b91c1c; }
.stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; margin-bottom: 24px; }
.stat-card { background: white; padding: 24px; border-radius: 24px; box-shadow: 0 14px 35px rgba(90,56,37,.08); }
.stat-label { color: #806e62; font-size: 14px; font-weight: 800; }
.stat-number { margin-top: 10px; color: #5a3825; font-size: 30px; font-weight: 900; }
.content { display: grid; grid-template-columns: 0.9fr 1.2fr; gap: 22px; }
.panel { background: white; border-radius: 28px; padding: 26px; box-shadow: 0 14px 35px rgba(90,56,37,.08); }
.panel h2 { color: #5a3825; margin: 0 0 20px; }
.form-row { margin-bottom: 16px; }
label { display: block; margin-bottom: 8px; color: #806e62; font-weight: 900; }
input, textarea { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 14px; background: white; }
textarea { min-height: 180px; resize: vertical; }
.btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 20px; border-radius: 14px; border: none; background: #5a3825; color: white; text-decoration: none; font-weight: 900; cursor: pointer; }
.btn.danger { background: #b91c1c; }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; color: #806e62; font-size: 13px; padding: 14px 10px; border-bottom: 1px solid #eadfd6; }
td { padding: 16px 10px; border-bottom: 1px solid #f1e8df; }
.email { font-weight: 900; color: #5a3825; }
.small { font-size: 13px; color: #806e62; margin-top: 4px; }
@media(max-width: 1100px) { .layout { grid-template-columns: 1fr; } .content { grid-template-columns: 1fr; } }
@media(max-width: 700px) { .main { padding: 20px; } .topbar { flex-direction: column; align-items: flex-start; } .stats { grid-template-columns: 1fr; } }
</style>
</head>

<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/LOGO.jpg" alt="Sweet Bean Logo">
            <h2>Sweet Bean Admin</h2>
            <p>Operation Control Panel</p>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_menu.php">Manage Menu</a>
            <a href="employees.php">Employees</a>
            <a href="reports.php">Sales Reports</a>
            <a href="reviews.php">Reviews</a>
            <a href="newsletter.php" class="active">Newsletter</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h1>Newsletter</h1>
                <p>Manage newsletter subscribers and promotional email messages.</p>
            </div>
            <div class="admin-pill">
                <div class="avatar"><?php echo htmlspecialchars($avatar); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($adminName); ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Total Subscribers</div>
                <div class="stat-number"><?php echo $total_subscribers; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Email Technology</div>
                <div class="stat-number">SMTP</div>
            </div>
        </div>

        <div class="content">
            <section class="panel">
                <h2>Compose Newsletter</h2>
                <form method="POST">
                    <div class="form-row">
                        <label>Email Subject</label>
                        <input type="text" name="subject" placeholder="Example: Weekend Coffee Promotion" required>
                    </div>
                    <div class="form-row">
                        <label>Newsletter Content</label>
                        <textarea name="content" placeholder="Write your promotional message here..." required></textarea>
                    </div>
                    <button type="submit" name="send_newsletter" class="btn">Send Newsletter</button>
                </form>
            </section>

            <section class="panel">
                <h2>Subscriber List</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Birth Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($subscribers && mysqli_num_rows($subscribers) > 0): ?>
                                <?php while($sub = mysqli_fetch_assoc($subscribers)): ?>
                                    <tr>
                                        <td>
                                            <div class="email"><?php echo htmlspecialchars($sub['email']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small"><?php echo htmlspecialchars($sub['created_at'] ?? '-'); ?></div>
                                        </td>
                                        <td>
                                            <a href="newsletter.php?delete=<?php echo $sub['user_id']; ?>" class="btn danger" onclick="return confirm('Remove this subscriber from list?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No subscribers yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>