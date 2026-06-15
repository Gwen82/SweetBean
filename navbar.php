<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$sectionDir = basename($scriptDir);
$appBase = in_array($sectionDir, ['auth', 'customer'], true) ? dirname($scriptDir) : $scriptDir;
$appBase = rtrim(str_replace('\\', '/', $appBase), '/');
if ($appBase === '/' || $appBase === '\\') {
    $appBase = '';
}

$homeUrl = $appBase . '/index.php';
$cartUrl = $appBase . '/customer/customer_cart.php';
$loginUrl = $appBase . '/auth/login.php';
$registerUrl = $appBase . '/auth/register.php';
?>

<!-- Font Awesome -->
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
/* NAVBAR */

.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 60px;
    background:#ffffff;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
    position:sticky;
    top:0;
    z-index:1000;
}

.navbar-left{
    display:flex;
    align-items:center;
    gap:12px;
}

.logo{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}

.brand-name{
    font-size:22px;
    font-weight:bold;
    color:#4e342e;
}

.navbar-right nav{
    display:flex;
    align-items:center;
    gap:25px;
}

.navbar-right a{
    text-decoration:none;
    color:#333;
    font-weight:500;
    transition:0.3s;
}

.navbar-right a:hover{
    color:#8b5e3c;
}

.navbar-right i{
    font-size:22px;
}

@media(max-width:768px){

    .navbar{
        flex-direction:column;
        padding:15px 20px;
        gap:15px;
    }

    .navbar-right nav{
        flex-wrap:wrap;
        justify-content:center;
        gap:15px;
    }

    .brand-name{
        font-size:18px;
    }
}
</style>

<header class="navbar">

    <div class="navbar-left">
        <img src="<?php echo htmlspecialchars($appBase . '/assets/LOGO.jpg'); ?>"
             alt="Sweet Bean Coffee"
             class="logo">

        <span class="brand-name">
            Sweet Bean Coffee
        </span>
    </div>

    <div class="navbar-right">
        <nav>

            <a href="<?php echo htmlspecialchars($homeUrl); ?>">
                Home
            </a>

            <a href="<?php echo htmlspecialchars($homeUrl . '#menu'); ?>">
                Menu
            </a>

            <a href="#">
                About Us
            </a>

            <a href="#">
                Contact
            </a>

            <a href="<?php echo htmlspecialchars($cartUrl); ?>">
                <i class="fa-solid fa-bag-shopping"></i>
            </a>

            <?php if(isset($_SESSION['user_id'])): ?>

                <a href="<?php echo htmlspecialchars($registerUrl); ?>">
                    <i class="fa-regular fa-circle-user"></i>
                </a>

            <?php else: ?>

                <a href="<?php echo htmlspecialchars($loginUrl); ?>">
                    <i class="fa-regular fa-circle-user"></i>
                </a>

            <?php endif; ?>

        </nav>
    </div>

</header>
