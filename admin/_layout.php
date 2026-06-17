<?php
function admin_nav_items(): array
{
    return [
        ['href' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'fa-chart-line'],
        ['href' => 'menu.php', 'label' => 'Menu', 'icon' => 'fa-mug-hot'],
        ['href' => 'staff.php', 'label' => 'Staff', 'icon' => 'fa-user-tie'],
        ['href' => 'orders.php', 'label' => 'Orders', 'icon' => 'fa-receipt'],
        ['href' => 'reviews.php', 'label' => 'Reviews', 'icon' => 'fa-star'],
        ['href' => 'reports.php', 'label' => 'Reports', 'icon' => 'fa-file-lines'],
    ];
}

function admin_header(string $title, string $subtitle = '', string $actionHtml = ''): void
{
    $current = basename($_SERVER['SCRIPT_NAME']);
    $flash = admin_flash();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title); ?> | Sweet Bean Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/admin.css">
</head>
<body>
<div class="admin-app">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">
            <img src="<?php echo BASE_URL; ?>assets/LOGO.jpg" alt="Sweet Bean Cafe">
            <span>Sweet Bean<br><strong>Admin</strong></span>
        </a>
        <nav class="admin-nav" aria-label="Admin navigation">
            <?php foreach (admin_nav_items() as $item): ?>
                <a class="<?php echo $current === $item['href'] ? 'is-active' : ''; ?>" href="<?php echo BASE_URL . 'admin/' . $item['href']; ?>">
                    <i class="fa-solid <?php echo h($item['icon']); ?>" aria-hidden="true"></i>
                    <?php echo h($item['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?php echo BASE_URL; ?>customer/menu.php"><i class="fa-solid fa-store"></i> Storefront</a>
            <a href="<?php echo BASE_URL; ?>auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="eyebrow">Cafe operations</p>
                <h1><?php echo h($title); ?></h1>
                <?php if ($subtitle !== ''): ?><p><?php echo h($subtitle); ?></p><?php endif; ?>
            </div>
            <?php if ($actionHtml !== ''): ?><div class="topbar-actions"><?php echo $actionHtml; ?></div><?php endif; ?>
        </header>
        <?php if ($flash): ?>
            <div class="alert <?php echo h($flash['type']); ?>"><?php echo h($flash['message']); ?></div>
        <?php endif; ?>
    <?php
}

function admin_footer(): void
{
    ?>
    </main>
</div>
</body>
</html>
    <?php
}
