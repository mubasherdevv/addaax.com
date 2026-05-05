-- Create database if not exists
CREATE DATABASE IF NOT EXISTS wholesale_ecommerce;

-- Use the database
USE wholesale_ecommerce;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    business_type ENUM('Retailer', 'Distributor', 'Wholesaler', 'Manufacturer', 'Other') DEFAULT 'Retailer',
    tax_id VARCHAR(50),
    is_verified BOOLEAN DEFAULT 0,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Indexes for faster queries
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_role ON users(role);
CREATE INDEX idx_business_type ON users(business_type);

-- Sample admin user (password: admin123)
INSERT INTO users (
    company_name, email, password, first_name, last_name, 
    business_type, is_verified, role
) VALUES (
    'Admin Company', 'admin@example.com', 
    '$2y$10$DzM.mQA99QQyZ1mV4YMODuqYXEh2MIUUsJ/Vr3ozzDU6.5AYJZ9Oe',
    'Admin', 'User', 'Other', 1, 'admin'
);

-- Sample regular user (password: user123)
INSERT INTO users (
    company_name, email, password, first_name, last_name, 
    business_type, is_verified, role
) VALUES (
    'Test Store', 'user@example.com', 
    '$2y$10$B0.t9.E5vkqOUL2a7nXGM.GTpEsRlpgpWrPEp7MV3LCVCgHqQvkEK',
    'Test', 'User', 'Retailer', 1, 'user'
); 