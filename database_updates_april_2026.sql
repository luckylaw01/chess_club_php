-- ==========================================================
-- NAIROBI CHESS CLUB - DATABASE UPDATES (APRIL 2026)
-- Run this script to sync your hosted database with the latest features.
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. EXTEND USERS TABLE FOR PASSWORD RESET
-- (Check if columns exist first if running incrementally)
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `reset_expires` DATETIME DEFAULT NULL;


-- 2. CREATE PRODUCT CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_categories` (`id`, `name`) VALUES
(1, 'Chess Sets'),
(2, 'Clocks'),
(3, 'Books'),
(4, 'Apparel')
ON DUPLICATE KEY UPDATE name=VALUES(name);


-- 3. CREATE PRODUCTS TABLE (Inventory)
-- We check if the table exists. If it does, we ensure it matches the code's expected schema (category_id).
-- If your current table has 'category' (VARCHAR) instead of 'category_id' (INT), we convert it.

-- First, ensure the table exists
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migration: If the table exists but has 'category' (string) instead of 'category_id' (id),
-- we add the column and drop the old one.
SET @dbname = DATABASE();
SET @tablename = 'products';
SET @columnname = 'category';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'ALTER TABLE `products` DROP COLUMN `category`, ADD COLUMN `category_id` INT(11) NOT NULL AFTER `id`, ADD KEY `category_id` (`category_id`), ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`)',
  'SELECT 1'
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert Default Products (KES)
-- Use DELETE instead of TRUNCATE to avoid foreign key issues
DELETE FROM `order_items`; -- Clear items first since they depend on products
DELETE FROM `products`;
ALTER TABLE `products` AUTO_INCREMENT = 1;

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `image_url`) VALUES
(1, 1, 'Professional Tournament Set', 'Weighted plastic pieces with a roll-up vinyl board.', 3500.00, 25, 'chess_set.png'),
(2, 1, 'Luxury Wooden Set', 'Hand-carved Staunton pieces in premium rosewood.', 12500.00, 5, 'slotted_board.png'),
(3, 1, 'Pocket Travel Set', 'Magnetic pieces for on-the-go analysis.', 1500.00, 50, 'magnetic_board.png'),
(4, 2, 'DGT 2010 Digital Clock', 'Official FIDE tournament clock with delay/increment.', 8500.00, 12, 'chess_clock.png'),
(5, 2, 'Analog Wooden Clock', 'Classic mechanical ticking clock for blitz.', 4500.00, 8, 'chess_clock.png'),
(6, 3, 'My 60 Memorable Games', 'Bobby Fischer classic strategy book.', 2200.00, 15, 'scorebook.png'),
(7, 3, 'Modern Chess Strategy', 'Comprehensive guide to middle-game concepts.', 2800.00, 3, 'scorebook.png'),
(8, 4, 'Club Hoodie', 'Warm hoodie with embroidered club logo.', 3000.00, 20, 'hoodie.png');


-- 4. CREATE SHOPPING ORDERS TABLE
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 5. CREATE ORDER ITEMS TABLE (Link between Orders and Products)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 6. ENSURE PAYMENTS TABLE SUPPORTS SHOPPING
-- (Assumes payments table exists, adding indexes if needed)
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


