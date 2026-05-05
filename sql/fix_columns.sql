USE wholesale_ecommerce;

-- Add missing columns to categories
ALTER TABLE categories ADD COLUMN IF NOT EXISTS slug VARCHAR(255) AFTER name;

-- Add missing columns to cities (if any)
ALTER TABLE cities ADD COLUMN IF NOT EXISTS slug VARCHAR(255) AFTER name;
ALTER TABLE cities ADD COLUMN IF NOT EXISTS image VARCHAR(255) AFTER slug;
ALTER TABLE cities ADD COLUMN IF NOT EXISTS is_popular TINYINT(1) DEFAULT 0 AFTER image;

-- Add missing columns to areas
ALTER TABLE areas ADD COLUMN IF NOT EXISTS slug VARCHAR(255) AFTER name;

-- Add missing columns to hotels
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS slug VARCHAR(255) AFTER name;

-- Add missing columns to products
ALTER TABLE products ADD COLUMN IF NOT EXISTS slug VARCHAR(255) AFTER name;
