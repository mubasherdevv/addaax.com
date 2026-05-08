<?php
/**
 * City Sitemap Generator
 */
require_once '../auth/db_connect.php';
header("Content-Type: application/xml; charset=utf-8");
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$cities_query = "SELECT name FROM cities WHERE status = 1";
$cities_result = $conn->query($cities_query);
if ($cities_result) {
    while ($city = $cities_result->fetch_assoc()) {
        $city_slug = strtolower(str_replace(' ', '-', trim($city['name'])));
        echo '<url>';
        echo '<loc>' . $base_url . '/call-girls/' . $city_slug . '</loc>';
        echo '</url>';
    }
}

echo '</urlset>';
