<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'addazjgu_root');
define('DB_PASSWORD', 'addazjgu_wholesale_ecommerce');
define('DB_NAME', 'addazjgu_wholesale_ecommerce');

// Path settings
define('SITE_URL', '/');
define('ROOT_PATH', __DIR__ . '/');
define('IMAGES_PATH', ROOT_PATH . 'images/');
define('PRODUCTS_IMG_PATH', IMAGES_PATH . 'products/');

// Include essential files
require_once 'auth/db_connect.php';

// Include website_settings.php if it exists
if (file_exists('includes/website_settings.php')) {
    require_once 'includes/website_settings.php';
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?> 