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

if (isset($_GET['delete'])) {
    $review_id = mysqli_real_escape_string($conn, $_GET['delete']);

    $delete = mysqli_query($conn, "
        DELETE FROM reviews
        WHERE review_id='$review_id'
    ");

    if ($delete) {
        header("Location: reviews.php?deleted=success");
        exit();
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] === 'success') {
    $message = "Review deleted successfully.";
    $message_type = "success";
}

$total_reviews = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM reviews
"))['total'];

$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(AVG(rating),0) AS average FROM reviews
"))['average'];

$five_star = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM reviews WHERE rating=5
"))['total'];

$reviews = mysqli_query($conn, "
    SELECT reviews.*, users.name AS customer_name, menu.product_name
    FROM reviews
    LEFT JOIN users ON reviews.user_id = users.user_id
    LEFT JOIN menu ON reviews.menu_id = menu.menu_id
    ORDER BY reviews.review_id DESC
");

$adminName = $_SESSION['user_name'] ?? 'Admin';
$avatar = strtoupper(substr($adminName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reviews | Sweet Bean Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { box-sizing: border-box; }

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

.admin-pill strong { color: #5a3825; }

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

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 24px;
    box-shadow: 0 14px 35px rgba(90,56,37,.08);
}

.stat-label {
    color: #806e62;
    font-size: 14px;
    font-weight: 800;
}

.stat-number {
    margin-top: 10px;
    color: #5a3825;
    font-size: 30px;
    font-weight: 900;
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

.review-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.review-card {
    background: #fff8ef;
    border-radius: 22px;
    padding: 22px;
    border: 1px solid #eadfd6;
}

.review-top {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 14px;
}

.customer {
    color: #5a3825;
    font-weight: 900;
    font-size: 18px;
}

.product {
    color: #806e62;
    font-size: 13px;
    margin-top: 4px;
}

.rating {
    color: #d59a2f;
    font-weight: 900;
    white-space: nowrap;
}

.comment {
    color: #2a211b;
    line-height: 1.6;
    margin-top: 12px;
}

.review-date {
    color: #806e62;
    font-size: 13px;
    margin-top: 14px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 16px;
    border-radius: 12px;
    text-decoration: none;
    border: none;
    font-weight: 900;
    cursor: pointer;
}

.btn.danger {
    background: #b91c1c;
    color: white;
    margin-top: 14px;
}

.empty {
    background: #fff8ef;
    padding: 24px;
    border-radius: 18px;
    color: #806e62;
    text-align: center;
}

@media(max-width: 1100px) {
    .layout { grid-template-columns: 1fr; }
    .review-grid { grid-template-columns: 1fr; }
    .stats { grid-template-columns: 1fr; }
}

@media(max-width: 700px) {
    .main { padding: 20px; }
    .topbar { flex-direction: column; align-items: flex-start; }
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
            <a href="employees.php">Employees</a>
            <a href="reports.php">Sales Reports</a>
            <a href="reviews.php" class="active">Reviews</a>
            <a href="newsletter.php">Newsletter</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Customer Reviews</h1>
                <p>Monitor customer ratings and feedback for Sweet Bean Cafe.</p>
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
                <div class="stat-label">Total Reviews</div>
                <div class="stat-number"><?php echo $total_reviews; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Average Rating</div>
                <div class="stat-number"><?php echo number_format($avg_rating, 1); ?>/5</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Five Star Reviews</div>
                <div class="stat-number"><?php echo $five_star; ?></div>
            </div>
        </div>

        <section class="panel">
            <h2>Review List</h2>

            <?php if($reviews && mysqli_num_rows($reviews) > 0): ?>
                <div class="review-grid">
                    <?php while($review = mysqli_fetch_assoc($reviews)): ?>
                        <div class="review-card">
                            <div class="review-top">
                                <div>
                                    <div class="customer">
                                        <?php echo htmlspecialchars($review['customer_name'] ?? 'Customer'); ?>
                                    </div>

                                    <div class="product">
                                        <?php echo htmlspecialchars($review['product_name'] ?? 'General Service'); ?>
                                    </div>
                                </div>

                                <div class="rating">
                                    <?php echo str_repeat('★', (int)$review['rating']); ?>
                                </div>
                            </div>

                            <div class="comment">
                                <?php echo htmlspecialchars($review['comment'] ?? '-'); ?>
                            </div>

                            <div class="review-date">
                                <?php echo htmlspecialchars($review['created_at'] ?? ''); ?>
                            </div>

                            <a 
                                href="reviews.php?delete=<?php echo $review['review_id']; ?>" 
                                class="btn danger"
                                onclick="return confirm('Delete this review?');"
                            >
                                Delete Review
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty">No customer reviews yet.</div>
            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>