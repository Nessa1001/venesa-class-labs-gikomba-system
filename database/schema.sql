CREATE DATABASE IF NOT EXISTS gikomba_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gikomba_store;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL,
    discount_price DECIMAL(12,2) NULL,
    stock INT NOT NULL DEFAULT 0,
    item_condition VARCHAR(30) NOT NULL DEFAULT 'Good',
    sizes VARCHAR(255) NULL,
    colors VARCHAR(255) NULL,
    badge ENUM('New', 'Sale', 'Trending') DEFAULT 'Trending',
    rating DECIMAL(3,2) NOT NULL DEFAULT 4.50,
    review_count INT NOT NULL DEFAULT 0,
    image_primary VARCHAR(255) NOT NULL,
    image_secondary VARCHAR(255) NULL,
    image_tertiary VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    county VARCHAR(120) NOT NULL,
    town VARCHAR(120) NOT NULL,
    street VARCHAR(180) NOT NULL,
    house_number VARCHAR(80) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE wishlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY ux_wishlist_user_product (user_id, product_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active', 'ordered', 'abandoned') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY ux_cart_product (cart_id, product_id),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    customer_name VARCHAR(180) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(191) NOT NULL,
    county VARCHAR(120) NOT NULL,
    town VARCHAR(120) NOT NULL,
    street VARCHAR(180) NOT NULL,
    house_number VARCHAR(80) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    shipping_fee DECIMAL(12,2) NOT NULL,
    vat_amount DECIMAL(12,2) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('mpesa', 'cod') NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'ready_for_delivery', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    estimated_delivery_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    method ENUM('mpesa', 'cod') NOT NULL,
    provider_reference VARCHAR(120) NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'successful', 'failed') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    stage ENUM('pending', 'confirmed', 'processing', 'ready_for_delivery', 'completed', 'cancelled') NOT NULL,
    expected_date DATE NULL,
    delivered_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review_text TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(191) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    channel ENUM('email', 'sms') NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_notifications_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (id, first_name, last_name, phone, email, password_hash, role, is_active, created_at, updated_at) VALUES
(1, 'Admin', 'User', '+254700000001', 'admin@gikombastore.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW(), NOW()),
(2, 'Demo', 'Customer', '+254700000002', 'customer@gikombastore.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 1, NOW(), NOW());

INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES
('Dresses', 'dresses', 'Affordable second-hand dresses', NOW(), NOW()),
('Trousers', 'trousers', 'Campus and office trousers', NOW(), NOW()),
('Jeans', 'jeans', 'Quality denim jeans', NOW(), NOW()),
('Shirts', 'shirts', 'Casual and official shirts', NOW(), NOW()),
('Tops', 'tops', 'Ladies tops and blouses', NOW(), NOW()),
('Jackets', 'jackets', 'Warm jackets and coats', NOW(), NOW()),
('Sweaters', 'sweaters', 'Hoodies and sweaters', NOW(), NOW()),
('Shoes', 'shoes', 'Affordable shoes collection', NOW(), NOW()),
('Bags', 'bags', 'School and travel bags', NOW(), NOW()),
('Accessories', 'accessories', 'Belts, caps, and watches', NOW(), NOW());

INSERT INTO products (category_id, name, slug, description, price, discount_price, stock, item_condition, sizes, colors, badge, rating, review_count, image_primary, image_secondary, image_tertiary, created_at, updated_at) VALUES
(6, 'Ladies Denim Jacket', 'ladies-denim-jacket', 'Original denim jacket from Gikomba in clean condition.', 1000.00, 800.00, 8, 'Excellent', 'S,M,L', 'Blue,Navy', 'Sale', 4.50, 18, 'dress.jpg', 'clothes.jpg', 'tights.jpg', NOW(), NOW()),
(3, 'Men\'s Jeans', 'mens-jeans', 'Durable denim jeans suitable for daily wear.', 750.00, 700.00, 14, 'Good', '30,32,34,36', 'Blue,Black', 'Trending', 4.60, 31, 'trouser.jpg', 'shorts.jpg', 'tights.jpg', NOW(), NOW()),
(1, 'Floral Dress', 'floral-dress', 'Light and comfortable floral dress for casual outings.', 750.00, 600.00, 10, 'Very Good', 'S,M,L', 'Pink,White', 'New', 4.40, 20, 'skirt.jpg', 'dress.jpg', 'clothes.jpg', NOW(), NOW()),
(4, 'Casual Shirt', 'casual-shirt', 'Cotton shirt for campus and weekend wear.', 500.00, NULL, 12, 'Good', 'M,L,XL', 'White,Checked', 'Trending', 4.20, 11, 'shorts.jpg', 'clothes.jpg', 'dress.jpg', NOW(), NOW()),
(7, 'Unisex Hoodie', 'unisex-hoodie', 'Warm hoodie ideal for cold mornings and evening classes.', 1200.00, 1000.00, 6, 'Excellent', 'M,L,XL', 'Grey,Black', 'Sale', 4.30, 15, 'clothes.jpg', 'dress.jpg', 'tights.jpg', NOW(), NOW()),
(8, 'Sneakers', 'sneakers', 'Comfortable second-hand sneakers in good condition.', 1500.00, 1200.00, 9, 'Very Good', '40,41,42,43', 'White,Black', 'Trending', 4.70, 26, 'sneakers.jpg', 'sportshoes.jpg', 'sandals.jpg', NOW(), NOW()),
(9, 'Handbag', 'handbag', 'Stylish handbag for everyday use.', 900.00, 750.00, 7, 'Good', 'One Size', 'Brown,Black', 'New', 4.10, 8, 'handbag.jpg', 'bags.jpg', 'schoolbags.jpg', NOW(), NOW());

INSERT INTO feedback (name, email, rating, message, created_at, updated_at) VALUES
('Amina W.', 'amina@student.ac.ke', 5, 'Great prices and clean clothes. I bought a jacket at KSh 800.', NOW(), NOW()),
('Brian K.', 'brian@student.ac.ke', 4, 'Delivery was on time and jeans quality was good.', NOW(), NOW());
