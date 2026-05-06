<?php
/**
 * Ads Sitemap Generator
 */
require_once '../auth/db_connect.php';
header("Content-Type: application/xml; charset=utf-8");
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$ads_query = "SELECT id, name FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 5000";
$ads_result = $conn->query($ads_query);
if ($ads_result) {
    while ($ad = $ads_result->fetch_assoc()) {
        $ad_slug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', trim($ad['name']))));
        echo '<url>';
        echo '<loc>' . $base_url . '/ad/' . $ad_slug . '-' . $ad['id'] . '</loc>';
        echo '<changefreq>daily</changefreq>';
        echo '<priority>0.6</priority>';
        echo '</url>';
    }
}

echo '</urlset>';
