<?php

function sweetbean_default_menu_items(): array
{
    return [
        [
            'id' => 'classic-espresso',
            'name' => 'Classic Espresso',
            'category' => 'Drinks',
            'price' => 3.50,
            'description' => 'A bold, smooth single shot with a caramel finish.',
            'badge' => 'Best Seller',
            'icon' => 'fa-mug-saucer',
            'is_available' => true,
            'sort_order' => 10,
        ],
        [
            'id' => 'vanilla-latte',
            'name' => 'Vanilla Latte',
            'category' => 'Drinks',
            'price' => 4.75,
            'description' => 'Steamed milk, espresso, and house vanilla syrup.',
            'badge' => 'Customer Pick',
            'icon' => 'fa-mug-hot',
            'is_available' => true,
            'sort_order' => 20,
        ],
        [
            'id' => 'iced-caramel-macchiato',
            'name' => 'Iced Caramel Macchiato',
            'category' => 'Drinks',
            'price' => 5.25,
            'description' => 'Chilled espresso layered with milk and caramel drizzle.',
            'badge' => 'Best Seller',
            'icon' => 'fa-glass-water',
            'is_available' => true,
            'sort_order' => 30,
        ],
        [
            'id' => 'mocha-frappe',
            'name' => 'Mocha Frappe',
            'category' => 'Drinks',
            'price' => 5.50,
            'description' => 'Blended coffee, cocoa, whipped cream, and chocolate.',
            'badge' => 'Cold',
            'icon' => 'fa-blender',
            'is_available' => true,
            'sort_order' => 40,
        ],
        [
            'id' => 'strawberry-shortcake',
            'name' => 'Strawberry Shortcake',
            'category' => 'Cakes',
            'price' => 6.25,
            'description' => 'Soft sponge cake with fresh strawberries and cream.',
            'badge' => 'Fresh',
            'icon' => 'fa-cake-candles',
            'is_available' => true,
            'sort_order' => 50,
        ],
        [
            'id' => 'chocolate-ganache-cake',
            'name' => 'Chocolate Ganache Cake',
            'category' => 'Cakes',
            'price' => 6.75,
            'description' => 'Rich chocolate layers finished with glossy ganache.',
            'badge' => 'Best Seller',
            'icon' => 'fa-cake-candles',
            'is_available' => true,
            'sort_order' => 60,
        ],
        [
            'id' => 'butter-croissant',
            'name' => 'Butter Croissant',
            'category' => 'Pastries',
            'price' => 3.25,
            'description' => 'Flaky, golden pastry baked fresh each morning.',
            'badge' => 'Baked Today',
            'icon' => 'fa-cookie-bite',
            'is_available' => true,
            'sort_order' => 70,
        ],
        [
            'id' => 'cinnamon-roll',
            'name' => 'Cinnamon Roll',
            'category' => 'Pastries',
            'price' => 4.00,
            'description' => 'Warm cinnamon swirl topped with vanilla glaze.',
            'badge' => 'Warm',
            'icon' => 'fa-bread-slice',
            'is_available' => true,
            'sort_order' => 80,
        ],
        [
            'id' => 'blueberry-muffin',
            'name' => 'Blueberry Muffin',
            'category' => 'Pastries',
            'price' => 3.75,
            'description' => 'Tender muffin packed with blueberries and crumb topping.',
            'badge' => 'Fresh',
            'icon' => 'fa-cookie',
            'is_available' => true,
            'sort_order' => 90,
        ],
    ];
}

function sweetbean_get_menu_items(?PDO $conn = null): array
{
    if (!$conn) {
        return sweetbean_default_menu_items();
    }

    try {
        $stmt = $conn->query("
            SELECT id, name, category, price, description, badge, icon, is_available, sort_order
            FROM menu_items
            WHERE is_available = true
            ORDER BY sort_order, name
        ");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $items ?: sweetbean_default_menu_items();
    } catch (Throwable $error) {
        error_log('Menu load failed: ' . $error->getMessage());
        return sweetbean_default_menu_items();
    }
}

function sweetbean_find_menu_item(string $id, ?PDO $conn = null): ?array
{
    if ($conn) {
        try {
            $stmt = $conn->prepare("
                SELECT id, name, category, price, description, badge, icon, is_available, sort_order
                FROM menu_items
                WHERE id = ? AND is_available = true
                LIMIT 1
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                return $item;
            }
        } catch (Throwable $error) {
            error_log('Menu item lookup failed: ' . $error->getMessage());
        }
    }

    foreach (sweetbean_default_menu_items() as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }

    return null;
}
