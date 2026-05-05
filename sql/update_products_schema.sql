ALTER TABLE products 
ADD COLUMN city VARCHAR(100) AFTER category_id,
ADD COLUMN area_id INT AFTER city,
ADD COLUMN area_name VARCHAR(100) AFTER area_id,
ADD COLUMN hotel_id INT AFTER area_name,
ADD COLUMN hotel_name VARCHAR(100) AFTER hotel_id,
ADD COLUMN views INT DEFAULT 0 AFTER status,
ADD COLUMN phone VARCHAR(20) AFTER views,
ADD COLUMN ad_type VARCHAR(50) AFTER phone;

-- Create indices for performance
CREATE INDEX idx_products_city ON products(city);
CREATE INDEX idx_products_area ON products(area_id);
CREATE INDEX idx_products_featured ON products(is_featured);
