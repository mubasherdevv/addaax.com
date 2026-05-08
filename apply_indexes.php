<?php
require_once 'auth/db_connect.php';

echo "<h1>Applying Database Optimizations...</h1>";

$queries = [
    "CREATE INDEX IF NOT EXISTS idx_products_seller ON products(seller_id)",
    "CREATE INDEX IF NOT EXISTS idx_products_status ON products(status)",
    "CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)",
    "CREATE INDEX IF NOT EXISTS idx_products_city ON products(city)",
    "CREATE INDEX IF NOT EXISTS idx_seo_page ON seo_settings(page_name)",
    "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "Successfully executed: $query <br>";
    } else {
        echo "Error executing $query: " . $conn->error . "<br>";
    }
}

echo "Done.";
?>
