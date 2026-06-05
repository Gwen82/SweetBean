/*blom mskin database*/
<?php 
$menu_items = [
    ["name" => "Classic Espresso", "price" => "$3.50", "tag" => "Drinks"]
];
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Coffee - Premium Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6f4e37;
            --primary-hover: #5a3d2a;
            --bg-color: #fdfaf7;
            --card-bg: #ffffff;
            --text-main: #2d2219;
            --accent-color: #e6ccb2;
            --cart-count: "0"; /* Handled dynamically by CSS/JS later */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding: 30px 15px;
        }

        .main-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(111, 78, 55, 0.08);
            overflow: hidden;
            border: 1px solid rgba(111, 78, 55, 0.05);
        }

        /* --- Header Upgrades --- */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 50px;
            border-bottom: 1px solid rgba(111, 78, 55, 0.1);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left, .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-placeholder {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background-image: url('logo.jpg'); /* Your custom logo image */
            background-size: cover;
            background-position: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 2px solid var(--primary-color);
        }

        .brand-name {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: -0.5px;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 35px;
        }

        nav a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s, transform 0.2s;
        }

        nav a:hover {
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        /* Upgraded Shopping Cart Icon with Badge Counter */
        .cart-wrapper {
            position: relative;
            cursor: pointer;
            padding: 5px;
        }

        .cart-icon {
            font-size: 1.4rem;
            color: var(--text-main);
            transition: color 0.2s;
        }

        .cart-wrapper::after {
            content: var(--cart-count);
            position: absolute;
            top: -5px;
            right: -8px;
            background: #de4e4e;
            color: white;
            font-size: 11px;
            font-weight: bold;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Upgraded Profile input/label combo (No JS) */
        .profile-container {
            position: relative;
        }

        .hidden-input {
            display: none;
        }

        .profile-placeholder {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: #f0e6df;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(111, 78, 55, 0.2);
            overflow: hidden;
        }

        .profile-placeholder:hover {
            transform: scale(1.05);
            background-color: var(--accent-color);
        }

        .profile-icon {
            font-size: 1.4rem;
            color: var(--primary-color);
        }

        /* CSS magic hiding icon when file uploaded */
        .hidden-input:valid ~ .profile-placeholder .profile-icon {
            display: none;
        }
        .hidden-input:valid ~ .profile-placeholder::after {
            content: "\f00c"; /* FontAwesome Checkmark */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #2b8a3e;
            font-size: 1.1rem;
        }

        /* --- Main / Menu Section --- */
        main {
            padding: 50px;
        }

        .page-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .page-subtitle {
            text-align: center;
            color: #8c7a6b;
            font-size: 1rem;
            margin-bottom: 40px;
        }

        /* --- Modern Segmented Control Filter Tabs --- */
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 50px;
            background: #f5ebe0;
            padding: 8px;
            border-radius: 16px;
            max-width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .tab-btn {
            background: transparent;
            border: none;
            padding: 10px 24px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            color: #6f5d50;
            transition: all 0.2s ease;
        }

        .tab-btn.active, .tab-btn:hover {
            background-color: var(--card-bg);
            color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(111, 78, 55, 0.1);
        }

        /* --- Grid & Modern Cards --- */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 25px;
        }

        .product-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(111, 78, 55, 0.08);
            box-shadow: 0 4px 15px rgba(111, 78, 55, 0.03);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(111, 78, 55, 0.1);
        }

        /* Styled Placeholder Graphic Box */
        .image-container {
            aspect-ratio: 1 / 1;
            background: #faf6f2;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dcd0c4;
        }

        /* Faded decorative background X inside the item box */
        .image-container::before, .image-container::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(111,78,55,0.08), transparent);
        }
        .image-container::before { transform: rotate(45deg); }
        .image-container::after { transform: rotate(-45deg); }

        .image-container i.coffee-icon {
            font-size: 2.5rem;
            z-index: 1;
            opacity: 0.6;
        }

        .product-info {
            padding: 18px;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
        }

        .product-tag {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #a88770;
            letter-spacing: 0.5px;
        }

        .product-name {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .product-price {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: auto;
            padding-top: 8px;
        }

        /* Modernized Elegant Add Button */
        .add-cart-btn {
            width: calc(100% - 36px);
            margin: 0 auto 18px auto;
            padding: 12px;
            background: #fcf8f5;
            border: 1px solid rgba(111, 78, 55, 0.15);
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-color);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .add-cart-btn:hover {
            background: var(--primary-color);
            color: #ffffff;
            border-color: var(--primary-color);
        }

        /* --- Responsive Viewports --- */
        @media (max-width: 1200px) {
            .menu-grid { grid-template-columns: repeat(4, 1fr); }
        }
        @media (max-width: 992px) {
            .menu-grid { grid-template-columns: repeat(3, 1fr); }
            header { padding: 20px 30px; }
        }
        @media (max-width: 768px) {
            header { flex-direction: column; gap: 20px; }
            .menu-grid { grid-template-columns: repeat(2, 1fr); }
            main { padding: 30px 15px; }
        }
        @media (max-width: 480px) {
            .menu-grid { grid-template-columns: 1fr; }
            nav { gap: 15px; }
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        
        <header>
            <div class="header-left">
                <img src="assets/LOGO.jpg" alt="Sweet Bean Coffee Logo" class="logo-placeholder"></img>
                <span class="brand-name">Sweet Bean Coffee</span>
            </div>
            <div class="header-right">
                <nav>
                    <div class="cart-wrapper" id="cartBtn">
                        <i class="fa-solid fa-bag-shopping cart-icon"></i>
                    </div>
                    <a href="#contact">Contact</a>
                    <a href="#about">About us</a>
                    <a href="#home">Home</a>
                </nav>
                
               <div class="profile-container">
                    <a href="auth/register.php" class="profile-placeholder">
                        <i class="fa-regular fa-circle-user profile-icon"></i>
                    </a>
                </div>
            </div>
        </header>

        <main>
            <h1 class="page-title">Our Menu</h1>
            <p class="page-subtitle">Freshly roasted coffee and homemade treats delivered to your table.</p>

            <div class="filter-tabs">
                <button class="tab-btn active">ALL</button>
                <button class="tab-btn">Best Seller</button>
                <button class="tab-btn">Drinks</button>
                <button class="tab-btn">Cakes</button>
                <button class="tab-btn">Pastries</button>
            </div>

            <div class="menu-grid">
                <?php foreach ($menu_items as $item): ?>
                    <div class="product-card">
                        <div class="image-container">
                            <i class="fa-solid fa-mug-hot coffee-icon"></i>
                        </div>
                        <div class="product-info">
                            <span class="product-tag"><?php echo $item['tag']; ?></span>
                            <span class="product-name"><?php echo $item['name']; ?></span>
                            <span class="product-price"><?php echo $item['price']; ?></span>
                        </div>
                        <button class="add-cart-btn" onclick="increaseCart()">
                            <i class="fa-solid fa-plus"></i> Add to Cart
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        let count = 0;
        function increaseCart() {
            count++;
            document.documentElement.style.setProperty('--cart-count', `"${count}"`);
        }
    </script>
</body>
</html>

