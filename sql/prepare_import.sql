USE wholesale_ecommerce;

-- Add mongo_id column to existing tables for mapping
ALTER TABLE users ADD COLUMN IF NOT EXISTS mongo_id VARCHAR(50) UNIQUE;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS mongo_id VARCHAR(50) UNIQUE;
ALTER TABLE products ADD COLUMN IF NOT EXISTS mongo_id VARCHAR(50) UNIQUE;
ALTER TABLE cities ADD COLUMN IF NOT EXISTS mongo_id VARCHAR(50) UNIQUE;
ALTER TABLE areas ADD COLUMN IF NOT EXISTS mongo_id VARCHAR(50) UNIQUE;
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS mongo_id VARCHAR(50) UNIQUE;

-- Create website_settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS website_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255),
    logo VARCHAR(255),
    favicon VARCHAR(255),
    contact_email VARCHAR(100),
    currency VARCHAR(20),
    featured_ad_price DECIMAL(10,2),
    social_links JSON,
    header_scripts TEXT,
    footer_scripts TEXT,
    maintenance_mode TINYINT(1) DEFAULT 0,
    maintenance_message TEXT,
    mongo_id VARCHAR(50) UNIQUE
);

-- Create seo_settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS seo_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_type VARCHAR(50),
    page_path VARCHAR(255),
    title VARCHAR(255),
    meta_description TEXT,
    keywords TEXT,
    reference_id VARCHAR(50), -- Mongo ID of city/area/hotel/etc
    is_active TINYINT(1) DEFAULT 1,
    mongo_id VARCHAR(50) UNIQUE
);

-- Create subcategories table if it doesn't exist
CREATE TABLE IF NOT EXISTS subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100),
    slug VARCHAR(100) UNIQUE,
    mongo_id VARCHAR(50) UNIQUE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
