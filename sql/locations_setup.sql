USE wholesale_ecommerce;

-- Create cities table
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    image VARCHAR(255),
    is_popular TINYINT(1) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create areas table
CREATE TABLE IF NOT EXISTS areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);

-- Create hotels table
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);

-- Insert sample cities
INSERT IGNORE INTO cities (name, slug, is_popular) VALUES 
('Karachi', 'karachi', 1),
('Lahore', 'lahore', 1),
('Islamabad', 'islamabad', 1),
('Rawalpindi', 'rawalpindi', 1),
('Faisalabad', 'faisalabad', 0),
('Multan', 'multan', 0);

-- Insert sample areas (assuming city IDs 1, 2, 3)
INSERT IGNORE INTO areas (city_id, name, slug) VALUES 
(1, 'Clifton', 'clifton'),
(1, 'DHA Phase 6', 'dha-phase-6'),
(2, 'Gulberg', 'gulberg'),
(2, 'Model Town', 'model-town'),
(3, 'F-7 Markaz', 'f-7-markaz');

-- Insert sample hotels
INSERT IGNORE INTO hotels (city_id, name, slug) VALUES 
(1, 'Movenpick Hotel', 'movenpick-hotel'),
(1, 'Pearl Continental', 'pc-hotel-karachi'),
(2, 'Avari Lahore', 'avari-lahore'),
(3, 'Serena Hotel', 'serena-hotel-islamabad');
