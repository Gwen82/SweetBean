<?php
require_once __DIR__ . '/../config/session.php';

$menuItems = [
    [
        'name' => 'Classic Espresso',
        'category' => 'Drinks',
        'price' => 3.50,
        'description' => 'A bold, smooth single shot with a caramel finish.',
        'badge' => 'Best Seller',
        'icon' => 'fa-mug-saucer',
    ],
    [
        'name' => 'Vanilla Latte',
        'category' => 'Drinks',
        'price' => 4.75,
        'description' => 'Steamed milk, espresso, and house vanilla syrup.',
        'badge' => 'Customer Pick',
        'icon' => 'fa-mug-hot',
    ],
    [
        'name' => 'Iced Caramel Macchiato',
        'category' => 'Drinks',
        'price' => 5.25,
        'description' => 'Chilled espresso layered with milk and caramel drizzle.',
        'badge' => 'Best Seller',
        'icon' => 'fa-glass-water',
    ],
    [
        'name' => 'Mocha Frappe',
        'category' => 'Drinks',
        'price' => 5.50,
        'description' => 'Blended coffee, cocoa, whipped cream, and chocolate.',
        'badge' => 'Cold',
        'icon' => 'fa-blender',
    ],
    [
        'name' => 'Strawberry Shortcake',
        'category' => 'Cakes',
        'price' => 6.25,
        'description' => 'Soft sponge cake with fresh strawberries and cream.',
        'badge' => 'Fresh',
        'icon' => 'fa-cake-candles',
    ],
    [
        'name' => 'Chocolate Ganache Cake',
        'category' => 'Cakes',
        'price' => 6.75,
        'description' => 'Rich chocolate layers finished with glossy ganache.',
        'badge' => 'Best Seller',
        'icon' => 'fa-cake-candles',
    ],
    [
        'name' => 'Butter Croissant',
        'category' => 'Pastries',
        'price' => 3.25,
        'description' => 'Flaky, golden pastry baked fresh each morning.',
        'badge' => 'Baked Today',
        'icon' => 'fa-cookie-bite',
    ],
    [
        'name' => 'Cinnamon Roll',
        'category' => 'Pastries',
        'price' => 4.00,
        'description' => 'Warm cinnamon swirl topped with vanilla glaze.',
        'badge' => 'Warm',
        'icon' => 'fa-bread-slice',
    ],
    [
        'name' => 'Blueberry Muffin',
        'category' => 'Pastries',
        'price' => 3.75,
        'description' => 'Tender muffin packed with blueberries and crumb topping.',
        'badge' => 'Fresh',
        'icon' => 'fa-cookie',
    ],
];

$categories = array_values(array_unique(array_column($menuItems, 'category')));
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Sweet Bean Coffee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --coffee: #5a3825;
            --coffee-dark: #2d1d16;
            --cream: #fff8ef;
            --latte: #ead8c4;
            --sage: #6b8068;
            --berry: #9b4f5f;
            --ink: #2a211b;
            --muted: #806e62;
            --line: rgba(90, 56, 37, 0.14);
            --card: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--cream);
            color: var(--ink);
            font-family: "Poppins", Arial, sans-serif;
        }

        .menu-page {
            padding: 42px 22px 70px;
            background:
                linear-gradient(180deg, rgba(255, 248, 239, 0.2), rgba(247, 239, 230, 0.72)),
                radial-gradient(circle at 10% 10%, rgba(107, 128, 104, 0.16), transparent 28%),
                radial-gradient(circle at 92% 24%, rgba(155, 79, 95, 0.12), transparent 30%);
        }

        .menu-shell {
            width: min(1180px, 100%);
            margin: 0 auto;
        }

        .menu-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 28px;
            align-items: end;
            padding: 34px 0 34px;
            border-bottom: 1px solid var(--line);
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--sage);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            color: var(--coffee-dark);
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(2.3rem, 6vw, 4.8rem);
            line-height: 0.96;
            letter-spacing: 0;
        }

        .hero-copy {
            max-width: 620px;
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .menu-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(120px, 1fr));
            gap: 12px;
        }

        .stat-tile {
            min-width: 142px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fffdf9;
        }

        .stat-value {
            display: block;
            color: var(--coffee);
            font-size: 1.65rem;
            font-weight: 700;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.82rem;
        }

        .menu-tools {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) auto;
            gap: 18px;
            align-items: center;
            padding: 24px 0;
        }

        .search-field {
            position: relative;
        }

        .search-field i {
            position: absolute;
            top: 50%;
            left: 16px;
            color: var(--muted);
            transform: translateY(-50%);
        }

        .search-field input {
            width: 100%;
            height: 48px;
            padding: 0 18px 0 46px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fffdf9;
            color: var(--ink);
            font: inherit;
            outline: none;
        }

        .search-field input:focus {
            border-color: var(--sage);
            box-shadow: 0 0 0 4px rgba(107, 128, 104, 0.13);
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .category-tab {
            height: 40px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fffdf9;
            color: var(--coffee);
            cursor: pointer;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .category-tab.is-active,
        .category-tab:hover {
            border-color: var(--coffee);
            background: var(--coffee);
            color: #fffaf4;
        }

        .cart-strip {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 22px;
            padding: 14px 16px;
            border: 1px solid rgba(107, 128, 104, 0.22);
            border-radius: 8px;
            background: #f6faf3;
            color: #40583d;
        }

        .cart-strip.auth-needed {
            border-color: rgba(155, 79, 95, 0.22);
            background: #fff4f4;
            color: #74404d;
        }

        .cart-strip strong {
            color: #2f472c;
        }

        .cart-strip-link {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 8px;
            background: var(--sage);
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
        }

        .cart-strip-link:hover {
            background: #526b4f;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .menu-card {
            display: flex;
            min-height: 100%;
            overflow: hidden;
            flex-direction: column;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--card);
            box-shadow: 0 14px 35px rgba(70, 42, 25, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            border-color: rgba(90, 56, 37, 0.28);
            box-shadow: 0 22px 44px rgba(70, 42, 25, 0.1);
        }

        .menu-art {
            display: grid;
            place-items: center;
            width: 100%;
            height: 154px;
            border-radius: 0;
            background:
                linear-gradient(135deg, rgba(234, 216, 196, 0.78), rgba(255, 248, 239, 0.96)),
                radial-gradient(circle at 28% 20%, rgba(155, 79, 95, 0.22), transparent 38%),
                radial-gradient(circle at 76% 80%, rgba(107, 128, 104, 0.18), transparent 36%);
            color: var(--coffee);
            font-size: 3.35rem;
        }

        .menu-info {
            display: flex;
            flex: 1;
            flex-direction: column;
            min-width: 0;
            padding: 18px;
        }

        .menu-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 3px 9px;
            border-radius: 999px;
            background: #f7efe6;
            color: var(--coffee);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .pill.accent {
            background: rgba(155, 79, 95, 0.12);
            color: var(--berry);
        }

        .item-name {
            margin: 0;
            color: var(--coffee-dark);
            font-size: 1.08rem;
            line-height: 1.32;
        }

        .item-description {
            margin: 8px 0 16px;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.55;
            flex: 1;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .price {
            color: var(--coffee);
            font-size: 1.05rem;
            font-weight: 800;
        }

        .add-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 8px;
            background: var(--coffee);
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
        }

        .add-btn:hover {
            background: var(--coffee-dark);
        }

        .login-order-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 12px;
            border: 1px solid rgba(90, 56, 37, 0.18);
            border-radius: 8px;
            background: #fff8ef;
            color: var(--coffee);
            font-size: 0.84rem;
            font-weight: 800;
            text-decoration: none;
        }

        .login-order-link:hover {
            border-color: var(--coffee);
            background: #f7efe6;
        }

        .empty-state {
            display: none;
            padding: 36px;
            border: 1px dashed var(--line);
            border-radius: 8px;
            color: var(--muted);
            text-align: center;
        }

        .empty-state.is-visible {
            display: block;
        }

        @media (max-width: 980px) {
            .menu-hero,
            .menu-tools {
                grid-template-columns: 1fr;
            }

            .category-tabs {
                justify-content: flex-start;
            }

            .menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 680px) {
            .menu-page {
                padding: 26px 14px 54px;
            }

            .menu-stats {
                grid-template-columns: 1fr 1fr;
            }

            .cart-strip {
                align-items: flex-start;
                flex-direction: column;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .menu-card {
                min-height: auto;
            }

            .menu-art {
                height: 132px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="menu-page">
        <div class="menu-shell">
            <section class="menu-hero" aria-labelledby="menu-title">
                <div>
                    <p class="eyebrow">Fresh from the counter</p>
                    <h1 id="menu-title">Sweet Bean Menu</h1>
                    <p class="hero-copy">
                        Choose from espresso drinks, chilled cafe favorites, soft cakes, and fresh pastries made for quiet mornings and easy catch-ups.
                    </p>
                </div>
                <div class="menu-stats" aria-label="Menu summary">
                    <div class="stat-tile">
                        <span class="stat-value"><?php echo count($menuItems); ?></span>
                        <span class="stat-label">Menu items</span>
                    </div>
                    <div class="stat-tile">
                        <span class="stat-value"><?php echo count($categories); ?></span>
                        <span class="stat-label">Categories</span>
                    </div>
                </div>
            </section>

            <section class="menu-tools" aria-label="Menu filters">
                <label class="search-field">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="menuSearch" placeholder="Search coffee, cakes, pastries..." autocomplete="off">
                </label>

                <div class="category-tabs" role="group" aria-label="Filter menu by category">
                    <button class="category-tab is-active" type="button" data-filter="All">All</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="category-tab" type="button" data-filter="<?php echo htmlspecialchars($category); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </button>
                    <?php endforeach; ?>
                    <button class="category-tab" type="button" data-filter="Best Seller">Best Seller</button>
                </div>
            </section>

            <section class="cart-strip <?php echo $isLoggedIn ? '' : 'auth-needed'; ?>" aria-live="polite">
                <?php if ($isLoggedIn): ?>
                    <span><strong id="cartCount">0</strong> item(s) selected for your order.</span>
                    <a class="cart-strip-link" href="<?php echo BASE_URL; ?>customer/cart.php">View Cart</a>
                <?php else: ?>
                    <span><strong>Login required.</strong> Please log in before adding menu items to your cart.</span>
                    <a class="cart-strip-link" href="<?php echo BASE_URL; ?>auth/login.php">Login</a>
                <?php endif; ?>
            </section>

            <section class="menu-grid" id="menuGrid" aria-label="Sweet Bean menu items">
                <?php foreach ($menuItems as $item): ?>
                    <?php $itemId = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($item['name'])), '-'); ?>
                    <article
                        class="menu-card"
                        data-category="<?php echo htmlspecialchars($item['category']); ?>"
                        data-badge="<?php echo htmlspecialchars($item['badge']); ?>"
                        data-name="<?php echo htmlspecialchars(strtolower($item['name'] . ' ' . $item['description'])); ?>"
                        data-item-id="<?php echo htmlspecialchars($itemId); ?>"
                        data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
                        data-item-price="<?php echo htmlspecialchars((string) $item['price']); ?>"
                        data-item-description="<?php echo htmlspecialchars($item['description']); ?>"
                        data-item-icon="<?php echo htmlspecialchars($item['icon']); ?>"
                    >
                        <div class="menu-art" aria-hidden="true">
                            <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        </div>
                        <div class="menu-info">
                            <div class="menu-meta">
                                <span class="pill"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="pill accent"><?php echo htmlspecialchars($item['badge']); ?></span>
                            </div>
                            <h2 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h2>
                            <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="card-footer">
                                <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                                <?php if ($isLoggedIn): ?>
                                    <button class="add-btn" type="button" aria-label="Add <?php echo htmlspecialchars($item['name']); ?> to cart">
                                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    </button>
                                <?php else: ?>
                                    <a class="login-order-link" href="<?php echo BASE_URL; ?>auth/login.php">Login to order</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <p class="empty-state" id="emptyState">No menu items match your search.</p>
        </div>
    </main>

    <script>
        const tabs = document.querySelectorAll('.category-tab');
        const cards = document.querySelectorAll('.menu-card');
        const search = document.getElementById('menuSearch');
        const emptyState = document.getElementById('emptyState');
        const cartCount = document.getElementById('cartCount');
        const cartStorageKey = 'sweetBeanCart';
        let activeFilter = 'All';

        function getCart() {
            try {
                return JSON.parse(localStorage.getItem(cartStorageKey)) || [];
            } catch (error) {
                return [];
            }
        }

        function saveCart(cart) {
            localStorage.setItem(cartStorageKey, JSON.stringify(cart));
            window.dispatchEvent(new Event('sweetBeanCartUpdated'));
        }

        function getCartQuantity(cart = getCart()) {
            return cart.reduce((total, item) => total + Number(item.quantity || 0), 0);
        }

        function updateCartCount() {
            if (!cartCount) {
                return;
            }

            cartCount.textContent = getCartQuantity();
        }

        function addToCart(card) {
            const cart = getCart();
            const existingItem = cart.find((item) => item.id === card.dataset.itemId);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: card.dataset.itemId,
                    name: card.dataset.itemName,
                    category: card.dataset.category,
                    price: Number(card.dataset.itemPrice),
                    description: card.dataset.itemDescription,
                    icon: card.dataset.itemIcon,
                    quantity: 1
                });
            }

            saveCart(cart);
            updateCartCount();
        }

        function renderMenu() {
            const query = search.value.trim().toLowerCase();
            let visibleCount = 0;

            cards.forEach((card) => {
                const matchesFilter =
                    activeFilter === 'All' ||
                    card.dataset.category === activeFilter ||
                    card.dataset.badge === activeFilter;
                const matchesSearch = !query || card.dataset.name.includes(query);
                const isVisible = matchesFilter && matchesSearch;

                card.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount++;
                }
            });

            emptyState.classList.toggle('is-visible', visibleCount === 0);
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach((currentTab) => currentTab.classList.remove('is-active'));
                tab.classList.add('is-active');
                activeFilter = tab.dataset.filter;
                renderMenu();
            });
        });

        search.addEventListener('input', renderMenu);

        document.querySelectorAll('.add-btn').forEach((button) => {
            button.addEventListener('click', () => {
                addToCart(button.closest('.menu-card'));
            });
        });

        if (cartCount) {
            updateCartCount();
        }
    </script>
</body>
</html>
