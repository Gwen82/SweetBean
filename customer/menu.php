<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) ? true : false;
$js_logged_in = $is_logged_in ? 'true' : 'false';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'ALL';

$where = "WHERE 1";

if ($search !== '') {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND menu.product_name LIKE '%$search_safe%'";
}

if ($category === 'Best Seller') {
    $where .= " AND menu.is_best_seller = 1";
} elseif ($category !== 'ALL') {
    $category_safe = mysqli_real_escape_string($conn, $category);
    $where .= " AND categories.category_name = '$category_safe'";
}

$menus = mysqli_query($conn, "
    SELECT menu.*, categories.category_name
    FROM menu
    LEFT JOIN categories ON menu.category_id = categories.category_id
    $where
");

$categories = mysqli_query($conn, "SELECT * FROM categories");

$cart_count = 0;
$cart_total = 0;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += (int)($item['qty'] ?? 0);
        $cart_total += $item['price'] * $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sweet Bean Coffee - Menu</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
:root {
    --primary-color: #6f4e37;
    --primary-hover: #5a3d2a;
    --bg-color: #fdfaf7;
    --text-main: #2d2219;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
}

body {
    background: var(--bg-color);
    color: var(--text-main);
    padding-bottom: 120px;
}

.main-wrapper {
    max-width: 1280px;
    margin: 30px auto;
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(111,78,55,0.08);
    overflow: hidden;
}

main {
    padding: 50px;
}

.page-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 800;
}

.page-subtitle {
    text-align: center;
    color: #8c7a6b;
    margin: 8px 0 30px;
}

.search-box {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 28px;
}

.search-box input {
    width: 360px;
    padding: 13px 18px;
    border: 1px solid rgba(111,78,55,0.2);
    border-radius: 14px;
}

.search-box button {
    padding: 13px 22px;
    border: none;
    border-radius: 14px;
    background: var(--primary-color);
    color: white;
    cursor: pointer;
}

.filter-tabs {
    display: flex;
    justify-content: center;
    gap: 10px;
    background: #f5ebe0;
    padding: 8px;
    border-radius: 16px;
    width: fit-content;
    margin: 0 auto 45px;
    flex-wrap: wrap;
}

.tab-btn {
    text-decoration: none;
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 12px;
    color: #6f5d50;
}

.tab-btn.active,
.tab-btn:hover {
    background: white;
    color: var(--primary-color);
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(111,78,55,0.08);
    box-shadow: 0 4px 15px rgba(111,78,55,0.03);
    transition: 0.3s;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 25px rgba(111,78,55,0.1);
}

.image-container {
    aspect-ratio: 1 / 1;
    background: #faf6f2;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-container i {
    font-size: 2.5rem;
    color: #d6c7b8;
}

.product-info {
    padding: 18px;
}

.product-tag {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #a88770;
}

.product-name {
    display: block;
    font-size: 1.05rem;
    font-weight: 600;
    margin-top: 5px;
}

.product-price {
    display: block;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-top: 8px;
}

/* --- MODAL --- */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-box {
    width: 370px;
    background: white;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

.modal-box h2 {
    color: var(--primary-color);
    margin-bottom: 8px;
}

.modal-box p {
    margin-bottom: 18px;
    color: #8c7a6b;
}

.modal-box select,
.modal-box input {
    width: 100%;
    padding: 11px;
    border: 1px solid rgba(111,78,55,0.2);
    border-radius: 12px;
    margin-bottom: 12px;
}

.add-cart-btn {
    width: 100%;
    padding: 13px;
    border: none;
    background: var(--primary-color);
    color: white;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

/* Gaya khusus tombol login saat guest */
.login-continue-btn {
    background: #e67e22; /* Warna oranye/warning agar mencolok */
}
.login-continue-btn:hover {
    background: #d35400;
}

.close-btn {
    margin-top: 10px;
    background: #eee;
    color: #333;
}

.empty-message {
    grid-column: 1 / -1;
    text-align: center;
    color: #8c7a6b;
    padding: 40px;
}

.bottom-cart-bar {
    position: fixed;
    left: 50%;
    bottom: 25px;
    transform: translateX(-50%);
    width: 90%;
    max-width: 850px;
    background: #6f4e37;
    color: white;
    padding: 18px 24px;
    border-radius: 24px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    display: <?php echo $cart_count > 0 ? 'flex' : 'none'; ?>;
    justify-content: space-between;
    align-items: center;
    z-index: 99999;
}

.bottom-cart-bar span {
    margin-left: 15px;
    font-weight: 700;
}

.bottom-actions {
    display: flex;
    gap: 12px;
}

.bottom-actions a {
    text-decoration: none;
    background: white;
    color: #6f4e37;
    padding: 10px 18px;
    border-radius: 18px;
    font-weight: 800;
}

.bottom-actions .checkout-btn {
    background: #f5d7b5;
}

@media(max-width: 1200px) {
    .menu-grid { grid-template-columns: repeat(4, 1fr); }
}

@media(max-width: 900px) {
    .menu-grid { grid-template-columns: repeat(2, 1fr); }
}

@media(max-width: 500px) {
    .menu-grid { grid-template-columns: 1fr; }
    main { padding: 25px; }
    .search-box { flex-direction: column; }
    .search-box input { width: 100%; }
    .bottom-cart-bar {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
}
</style>
</head>

<body>

<?php 
include __DIR__ . '/../navbar.php'; 
?>

<div class="main-wrapper">
<main>
    <h1 class="page-title">Our Menu</h1>
    <p class="page-subtitle">Freshly roasted coffee and homemade treats delivered to your table.</p>

    <form method="GET" action="menu.php" class="search-box">
        <input 
            type="text" 
            name="search" 
            placeholder="Search your favorite menu..." 
            value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
    </form>

    <div class="filter-tabs">
        <a class="tab-btn <?php echo $category === 'ALL' ? 'active' : ''; ?>" href="menu.php?category=ALL">
            ALL
        </a>

        <a class="tab-btn <?php echo $category === 'Best Seller' ? 'active' : ''; ?>" href="menu.php?category=Best Seller">
            Best Seller
        </a>

        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
            <?php if($cat['category_name'] !== 'Best Seller'): ?>
                <a 
                    class="tab-btn <?php echo $category === $cat['category_name'] ? 'active' : ''; ?>"
                    href="menu.php?category=<?php echo urlencode($cat['category_name']); ?>"
                >
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </a>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>

    <div class="menu-grid">
        <?php if($menus && mysqli_num_rows($menus) > 0): ?>
            <?php while($item = mysqli_fetch_assoc($menus)): ?>
                <?php $isDrink = (($item['item_type'] ?? 'Food') === 'Drink'); ?>

                <div 
                    class="product-card"
                    onclick="openModal(
                        '<?php echo $item['menu_id']; ?>',
                        '<?php echo htmlspecialchars($item['product_name'], ENT_QUOTES); ?>',
                        '<?php echo $item['price']; ?>',
                        '<?php echo $isDrink ? '1' : '0'; ?>'
                    )"
                >
                    <div class="image-container">
                        <?php if(!empty($item['image'])): ?>
                            <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <?php else: ?>
                            <i class="fa-solid <?php echo $isDrink ? 'fa-mug-hot' : 'fa-cookie-bite'; ?>"></i>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <span class="product-tag">
                            <?php echo strtoupper(htmlspecialchars($item['category_name'] ?: 'Uncategorized')); ?>
                        </span>
                        <span class="product-name">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </span>
                        <span class="product-price">
                            NT$ <?php echo number_format($item['price']); ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-message">
                No menu found.
            </div>
        <?php endif; ?>
    </div>
</main>
</div>

<!-- MODAL BOX -->
<div class="modal" id="orderModal">
    <div class="modal-box">
        <h2 id="modalName"></h2>
        <p id="modalPrice"></p>

        <form id="addCartForm">
            <input type="hidden" name="menu_id" id="modalMenuId">

            <div id="drinkOptions">
                <label>Ice Level</label>
                <select name="ice_level" id="iceLevel">
                    <option value="Normal Ice">Normal Ice</option>
                    <option value="Less Ice">Less Ice</option>
                    <option value="No Ice">No Ice</option>
                    <option value="Hot">Hot</option>
                </select>

                <label>Sugar Level</label>
                <select name="sugar_level" id="sugarLevel">
                    <option value="100%">Sugar 100%</option>
                    <option value="75%">Sugar 75%</option>
                    <option value="50%">Sugar 50%</option>
                    <option value="25%">Sugar 25%</option>
                    <option value="0%">Sugar 0%</option>
                </select>
            </div>

            <label>Quantity</label>
            <input type="number" name="quantity" value="1" min="1" max="20" required>

            <!-- TOMBOL ADAPTIF BERDASARKAN SESSION -->
            <?php if ($is_logged_in): ?>
                <button type="submit" name="add_cart" class="add-cart-btn">
                    <i class="fa-solid fa-plus"></i> Add to Cart
                </button>
            <?php else: ?>
                <button type="submit" name="add_cart" class="add-cart-btn login-continue-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Login to Continue
                </button>
            <?php endif; ?>

            <button type="button" class="add-cart-btn close-btn" onclick="closeModal()">
                Cancel
            </button>
        </form>
    </div>
</div>

<!-- BOTTOM BAR CART (Hanya muncul jika cart ada isinya) -->
<div class="bottom-cart-bar" id="bottomCartBar">
    <div>
        <strong id="bottomCartCount"><?php echo $cart_count; ?> item<?php echo $cart_count > 1 ? 's' : ''; ?></strong>
        <span id="bottomCartTotal">NT$ <?php echo number_format($cart_total); ?></span>
    </div>

    <div class="bottom-actions">
        <a href="customer_cart.php">Go to Cart</a>
        <a href="checkout.php" class="checkout-btn">Checkout</a>
    </div>
</div>

<script>
const isLoggedIn = <?php echo $js_logged_in; ?>;

function openModal(id, name, price, isDrink) {
    document.getElementById('modalMenuId').value = id;
    document.getElementById('modalName').innerText = name;
    document.getElementById('modalPrice').innerText = 'NT$ ' + Number(price).toLocaleString();

    const drinkOptions = document.getElementById('drinkOptions');
    const iceLevel = document.getElementById('iceLevel');
    const sugarLevel = document.getElementById('sugarLevel');

    if (isDrink === '1') {
        drinkOptions.style.display = 'block';
        iceLevel.disabled = false;
        sugarLevel.disabled = false;
    } else {
        drinkOptions.style.display = 'none';
        iceLevel.disabled = true;
        sugarLevel.disabled = true;
    }

    document.getElementById('orderModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

document.getElementById('addCartForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // JIKA USER BELUM LOGIN, LANGSUNG REDIRECT KE HALAMAN LOGIN
    if (!isLoggedIn) {
        window.location.href = '../auth/login.php';
        return;
    }

    const formData = new FormData(this);

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Failed to add item.');
            return;
        }

        closeModal();

        document.getElementById('bottomCartBar').style.display = 'flex';

        document.getElementById('bottomCartCount').innerText =
            data.cart_count + ' item' + (data.cart_count > 1 ? 's' : '');

        document.getElementById('bottomCartTotal').innerText =
            'NT$ ' + Number(data.cart_total).toLocaleString();

        const navCart = document.getElementById('navCartCount');
        if(navCart) {
            navCart.style.display = 'flex';
            navCart.innerText = data.cart_count;
        }
    })
    .catch(() => {
        alert('Something went wrong.');
    });
});

window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

</body>
</html>