<?php
/**
 * Advanced PHP Router for Adaax.com
 * Handles SEO-friendly URLs reliably across all servers
 */

$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/'; // Change this if the site is in a subdirectory

// Get the path from URL
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove the base path from the start of the path
if ($base_path !== '/' && strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

$path = trim($path, '/');

// Routing Logic
$segments = explode('/', $path);

// 1. Escorts Base (addaax.com/escorts)
if ($segments[0] === 'escorts') {
    if (isset($segments[1]) && !empty($segments[1])) {
        $_GET['city'] = $segments[1]; // City filter
        if (isset($segments[2])) {
            $_GET['area'] = $segments[2]; // Area filter
        }
    }
    require 'products.php';
    exit;
}

// 2. Ad Details (addaax.com/ad/slug-id)
if ($segments[0] === 'ad' && isset($segments[1])) {
    $parts = explode('-', $segments[1]);
    $id = end($parts); // Get the last part which is the ID
    if (is_numeric($id)) {
        $_GET['id'] = $id;
        require 'product_details.php';
        exit;
    }
}

// 3. Fallback to existing files (e.g. login.php, dashboard.php)
if (file_exists($path . '.php')) {
    require $path . '.php';
    exit;
}

if (file_exists($path) && is_file($path)) {
    return false; // Serve static files as-is
}

// 4. Default to index.php
require 'index.php';
?>
