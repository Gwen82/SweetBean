<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";
$message_type = "";

if (isset($_POST['add_staff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' OR phone='$phone'");

    if (mysqli_num_rows($check) > 0) {
        $message = "Email or phone already exists.";
        $message_type = "error";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO users (name,email,phone,birth_date,password,role)
            VALUES ('$name','$email','$phone','$birth_date','$password','staff')
        ");

        if ($insert) {
            $message = "Staff account added successfully.";
            $message_type = "success";
        } else {
            $message = "Failed to add staff: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}

if (isset($_POST['update_staff'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);

    $password_sql = "";

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password='$password'";
    }

    $update = mysqli_query($conn, "
        UPDATE users
        SET name='$name',
            email='$email',
            phone='$phone',
            birth_date='$birth_date'
            $password_sql
        WHERE user_id='$user_id'
        AND role='staff'
    ");

    if ($update) {
        $message = "Staff updated successfully.";
        $message_type = "success";
    } else {
        $message = "Failed to update staff.";
        $message_type = "error";
    }
}

if (isset($_GET['delete'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['delete']);

    mysqli_query($conn, "
        DELETE FROM users
        WHERE user_id='$user_id'
        AND role='staff'
    ");

    header("Location: employees.php");
    exit();
}

$edit_staff = null;

if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);

    $result = mysqli_query($conn, "
        SELECT *
        FROM users
        WHERE user_id='$edit_id'
        AND role='staff'
        LIMIT 1
    ");

    $edit_staff = mysqli_fetch_assoc($result);
}

$staffs = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE role='staff'
    ORDER BY user_id DESC
");

$adminName = $_SESSION['user_name'] ?? 'Admin';
$avatar = strtoupper(substr($adminName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employees | Sweet Bean Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f6efe7;
    color: #2a211b;
    font-family: Arial, sans-serif;
}

.layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
}

.sidebar {
    background: linear-gradient(180deg, #2f1d14, #5a3825);
    color: white;
    padding: 28px 20px;
}

.logo-box {
    text-align: center;
    margin-bottom: 36px;
}

.logo-box img {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    object-fit: cover;
    background: white;
    margin-bottom: 10px;
}

.logo-box h2 {
    margin: 0;
    font-size: 20px;
}

.logo-box p {
    margin: 6px 0 0;
    color: #ead7c7;
    font-size: 13px;
}

.nav-menu {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.nav-menu a {
    text-decoration: none;
    color: white;
    padding: 15px 18px;
    border-radius: 16px;
    font-weight: 800;
    background: rgba(255,255,255,.08);
}

.nav-menu a.active,
.nav-menu a:hover {
    background: #fff8ef;
    color: #5a3825;
}

.logout {
    margin-top: 40px;
    background: rgba(255,255,255,.15)!important;
}

.main {
    padding: 34px;
}

.topbar {
    background: white;
    padding: 28px;
    border-radius: 28px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
}

.topbar h1 {
    margin: 0;
    color: #5a3825;
    font-size: 34px;
}

.topbar p {
    color: #806e62;
    margin: 8px 0 0;
}

.admin-pill {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff8ef;
    padding: 12px 16px;
    border-radius: 999px;
}

.avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
}

.admin-pill strong {
    color: #5a3825;
}

.admin-pill span {
    display: block;
    color: #806e62;
    font-size: 13px;
    margin-top: 2px;
}

.msg {
    padding: 14px 18px;
    border-radius: 16px;
    margin-bottom: 22px;
    font-weight: 800;
}

.msg.success {
    background: #d1fae5;
    color: #047857;
}

.msg.error {
    background: #fee2e2;
    color: #b91c1c;
}

.content {
    display: grid;
    grid-template-columns: 0.85fr 1.4fr;
    gap: 22px;
}

.panel {
    background: white;
    border-radius: 28px;
    padding: 26px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
}

.panel h2 {
    color: #5a3825;
    margin: 0 0 20px;
}

.form-row {
    margin-bottom: 16px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #806e62;
    font-weight: 900;
}

input {
    width: 100%;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 14px;
    background: white;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 20px;
    border: none;
    border-radius: 14px;
    background: #5a3825;
    color: white;
    text-decoration: none;
    font-weight: 900;
    cursor: pointer;
    margin: 2px;
}

.btn.secondary {
    background: #f7efe6;
    color: #5a3825;
}

.btn.danger {
    background: #b91c1c;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    color: #806e62;
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eadfd6;
}

td {
    padding: 16px 10px;
    border-bottom: 1px solid #f1e8df;
}

.staff-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.staff-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #5a3825;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
}

.staff-name {
    color: #5a3825;
    font-weight: 900;
}

.small {
    font-size: 13px;
    color: #806e62;
    margin-top: 4px;
}

.badge {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    background: #fff3cd;
    color: #8a5a00;
}

.action-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

@media(max-width: 1100px) {
    .layout {
        grid-template-columns: 1fr;
    }

    .content {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 700px) {
    .main {
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }
}
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
            <a href="employees.php" class="active">Employees</a>
            <a href="reports.php">Sales Reports</a>
            <a href="reviews.php">Reviews</a>
            <a href="newsletter.php">Newsletter</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Employees</h1>
                <p>Manage Sweet Bean staff accounts and staff access.</p>
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

        <div class="content">

            <section class="panel">
                <h2><?php echo $edit_staff ? 'Edit Staff' : 'Add Staff'; ?></h2>

                <form method="POST">

                    <?php if ($edit_staff): ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_staff['user_id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <label>Staff Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            value="<?php echo htmlspecialchars($edit_staff['name'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <label>Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($edit_staff['email'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <label>Phone Number</label>
                        <input 
                            type="text" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($edit_staff['phone'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <label>Birth Date</label>
                        <input 
                            type="date" 
                            name="birth_date" 
                            value="<?php echo htmlspecialchars($edit_staff['birth_date'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <label>
                            Password <?php echo $edit_staff ? '(leave empty if not changed)' : ''; ?>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            <?php echo $edit_staff ? '' : 'required'; ?>
                        >
                    </div>

                    <?php if ($edit_staff): ?>
                        <button type="submit" name="update_staff" class="btn">Update Staff</button>
                        <a href="employees.php" class="btn secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_staff" class="btn">Add Staff</button>
                    <?php endif; ?>

                </form>
            </section>

            <section class="panel">
                <h2>Staff List</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Phone</th>
                                <th>Birth Date</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if($staffs && mysqli_num_rows($staffs) > 0): ?>
                                <?php while($staff = mysqli_fetch_assoc($staffs)): ?>
                                    <tr>
                                        <td>
                                            <div class="staff-info">
                                                <div class="staff-avatar">
                                                    <?php echo strtoupper(substr($staff['name'] ?? 'S', 0, 1)); ?>
                                                </div>

                                                <div>
                                                    <div class="staff-name">
                                                        <?php echo htmlspecialchars($staff['name']); ?>
                                                    </div>

                                                    <div class="small">
                                                        <?php echo htmlspecialchars($staff['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td><?php echo htmlspecialchars($staff['phone'] ?? '-'); ?></td>

                                        <td><?php echo htmlspecialchars($staff['birth_date'] ?? '-'); ?></td>

                                        <td>
                                            <span class="badge">Staff</span>
                                        </td>

                                        <td>
                                            <div class="action-group">
                                                <a class="btn secondary" href="employees.php?edit=<?php echo $staff['user_id']; ?>">
                                                    Edit
                                                </a>

                                                <a 
                                                    class="btn danger" 
                                                    href="employees.php?delete=<?php echo $staff['user_id']; ?>"
                                                    onclick="return confirm('Delete this staff account?');"
                                                >
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No staff accounts found.</td>
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