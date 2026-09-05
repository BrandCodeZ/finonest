-- ============================================================
-- Finonest - Database Schema
-- Database: finonest_db
-- Import this file into phpMyAdmin (or run via mysql CLI)
-- ============================================================

CREATE DATABASE IF NOT EXISTS finonest_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE finonest_db;

-- ------------------------------------------------------------
-- Table: admins
-- Stores admin login credentials (password is hashed)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: categories
-- Product categories (e.g. Home Loans, Car Loans, etc.)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(150) NOT NULL,
  description TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_category_name (category_name),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: products
-- Product catalogue shown on the frontend
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_name VARCHAR(200) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  description TEXT DEFAULT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount_price DECIMAL(12,2) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  stock_quantity INT NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_category_id (category_id),
  KEY idx_status (status),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Seed: Default admin user
-- Login:  admin  /  admin123
-- ------------------------------------------------------------
-- The password below is a valid bcrypt hash of "admin123".
-- You can change these credentials after logging in (or edit this
-- file and re-generate a hash with password_hash()).
INSERT INTO admins (username, email, password) VALUES
('admin', 'admin@finonest.com', '$2y$10$V8zNfyhj88Lc7PRM2IP5GeOKhJISWyok9C1CxtGO1JvxsKqSDheiO');

-- ------------------------------------------------------------
-- Seed: Default categories
-- ------------------------------------------------------------
INSERT INTO categories (category_name, description, status) VALUES
('Home Loans',  'Loans to buy, build or renovate your dream home.', 1),
('Car Loans',   '100% financing for new and used cars.', 1),
('Personal Loans', 'Instant unsecured personal loans for any need.', 1),
('Business Loans', 'Working capital and term loans for business growth.', 1),
('Loan Against Property', 'High-value loans against your property.', 1),
('Credit Cards', 'Rewards, travel and cashback credit cards.', 1);

-- ------------------------------------------------------------
-- Seed: Sample products (you can edit or delete these from admin)
-- ------------------------------------------------------------
INSERT INTO products (product_name, category_id, description, price, discount_price, image, stock_quantity, status) VALUES
('Home Loan', 1, 'Turn your dream home into reality with flexible tenures up to 30 years, up to 90% LTV and doorstep assistance. Best rates from 50+ lenders.', 1200000.00, 1080000.00, NULL, 100, 1),
('Car Loan', 2, '100% on-road financing for new and used cars with same-day approval and doorstep pickup.', 800000.00, 720000.00, NULL, 80, 1),
('Personal Loan', 3, 'Instant personal loans up to Rs. 40 lakh for any need — wedding, travel, renovation or emergency. Minimal documentation, 24H disbursal.', 500000.00, 450000.00, NULL, 120, 1),
('Business Loan', 4, 'Working capital and term loans to help your business grow with minimal documentation and collateral-free options.', 2000000.00, 1800000.00, NULL, 60, 1),
('Loan Against Property', 5, 'Unlock the value of your property for higher loan amounts at some of the lowest interest rates. Tenure up to 20 years.', 5000000.00, 4500000.00, NULL, 40, 1),
('Credit Cards', 6, 'Rewards, travel and cashback cards from top banks with lounge access, No Cost EMI and instant approval.', 100000.00, 85000.00, NULL, 200, 1);
