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

if (isset($_POST['add_menu'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $item_type = mysqli_real_escape_string($conn, $_POST['item_type']);
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;

    $image = "";

    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        $target = __DIR__ . "/../assets/images/" . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    $insert = mysqli_query($conn, "
        INSERT INTO menu
        (category_id, product_name, description, price, image, status, item_type, is_best_seller)
        VALUES
        ('$category_id', '$product_name', '$description', '$price', '$image', '$status', '$item_type', '$is_best_seller')
    ");

    if ($insert) {
        $message = "Menu item added successfully.";
        $message_type = "success";
    } else {
        $message = "Failed to add menu item: " . mysqli_error($conn);
        $message_type = "error";
    }
}

if (isset($_POST['update_menu'])) {
    $menu_id = mysqli_real_escape_string($conn, $_POST['menu_id']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $item_type = mysqli_real_escape_string($conn, $_POST['item_type']);
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;

    $image_sql = "";

    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        $target = __DIR__ . "/../assets/images/" . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $image_sql = ", image='$image'";
    }

    $update = mysqli_query($conn, "
        UPDATE menu
        SET category_id='$category_id',
            product_name='$product_name',
            description='$description',
            price='$price',
            status='$status',
            item_type='$item_type',
            is_best_seller='$is_best_seller'
            $image_sql
        WHERE menu_id='$menu_id'
    ");

    if ($update) {
        $message = "Menu item updated successfully.";
        $message_type = "success";
    } else {
        $message = "Failed to update menu item: " . mysqli_error($conn);
        $message_type = "error";
    }
}

if (isset($_GET['delete'])) {
    $menu_id = mysqli_real_escape_string($conn, $_GET['delete']);

    mysqli_query($conn, "
        DELETE FROM menu
        WHERE menu_id='$menu_id'
    ");

    header("Location: manage_menu.php");
    exit();
}

$edit_item = null;

if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);

    $edit_result = mysqli_query($conn, "
        SELECT *
        FROM menu
        WHERE menu_id='$edit_id'
        LIMIT 1
    ");

    $edit_item = mysqli_fetch_assoc($edit_result);
}

$categories = mysqli_query($conn, "
    SELECT *
    FROM categories
    ORDER BY category_name ASC
");

$menus = mysqli_query($conn, "
    SELECT menu.*, categories.category_name
    FROM menu
    LEFT JOIN categories ON menu.category_id = categories.category_id
    ORDER BY menu.menu_id DESC
");

$adminName = $_SESSION['user_name'] ?? 'Admin';
$avatar = strtoupper(substr($adminName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Menu | Sweet Bean Admin</title>
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
    grid-template-columns: 0.9fr 1.4fr;
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

input,
textarea,
select {
    width: 100%;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 14px;
    background: white;
}

textarea {
    resize: vertical;
    min-height: 90px;
}

.check-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 18px 0;
    color: #5a3825;
    font-weight: 900;
}

.check-row input {
    width: auto;
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
    vertical-align: middle;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.product-img {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    object-fit: cover;
    background: #fff8ef;
}

.product-icon {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    background: #fff8ef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #5a3825;
    font-weight: 900;
}

.product-name {
    font-weight: 900;
    color: #5a3825;
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
}

.available {
    background: #d1fae5;
    color: #047857;
}

.unavailable {
    background: #fee2e2;
    color: #b91c1c;
}

.type {
    background: #fff3cd;
    color: #8a5a00;
}

.best {
    background: #ede9fe;
    color: #6d28d9;
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
            <a href="manage_menu.php" class="active">Manage Menu</a>
            <a href="employees.php">Employees</a>
            <a href="reports.php">Sales Reports</a>
            <a href="reviews.php">Reviews</a>
            <a href="newsletter.php">Newsletter</a>
            <a href="../auth/logout.php" class="logout">Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Manage Menu</h1>
                <p>Add, update, or remove Sweet Bean Coffee menu items.</p>
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
                <h2><?php echo $edit_item ? 'Edit Menu Item' : 'Add New Menu'; ?></h2>

                <form method="POST" enctype="multipart/form-data">

                    <?php if ($edit_item): ?>
                        <input type="hidden" name="menu_id" value="<?php echo $edit_item['menu_id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <label>Product Name</label>
                        <input 
                            type="text" 
                            name="product_name" 
                            value="<?php echo htmlspecialchars($edit_item['product_name'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <label>Category</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>

                            <?php mysqli_data_seek($categories, 0); ?>
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                                <option 
                                    value="<?php echo $cat['category_id']; ?>"
                                    <?php echo ($edit_item && $edit_item['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <label>Price</label>
                        <input 
                            type="number" 
                            name="price" 
                            min="0" 
                            step="1"
                            value="<?php echo htmlspecialchars($edit_item['price'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <label>Item Type</label>
                        <select name="item_type" required>
                            <option value="Drink" <?php echo ($edit_item && $edit_item['item_type'] == 'Drink') ? 'selected' : ''; ?>>Drink</option>
                            <option value="Food" <?php echo (!$edit_item || $edit_item['item_type'] == 'Food') ? 'selected' : ''; ?>>Food</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Available" <?php echo (!$edit_item || $edit_item['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                            <option value="Unavailable" <?php echo ($edit_item && $edit_item['status'] == 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Product Image</label>
                        <input type="file" name="image" accept="image/*">

                        <?php if ($edit_item && !empty($edit_item['image'])): ?>
                            <div class="small">
                                Current image: <?php echo htmlspecialchars($edit_item['image']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <label class="check-row">
                        <input 
                            type="checkbox" 
                            name="is_best_seller" 
                            <?php echo ($edit_item && (int)$edit_item['is_best_seller'] === 1) ? 'checked' : ''; ?>
                        >
                        Mark as Best Seller
                    </label>

                    <?php if ($edit_item): ?>
                        <button type="submit" name="update_menu" class="btn">Update Menu</button>
                        <a href="manage_menu.php" class="btn secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_menu" class="btn">Add Menu</button>
                    <?php endif; ?>

                </form>
            </section>

            <section class="panel">
                <h2>Menu List</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Best</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if($menus && mysqli_num_rows($menus) > 0): ?>
                                <?php while($item = mysqli_fetch_assoc($menus)): ?>
                                    <tr>
                                        <td>
                                            <div class="product-info">
                                                <?php if(!empty($item['image'])): ?>
                                                    <img 
                                                        src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                                        class="product-img"
                                                    >
                                                <?php else: ?>
                                                    <div class="product-icon">
                                                        <?php echo ($item['item_type'] == 'Drink') ? '☕' : '🍰'; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div>
                                                    <div class="product-name">
                                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                                    </div>
                                                    <div class="small">
                                                        <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>

                                        <td>NT$ <?php echo number_format($item['price']); ?></td>

                                        <td>
                                            <span class="badge type">
                                                <?php echo htmlspecialchars($item['item_type']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge <?php echo $item['status'] == 'Available' ? 'available' : 'unavailable'; ?>">
                                                <?php echo htmlspecialchars($item['status']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if((int)$item['is_best_seller'] === 1): ?>
                                                <span class="badge best">Best</span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="action-group">
                                                <a class="btn secondary" href="manage_menu.php?edit=<?php echo $item['menu_id']; ?>">
                                                    Edit
                                                </a>

                                                <a 
                                                    class="btn danger" 
                                                    href="manage_menu.php?delete=<?php echo $item['menu_id']; ?>"
                                                    onclick="return confirm('Delete this menu item?');"
                                                >
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No menu items found.</td>
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