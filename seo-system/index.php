<?php
/**
 * Main Sitemap Index
 */
header("Content-Type: application/xml; charset=utf-8");
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. Static Pages (Directly in Index or separate)
// We'll put static pages in the main index for simplicity or a separate file.
// For now, let's just point to the sub-sitemaps.

echo '<sitemap>';
echo '<loc>' . $base_url . '/seo-system/cities.php</loc>';
echo '</sitemap>';

echo '<sitemap>';
echo '<loc>' . $base_url . '/seo-system/ads.php</loc>';
echo '</sitemap>';

echo '</sitemapindex>';
