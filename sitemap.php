<?php
/**
 * Dynamic Sitemap Generator for ADDAAX.com
 */
require_once 'auth/db_connect.php';

header("Content-Type: application/xml; charset=utf-8");

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. Static Pages
$static_pages = [
    '',
    '/products.php',
    '/cities.php',
    '/auth/login.php',
    '/auth/register.php'
];

foreach ($static_pages as $page) {
    echo '<url>';
    echo '<loc>' . $base_url . $page . '</loc>';
    echo '<changefreq>daily</changefreq>';
    echo '<priority>1.0</priority>';
    echo '</url>';
}

// 2. City Pages (Escorts in City)
$cities_query = "SELECT name FROM cities WHERE status = 1";
$cities_result = $conn->query($cities_query);
if ($cities_result) {
    while ($city = $cities_result->fetch_assoc()) {
        $city_slug = strtolower(str_replace(' ', '-', trim($city['name'])));
        echo '<url>';
        echo '<loc>' . $base_url . '/escorts/' . $city_slug . '</loc>';
        echo '<changefreq>weekly</changefreq>';
        echo '<priority>0.8</priority>';
        echo '</url>';
    }
}

// 3. Individual Ad Pages
$ads_query = "SELECT id, name FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 1000";
$ads_result = $conn->query($ads_query);
if ($ads_result) {
    while ($ad = $ads_result->fetch_assoc()) {
        $ad_slug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', trim($ad['name']))));
        echo '<url>';
        echo '<loc>' . $base_url . '/ad/' . $ad_slug . '-' . $ad['id'] . '</loc>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.6</priority>';
        echo '</url>';
    }
}

echo '</urlset>';
?>
