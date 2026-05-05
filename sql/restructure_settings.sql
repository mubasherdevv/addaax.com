USE wholesale_ecommerce;

-- Rename old tables if they exist and don't match the new schema
RENAME TABLE website_settings TO website_settings_old;
RENAME TABLE seo_settings TO seo_settings_old;

-- Create website_settings table with new schema
CREATE TABLE website_settings (
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

-- Create seo_settings table with new schema
CREATE TABLE seo_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_type VARCHAR(50),
    page_path VARCHAR(255),
    title VARCHAR(255),
    meta_description TEXT,
    keywords TEXT,
    reference_id VARCHAR(50), 
    is_active TINYINT(1) DEFAULT 1,
    mongo_id VARCHAR(50) UNIQUE
);
