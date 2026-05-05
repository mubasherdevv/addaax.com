ALTER TABLE products 
ADD COLUMN seller_id INT AFTER category_id,
ADD COLUMN badges TEXT AFTER ad_type;

-- Create index for seller_id
CREATE INDEX idx_products_seller ON products(seller_id);
