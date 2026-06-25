<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$editMode = isset($_GET['edit']);
$message = "";
$message_type = "";

if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);

    $update = mysqli_query($conn, "
        UPDATE users
        SET name='$name',
            phone='$phone',
            birth_date='$birth_date'
        WHERE user_id='$user_id'
    ");

    if ($update) {
        $_SESSION['user_name'] = $name;
        header("Location: profile.php?updated=success");
        exit();
    } else {
        $message = "Failed to update profile.";
        $message_type = "error";
    }
}

if (isset($_GET['updated']) && $_GET['updated'] === 'success') {
    $message = "Profile updated successfully.";
    $message_type = "success";
}

$result = mysqli_query($conn, "
    SELECT * FROM users
    WHERE user_id='$user_id'
    LIMIT 1
");

$user = mysqli_fetch_assoc($result);
$avatar = strtoupper(substr($user['name'] ?? 'C', 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile | Sweet Bean Coffee</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    margin: 0;
    min-height: 100vh;
    background: #fff8ef;
    color: #2a211b;
    font-family: Arial, sans-serif;
}

.profile-page {
    padding: 55px 20px 80px;
}

.profile-card {
    max-width: 760px;
    margin: auto;
    padding: 38px;
    border-radius: 26px;
    background: #fff;
    box-shadow: 0 14px 35px rgba(70, 42, 25, 0.08);
}

.avatar {
    width: 115px;
    height: 115px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 42px;
    font-weight: 900;
    margin: 0 auto 18px;
}

h1 {
    text-align: center;
    color: #5a3825;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #806e62;
    margin-bottom: 34px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 18px 0;
    border-top: 1px solid rgba(90, 56, 37, 0.12);
}

.detail-row span {
    color: #806e62;
}

.detail-row strong {
    text-align: right;
}

label {
    display: block;
    margin-top: 18px;
    font-weight: bold;
    color: #806e62;
}

input {
    width: 100%;
    padding: 13px;
    margin-top: 8px;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-sizing: border-box;
}

input[readonly] {
    background: #f7efe6;
    color: #806e62;
}

.msg {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.msg.success {
    background: #d1e7dd;
    color: #0f5132;
}

.msg.error {
    background: #f8d7da;
    color: #842029;
}

.actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 30px;
    justify-content: center;
}

.actions a,
.actions button {
    display: flex;
    align-items: center;
    justify-content: center;

    height: 48px;
    min-width: 140px;

    padding: 0 22px;

    border: none;
    border-radius: 12px;

    background: #5a3825;
    color: white;

    font-size: 15px;
    font-weight: 700;

    text-decoration: none;
    cursor: pointer;
}

.actions .secondary {
    background: #f7efe6;
    color: #5a3825;
}

@media(max-width: 600px) {
    .detail-row {
        flex-direction: column;
        gap: 6px;
    }

    .detail-row strong {
        text-align: left;
    }
}
</style>
</head>

<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="profile-page">
    <section class="profile-card">

        <div class="avatar"><?php echo htmlspecialchars($avatar); ?></div>

        <h1>Your Profile</h1>
        <div class="subtitle">Manage your personal information</div>

        <?php if (!empty($message)): ?>
            <div class="msg <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$editMode): ?>

            <div class="detail-row">
                <span>Name</span>
                <strong><?php echo htmlspecialchars($user['name'] ?? '-'); ?></strong>
            </div>

            <div class="detail-row">
                <span>Email</span>
                <strong><?php echo htmlspecialchars($user['email'] ?? '-'); ?></strong>
            </div>

            <div class="detail-row">
                <span>Phone</span>
                <strong><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></strong>
            </div>

            <div class="detail-row">
                <span>Birth Date</span>
                <strong><?php echo htmlspecialchars($user['birth_date'] ?? '-'); ?></strong>
            </div>

            <div class="detail-row">
                <span>Role</span>
                <strong><?php echo htmlspecialchars($user['role'] ?? 'customer'); ?></strong>
            </div>

            <div class="actions">
                <a href="profile.php?edit=1">Edit Profile</a>
                <a href="../auth/logout.php" class="secondary">Logout</a>
            </div>

        <?php else: ?>

            <form method="POST">

                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>

                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>

                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">

                <label>Birth Date</label>
                <input type="date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>">

                <label>Role</label>
                <input type="text" value="<?php echo htmlspecialchars($user['role'] ?? 'customer'); ?>" readonly>

                <div class="actions">
                    <button type="submit" name="update_profile">Save Changes</button>
                    <a href="profile.php" class="secondary">Cancel</a>
                </div>

            </form>

        <?php endif; ?>

    </section>
</main>

</body>
</html>