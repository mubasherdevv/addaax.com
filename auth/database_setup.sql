-- First, drop all foreign key constraints
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS scan_history;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create scan_history table
CREATE TABLE IF NOT EXISTS scan_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some sample categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Food', 'Food and beverage products'),
('Home', 'Home and kitchen items'),
('Sports', 'Sports equipment and accessories');

-- Insert some sample products
INSERT INTO products (name, sku, category_id, price, stock_quantity, description) VALUES
('Smartphone X', 'SPX-001', 1, 999.99, 50, 'Latest smartphone model'),
('Laptop Pro', 'LP-001', 1, 1299.99, 30, 'High-performance laptop'),
('T-Shirt', 'TS-001', 2, 19.99, 100, 'Cotton t-shirt'),
('Jeans', 'JN-001', 2, 49.99, 75, 'Classic blue jeans'),
('Coffee Beans', 'CB-001', 3, 12.99, 200, 'Premium arabica coffee'),
('Blender', 'BL-001', 4, 79.99, 40, 'High-speed kitchen blender'),
('Yoga Mat', 'YM-001', 5, 29.99, 60, 'Non-slip yoga mat');

-- Insert sample scan history data
INSERT INTO scan_history (product_id, quantity, scan_time, user_id) VALUES
(1, 5, DATE_SUB(NOW(), INTERVAL 1 DAY), 1),
(2, 3, DATE_SUB(NOW(), INTERVAL 2 DAY), 1),
(3, 10, DATE_SUB(NOW(), INTERVAL 3 DAY), 1),
(4, 8, DATE_SUB(NOW(), INTERVAL 4 DAY), 1),
(5, 15, DATE_SUB(NOW(), INTERVAL 5 DAY), 1),
(6, 4, DATE_SUB(NOW(), INTERVAL 6 DAY), 1),
(7, 6, DATE_SUB(NOW(), INTERVAL 7 DAY), 1),
(1, 2, DATE_SUB(NOW(), INTERVAL 8 DAY), 1),
(2, 1, DATE_SUB(NOW(), INTERVAL 9 DAY), 1),
(3, 7, DATE_SUB(NOW(), INTERVAL 10 DAY), 1); 