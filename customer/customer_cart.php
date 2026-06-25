<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. LOGIKA SYNC DARI NAVBAR (Menangani Status Login & Keranjang)
// Memanggil config untuk mendapatkan BASE_URL
include_once __DIR__ . '/../config.php'; 

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Account';

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int)($item['qty'] ?? 0);
    }
}

// 2. LOGIKA ASLI DARI CUSTOMER_CART (Menangani Hapus Item)
$cart = $_SESSION['cart'] ?? [];
$total = 0;

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: customer_cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shopping Cart - Sweet Bean Coffee</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
/* --- CSS GLOBAL & CART --- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
}

body {
    background: #f4eee7;
    color: #3d2a1f;
    min-height: 100vh;
}

.cart-wrapper {
    max-width: 1150px;
    margin: 45px auto;
    background: white;
    border-radius: 28px;
    box-shadow: 0 18px 45px rgba(111,78,55,0.12);
    overflow: hidden;
}

.cart-header {
    padding: 35px;
    text-align: center;
    background: #6f4e37;
    color: white;
}

.cart-header h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.cart-content {
    display: grid;
    grid-template-columns: 1.6fr 0.9fr;
}

.cart-items {
    padding: 35px;
    border-right: 1px solid #eee;
}

.cart-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px;
    margin-bottom: 18px;
    border: 1px solid #eee;
    border-radius: 20px;
    background: #fffaf6;
}

.item-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.item-icon {
    width: 75px;
    height: 75px;
    border-radius: 18px;
    background: #f1e7dc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6f4e37;
    font-size: 30px;
}

.item-name {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 5px;
}

.item-detail {
    font-size: 13px;
    color: #8a7568;
    line-height: 1.6;
}

.item-price {
    text-align: right;
    font-weight: 800;
}

.remove-btn {
    display: inline-block;
    margin-top: 8px;
    color: #c0392b;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

.summary {
    padding: 35px;
    background: #faf7f2;
}

.summary h2 {
    margin-bottom: 25px;
    color: #6f4e37;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
    font-size: 15px;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    margin-top: 18px;
    padding-top: 18px;
    border-top: 2px solid #6f4e37;
    font-size: 22px;
    font-weight: 900;
}

.btn {
    display: block;
    width: 100%;
    text-align: center;
    padding: 15px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 900;
    margin-top: 16px;
}

.btn-primary {
    background: #6f4e37;
    color: white;
}

.btn-secondary {
    background: white;
    color: #6f4e37;
    border: 1.5px solid #6f4e37;
}

.empty-box {
    padding: 60px;
    text-align: center;
}

.empty-box i {
    font-size: 60px;
    color: #c8b8aa;
    margin-bottom: 20px;
}

.empty-box h2 {
    color: #6f4e37;
    margin-bottom: 10px;
}

/* --- CSS STYLING DARI NAVBAR FILE (SYNCED) --- */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 60px;
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.brand-name {
    font-size: 22px;
    font-weight: bold;
    color: #4e342e;
}

.navbar-right nav {
    display: flex;
    align-items: center;
    gap: 25px;
}

.navbar-right a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: 0.3s;
}

.navbar-right a:hover {
    color: #8b5e3c;
}

.navbar-right i {
    font-size: 22px;
}

.cart-nav-link {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.cart-badge {
    position: absolute;
    top: -10px;
    right: -12px;
    display: none;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #9b4f5f;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
}

.cart-badge.is-visible {
    display: inline-block;
}

.account-menu {
    position: relative;
}

.account-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    background: transparent;
    color: #333;
    cursor: pointer;
    font: inherit;
    font-weight: 600;
}

.account-trigger i {
    color: #4e342e;
}

.account-name {
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.account-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    display: none;
    min-width: 210px;
    padding: 8px;
    border: 1px solid rgba(78,52,46,0.12);
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 16px 34px rgba(78,52,46,0.14);
}

.account-menu:hover .account-dropdown,
.account-menu:focus-within .account-dropdown {
    display: block;
}

.navbar-right .account-dropdown a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 8px;
    color: #4e342e;
    font-size: 14px;
    font-weight: 600;
}

.navbar-right .account-dropdown a:hover {
    background: #f7efe6;
}

@media(max-width: 850px) {
    .cart-content {
        grid-template-columns: 1fr;
    }

    .cart-items {
        border-right: none;
    }

    .cart-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .item-price {
        text-align: left;
    }

    .navbar {
        flex-direction: column;
        padding: 15px 20px;
        gap: 15px;
    }

    .navbar-right nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
    }

    .brand-name {
        font-size: 18px;
    }
}
</style>
</head>

<body>

<header class="navbar">
    <div class="navbar-left">
        <img src="<?php echo BASE_URL; ?>assets/LOGO.jpg" alt="Sweet Bean Coffee" class="logo">
        <span class="brand-name">Sweet Bean Coffee</span>
    </div>

    <div class="navbar-right">
        <nav>
            <a href="<?php echo BASE_URL; ?>index.php">Home</a>
            <a href="<?php echo BASE_URL; ?>customer/menu.php">Menu</a>

            <?php if($isLoggedIn): ?>
                <div class="account-menu">
                    <button class="account-trigger" type="button">
                        <i class="fa-regular fa-circle-user"></i>
                        <span class="account-name">
                            <?php echo htmlspecialchars($userName); ?>
                        </span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>

                    <div class="account-dropdown">
                        <a href="<?php echo BASE_URL; ?>customer/profile.php">
                            <i class="fa-regular fa-id-card"></i>
                            Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>customer/my_orders.php">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Order History
                        </a>
                        <a href="<?php echo BASE_URL; ?>auth/logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/login.php">
                    <i class="fa-regular fa-circle-user"></i>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="cart-wrapper">
    <div class="cart-header">
        <h1>Shopping Cart</h1>
        <p>Review your order before checkout</p>
    </div>

    <?php if(empty($cart)): ?>
        <div class="empty-box">
            <i class="fa-solid fa-bag-shopping"></i>
            <h2>Your cart is empty</h2>
            <p>Add your favorite coffee and cakes first.</p>
            <a href="menu.php" class="btn btn-primary">Back to Menu</a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach($cart as $key => $item): ?>
                    <?php
                    $subtotal = $item['price'] * $item['qty'];
                    $total += $subtotal;
                    ?>

                    <div class="cart-card">
                        <div class="item-left">
                            <div class="item-icon">
                                <?php if(($item['ice_level'] ?? 'N/A') !== 'N/A'): ?>
                                    <i class="fa-solid fa-mug-hot"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-cookie-bite"></i>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="item-name">
                                    <?= htmlspecialchars($item['name']); ?>
                                </div>

                                <div class="item-detail">
                                    Qty: <?= $item['qty']; ?> × NT$ <?= number_format($item['price']); ?><br>

                                    <?php if(($item['ice_level'] ?? 'N/A') !== 'N/A'): ?>
                                        Ice: <?= htmlspecialchars($item['ice_level']); ?> |
                                        Sugar: <?= htmlspecialchars($item['sugar_level']); ?>
                                    <?php else: ?>
                                        No drink options
                                    <?php endif; ?>
                                </div>

                                <a class="remove-btn" href="customer_cart.php?remove=<?= urlencode($key); ?>">
                                    Remove item
                                </a>
                            </div>
                        </div>

                        <div class="item-price">
                            NT$ <?= number_format($subtotal); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>NT$ <?= number_format($total); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span>NT$ <?= number_format($total); ?></span>
                </div>

                <a href="checkout.php" class="btn btn-primary">Checkout</a>
                <a href="menu.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>