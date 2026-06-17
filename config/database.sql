CREATE DATABASE IF NOT EXISTS sweetbean;
USE sweetbean;

DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS info;

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    birthday DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT "customer",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CATEGORIES
-- =========================
CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL
);

-- =========================
-- MENU ITEMS
-- =========================
CREATE TABLE menu_items (
    item_id SERIAL PRIMARY KEY,
    category_id INT REFERENCES categories(category_id),
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url TEXT,
    stock INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE
);

-- =========================
-- CART
-- =========================
CREATE TABLE carts (
    cart_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(user_id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CART ITEMS
-- =========================
CREATE TABLE cart_items (
    cart_item_id SERIAL PRIMARY KEY,
    cart_id INT REFERENCES carts(cart_id) ON DELETE CASCADE,
    item_id INT REFERENCES menu_items(item_id),
    quantity INT NOT NULL DEFAULT 1
);

-- =========================
-- ORDERS
-- =========================
CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(user_id),

    order_type VARCHAR(20) NOT NULL,

    delivery_address TEXT,

    total_price DECIMAL(10,2) NOT NULL,

    payment_method VARCHAR(30),

    payment_status VARCHAR(20) DEFAULT 'paid',

    order_status VARCHAR(30) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ORDER ITEMS
-- =========================
CREATE TABLE order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(order_id) ON DELETE CASCADE,
    item_id INT REFERENCES menu_items(item_id),
    quantity INT NOT NULL,
    price NUMERIC(10,2) NOT NULL
);

-- =========================
-- REVIEWS
-- =========================
CREATE TABLE reviews (
    review_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(user_id),
    order_id INT REFERENCES orders(order_id),
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- SALES REPORT
-- =========================
CREATE VIEW sales_report AS
SELECT
    DATE(created_at) AS sales_date,
    COUNT(order_id) AS total_orders,
    SUM(total_amount) AS total_sales
FROM orders
WHERE order_status = 'completed'
GROUP BY DATE(created_at);