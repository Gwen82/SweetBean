-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql303.byetcluster.com
-- Generation Time: Jun 25, 2026 at 03:42 AM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ezyro_42229598_sweetbean_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(9, 'Best Seller'),
(10, 'Drinks'),
(11, 'Cakes'),
(12, 'Pastries');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Available','Unavailable') DEFAULT 'Available',
  `item_type` enum('Drink','Food') NOT NULL DEFAULT 'Food',
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `category_id`, `product_name`, `description`, `price`, `image`, `status`, `item_type`, `is_best_seller`) VALUES
(1, 10, 'Americano', 'Fresh espresso with hot water', '120.00', '1782103437_americano.jpg', 'Available', 'Drink', 0),
(2, 10, 'Latte', 'Espresso with milk', '150.00', '1782103428_latte.jpg', 'Available', 'Drink', 1),
(3, 10, 'Green Tea', 'Japanese Green Tea', '100.00', '1782103506_greentea.jpg', 'Available', 'Drink', 0),
(4, 11, 'Cheese Cake', 'Creamy cheesecake', '180.00', '1782103417_cheesecake.jpg', 'Available', 'Food', 1),
(6, 12, 'Croissant', 'Butter pastry', '90.00', '1782103381_croissant.jpg', 'Available', 'Food', 1),
(7, 11, 'Dubai Chewy Cookies', 'Marshmallow with pistachio and kunafa inside', '110.00', '1782103570_1782058100_Cuki.jpg', 'Available', 'Food', 1),
(8, 10, 'Orange Juice', 'Fresh Oranges', '75.00', '1782103727_orange juice.jpg', 'Available', 'Drink', 0),
(9, 10, 'Caramel Machiatto', 'A sweet espresso beverage made with vanilla syrup, steamed milk, espresso, and a caramel drizzle', '100.00', '1782288526_Caramel Macchiato.jpg', 'Available', 'Drink', 0),
(10, 10, 'Espresso', 'A concentrated coffee beverage brewed by forcing hot water under high pressure (typically 9 bars) through finely ground coffee beans', '74.00', '1782288629_Espresso.jpg', 'Available', 'Drink', 0),
(11, 10, 'Red Velvet Latte', 'A combination between bold espresso, cocoa, and a hint of vanilla for the taste of red velvet cake in a cup', '134.00', '1782288720_Red Velvet Latte.jpg', 'Available', 'Drink', 0),
(12, 11, 'Tiramisu', 'An Italian dessert made with coffee-soaked ladyfingers covered with a cream of egg yolks, sugar, mascarpone, and cocoa powder.', '199.00', '1782288822_Tiramisu.jpg', 'Available', 'Food', 0),
(13, 12, 'Egg Tart', 'A kind of tart found in Cantonese cuisine', '49.00', '1782288906_Egg Tart.jpg', 'Available', 'Food', 0),
(14, 11, 'Brownies', 'A chocolate baked dessert bar', '179.00', '1782288986_Brownies.jpg', 'Available', 'Food', 0),
(15, 12, 'Chocolate Chip Cookies', 'A drop cookie that contains pieces of chocolate mixed into the dough before baking', '39.00', '1782289100_Cookies.jpg', 'Available', 'Food', 0);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `subscriber_id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subscribe_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_type` enum('Pickup','Delivery') DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Preparing','Ready','Delivering','Completed','Cancelled') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'COD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_type`, `delivery_address`, `total_amount`, `status`, `order_date`, `payment_method`) VALUES
(1, 1, 'Pickup', '', '240.00', 'Completed', '2026-06-17 18:28:31', 'COD'),
(2, 1, 'Delivery', 'mana', '390.00', 'Delivering', '2026-06-17 18:38:01', 'COD'),
(3, 1, 'Pickup', '                ', '580.00', 'Ready', '2026-06-18 05:03:00', 'COD'),
(4, 1, 'Delivery', 'No. 129è™Ÿ, Daxue E Rd, Lantian Village, Nanzih District, Kaohsiung City, 811', '245.00', 'Preparing', '2026-06-22 05:58:57', 'COD'),
(5, 1, 'Delivery', 'nuk', '389.00', 'Cancelled', '2026-06-24 09:37:05', 'COD'),
(6, 1, 'Pickup', 'nuk        ', '260.00', 'Cancelled', '2026-06-24 09:47:36', 'COD'),
(7, 1, 'Delivery', 'nuk', '270.00', 'Pending', '2026-06-24 09:59:25', 'COD');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `ice_level` varchar(50) DEFAULT 'N/A',
  `sugar_level` varchar(50) DEFAULT 'N/A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`) VALUES
(8, 4, 7, 1, '110.00', 'N/A', 'N/A'),
(9, 4, 8, 1, '75.00', 'N/A', 'N/A'),
(10, 5, 14, 1, '179.00', 'N/A', 'N/A'),
(11, 5, 2, 1, '150.00', 'N/A', 'N/A'),
(12, 6, 7, 1, '110.00', 'N/A', 'N/A'),
(13, 6, 2, 1, '150.00', 'N/A', 'N/A'),
(14, 7, 7, 1, '110.00', 'N/A', 'N/A'),
(15, 7, 3, 1, '100.00', 'N/A', 'N/A');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `is_subscribed` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`, `phone`, `birth_date`, `is_subscribed`, `reset_token`, `reset_token_expires_at`) VALUES
(1, 'abc', 'abc@gmail.com', '$2y$10$Lf7yE.y9RCQ1phrUf60IV.ulp39Pd7CE78DuliHdF.mh2zwecW/pK', 'customer', '2026-06-17 13:20:46', NULL, NULL, 0, NULL, NULL),
(2, 'es', 'es@gmail.com', '$2y$10$BmWskXOcboYYzQYokAqI0uKV4SYW2kUHE7kfXvqPiWF8.iEZSg3B6', 'customer', '2026-06-17 16:24:15', '0912345678', '2001-01-01', 0, NULL, NULL),
(3, 'Staff Sweet Bean', 'staff@sweetbean.com', '$2y$10$nu47hUXgIQ5Hsurz6gfg5ecRoYebNiBMjCdq3LPjcxaZBFho8Ujl2', 'staff', '2026-06-18 05:40:04', '0912345678', '2000-01-01', 0, NULL, NULL),
(4, 'Admin Sweet Bean', 'admin@sweetbean.com', '$2y$10$Q/PwmTRr1alpTVjrzqfQyeCJnh.ibVdt4m6KphfUVH.txncHTUgVe', 'admin', '2026-06-18 05:42:35', '0900000000', '1999-01-01', 0, NULL, NULL),
(5, 'abcd', 'priscilliagwen@gmail.com', '$2y$10$qq9jbbU.7RnpuPUvenD/NuNz4wyVNMRGFZSLfv6AvrnyBxilQjiGm', 'customer', '2026-06-22 04:53:07', '08657345', '2007-09-12', 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`subscriber_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `subscriber_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
