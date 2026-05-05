<?php
require_once __DIR__ . '/../config/constants.php';
/**
 * Website Settings Include File
 * 
 * This file should be included at the top of all front-end pages to load website settings
 * from the database and make them available for use throughout the site.
 */

// Include helper functions
require_once __DIR__ . '/functions.php';

// Include database connection if not already included
if (!function_exists('getWebsiteSettings')) {
    require_once __DIR__ . '/../auth/get_settings.php';
}

// Load all website settings
$WEBSITE_SETTINGS = getWebsiteSettings();

// Default values for essential settings if not set in database
$WEBSITE_NAME = $WEBSITE_SETTINGS['website_name'] ?? 'Wholesale E-commerce';
$WEBSITE_LOGO = $WEBSITE_SETTINGS['website_logo'] ?? 'logo.svg';
$COMPANY_NAME = $WEBSITE_SETTINGS['company_name'] ?? 'Wholesale Supply Co.';
$COMPANY_EMAIL = $WEBSITE_SETTINGS['company_email'] ?? 'info@wholesale.com';
$COMPANY_PHONE = $WEBSITE_SETTINGS['company_phone'] ?? '+1 (212) 555-1234';
$COMPANY_ADDRESS = $WEBSITE_SETTINGS['company_address'] ?? '123 Business Street, Suite 200, New York, NY 10001';

// Shipping and tax settings
$DEFAULT_SHIPPING = floatval($WEBSITE_SETTINGS['default_shipping'] ?? 15);
$FREE_SHIPPING_THRESHOLD = floatval($WEBSITE_SETTINGS['free_shipping_threshold'] ?? 500);
$TAX_RATE = floatval($WEBSITE_SETTINGS['tax_rate'] ?? 7.5);

// Feature flags
$ENABLE_REVIEWS = isset($WEBSITE_SETTINGS['enable_reviews']) ? $WEBSITE_SETTINGS['enable_reviews'] == '1' : true;
$ENABLE_WISHLIST = isset($WEBSITE_SETTINGS['enable_wishlist']) ? $WEBSITE_SETTINGS['enable_wishlist'] == '1' : true;
$ENABLE_COMPARE = isset($WEBSITE_SETTINGS['enable_compare']) ? $WEBSITE_SETTINGS['enable_compare'] == '1' : true;
$ENABLE_GUEST_CHECKOUT = isset($WEBSITE_SETTINGS['enable_guest_checkout']) ? $WEBSITE_SETTINGS['enable_guest_checkout'] == '1' : false;
$MAINTENANCE_MODE = isset($WEBSITE_SETTINGS['maintenance_mode']) ? $WEBSITE_SETTINGS['maintenance_mode'] == '1' : false;

// Homepage Section Settings
$SHOW_HERO = isset($WEBSITE_SETTINGS['show_hero']) ? $WEBSITE_SETTINGS['show_hero'] == '1' : true;
$SHOW_FEATURED_CATEGORIES = isset($WEBSITE_SETTINGS['show_featured_categories']) ? $WEBSITE_SETTINGS['show_featured_categories'] == '1' : true;
$SHOW_FEATURED_PRODUCTS = isset($WEBSITE_SETTINGS['show_featured_products']) ? $WEBSITE_SETTINGS['show_featured_products'] == '1' : true;
$SHOW_NEW_ARRIVALS = isset($WEBSITE_SETTINGS['show_new_arrivals']) ? $WEBSITE_SETTINGS['show_new_arrivals'] == '1' : true;
$SHOW_HOT_DEALS = isset($WEBSITE_SETTINGS['show_hot_deals']) ? $WEBSITE_SETTINGS['show_hot_deals'] == '1' : true;
$SHOW_TESTIMONIALS = isset($WEBSITE_SETTINGS['show_testimonials']) ? $WEBSITE_SETTINGS['show_testimonials'] == '1' : true;

// Check if site is in maintenance mode and redirect if needed
if ($MAINTENANCE_MODE && !isset($_SESSION['user_role']) && $_SERVER['SCRIPT_NAME'] != '/maintenance.php') {
    // Don't redirect these URLs
    $allowed_urls = [
        '/auth/login.php',
        '/auth/logout.php',
        '/maintenance.php'
    ];
    
    if (!in_array($_SERVER['SCRIPT_NAME'], $allowed_urls)) {
        header('Location: /maintenance.php');
        exit;
    }
}
?> 