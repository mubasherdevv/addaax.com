-- Add featured content columns to categories table if they don't exist
USE wholesale_ecommerce;

-- Add is_featured to categories if it doesn't exist
SELECT COUNT(*) INTO @exists FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'wholesale_ecommerce'
AND TABLE_NAME = 'categories'
AND COLUMN_NAME = 'is_featured';

SET @sql = IF(@exists = 0, 
    'ALTER TABLE categories ADD COLUMN is_featured TINYINT(1) DEFAULT 0',
    'SELECT "Column is_featured already exists in categories"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add columns to products table if they don't exist
-- Add is_featured
SELECT COUNT(*) INTO @exists FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'wholesale_ecommerce'
AND TABLE_NAME = 'products'
AND COLUMN_NAME = 'is_featured';

SET @sql = IF(@exists = 0, 
    'ALTER TABLE products ADD COLUMN is_featured TINYINT(1) DEFAULT 0',
    'SELECT "Column is_featured already exists in products"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add is_new_arrival
SELECT COUNT(*) INTO @exists FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'wholesale_ecommerce'
AND TABLE_NAME = 'products'
AND COLUMN_NAME = 'is_new_arrival';

SET @sql = IF(@exists = 0, 
    'ALTER TABLE products ADD COLUMN is_new_arrival TINYINT(1) DEFAULT 0',
    'SELECT "Column is_new_arrival already exists in products"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add is_hot_deal
SELECT COUNT(*) INTO @exists FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'wholesale_ecommerce'
AND TABLE_NAME = 'products'
AND COLUMN_NAME = 'is_hot_deal';

SET @sql = IF(@exists = 0, 
    'ALTER TABLE products ADD COLUMN is_hot_deal TINYINT(1) DEFAULT 0',
    'SELECT "Column is_hot_deal already exists in products"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 