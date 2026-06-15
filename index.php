<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sweet Bean Coffee</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: #FBF9F6; /* Warna cream-white super lembut ala café Jepang */
            color: #3E362E; /* Cokelat sangat gelap, lebih premium dibanding hitam pekat */
            line-height: 1.7;
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            color: #2A1A0F;
        }

        /* Container Pembatas */
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* 1. HERO SECTION */
        .hero-section {
            padding: 100px 0 60px 0;
            text-align: center;
        }

        .hero-badge {
            background: #EFEAE4;
            color: #8B5E3C;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 25px;
        }

        .hero-title {
            font-size: 56px;
            line-height: 1.15;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-title em {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            color: #8B5E3C;
            font-weight: 400;
        }

        .hero-subtitle {
            font-size: 18px;
            color: #70655B;
            max-width: 580px;
            margin: 0 auto 40px auto;
            font-weight: 300;
        }

        .btn-primary {
            display: inline-block;
            padding: 16px 40px;
            background: #2A1A0F;
            color: #FFF;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 25px rgba(42, 26, 15, 0.15);
        }

        .btn-primary:hover {
            background: #8B5E3C;
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(139, 94, 60, 0.25);
        }

        /* 2. FEATURED PRODUCTS (GRID SYSTEM) */
        .featured-section {
            padding: 100px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 38px;
            margin-bottom: 10px;
        }

        .section-header p {
            color: #8B5E3C;
            font-style: italic;
            font-family: 'Playfair Display', serif;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .menu-card {
            background: #FFF;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(139, 94, 60, 0.03);
            border: 1px solid rgba(139, 94, 60, 0.05);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .menu-img-wrapper {
            width: 100%;
            height: 240px;
            overflow: hidden;
            position: relative;
        }

        .menu-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .menu-info {
            padding: 30px;
        }

        .menu-info h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .menu-info p {
            color: #70655B;
            font-size: 14px;
            font-weight: 300;
        }

        /* Card Hover Effect */
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(139, 94, 60, 0.08);
            border-color: rgba(139, 94, 60, 0.15);
        }

        .menu-card:hover .menu-img-wrapper img {
            transform: scale(1.08);
        }

        /* 3. ABOUT SECTION */
        .about-section {
            background: #F3ECE4; /* Warna cokelat susu sangat pastel */
            padding: 120px 0;
            border-radius: 60px;
            margin: 20px;
            text-align: center;
        }

        .about-content {
            max-width: 750px;
            margin: 0 auto;
        }

        .about-content h2 {
            font-size: 40px;
            margin-bottom: 25px;
        }

        .about-content p {
            font-size: 17px;
            color: #554C43;
            font-weight: 300;
            line-height: 1.9;
        }

        /* 4. CONTACT SECTION */
        .contact-section {
            padding: 100px 0;
            text-align: center;
        }

        .contact-grid {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .contact-card {
            background: #FFF;
            padding: 25px 40px;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 5px 20px rgba(0,0,0,0.01);
            width: 280px;
        }

        .contact-card span {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8B5E3C;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .contact-card p {
            font-size: 15px;
            color: #2A1A0F;
            font-weight: 500;
        }

        footer {
            padding: 40px 0;
            text-align: center;
            font-size: 13px;
            color: #A0948A;
            border-top: 1px solid #EFEAE4;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <section class="hero-section">
        <div class="container">
            <div class="hero-badge">Premium Coffee Experience</div>
            <h1 class="hero-title">Where every bean tells a <br><em>beautiful story</em></h1>
            <p class="hero-subtitle">
                Awaken your senses with our artisanal coffee, handcrafted pastries, and a seamless digital café experience.
            </p>
            <a href="customer/menu.php" class="btn-primary">Explore Our Menu</a>
        </div>
    </section>

    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Seasonal Favorites</h2>
                <p>Meticulously crafted, just for you</p>
            </div>

            <div class="menu-grid">
                <div class="menu-card">
                    <div class="menu-img-wrapper">
                        <img src="https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Creamy Latte">
                    </div>
                    <div class="menu-info">
                        <h3>Velvet Latte</h3>
                        <p>Rich espresso blended with silky smooth steamed milk and a hint of vanilla sweetness.</p>
                    </div>
                </div>

                <div class="menu-card">
                    <div class="menu-img-wrapper">
                        <img src="https://images.pexels.com/photos/1126359/pexels-photo-1126359.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Cheesecake">
                    </div>
                    <div class="menu-info">
                        <h3>Classic Basque</h3>
                        <p>Rich, creamy baked cheesecake with a beautifully caramelized burnt top crust.</p>
                    </div>
                </div>

                <div class="menu-card">
                    <div class="menu-img-wrapper">
                        <img src="https://images.pexels.com/photos/372851/pexels-photo-372851.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Croissant">
                    </div>
                    <div class="menu-info">
                        <h3>Butter Croissant</h3>
                        <p>Flaky, golden layers of French butter pastry baked fresh every single morning.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <h2>The Sweet Bean Way</h2>
                <p>
                    Born out of a simple love for slow mornings and rich aromas, Sweet Bean Coffee is a sanctuary for coffee enthusiasts. We source our beans ethically from single-origin farms and roast them to perfection. Combined with our artisan bakes, we invite you to take a pause, take a sip, and savor the moment.
                </p>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <h2>Let's Connect</h2>
            <p style="color: #70655B; font-weight: 300;">We'd love to hear from you. Visit us or drop a message!</p>
            
            <div class="contact-grid">
                <div class="contact-card">
                    <span>Email Support</span>
                    <p>hello@sweetbean.com</p>
                </div>
                <div class="contact-card">
                    <span>Our Phone</span>
                    <p>0912-345-678</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2026 Sweet Bean Coffee. Made with love for premium tastes.</p>
        </div>
    </footer>

</body>

</html>
