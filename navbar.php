<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Account';

$cartCount = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int)($item['qty'] ?? 0);
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
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

@media(max-width: 768px) {
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

<header class="navbar">

    <div class="navbar-left">
        <img src="<?php echo BASE_URL; ?>assets/LOGO.jpg" alt="Sweet Bean Coffee" class="logo">
        <span class="brand-name">Sweet Bean Coffee</span>
    </div>

    <div class="navbar-right">
        <nav>
            <a href="<?php echo BASE_URL; ?>index.php">Home</a>
            <a href="<?php echo BASE_URL; ?>customer/menu.php">Menu</a>
            <a href="<?php echo BASE_URL; ?>index.php#about">About Us</a>
            <a href="<?php echo BASE_URL; ?>index.php#contact">Contact</a>

            <a href="<?php echo BASE_URL; ?>customer/customer_cart.php" class="cart-nav-link">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="cart-badge <?php echo $cartCount > 0 ? 'is-visible' : ''; ?>">
                    <?php echo $cartCount; ?>
                </span>
            </a>

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