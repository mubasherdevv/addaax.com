<?php
require_once 'db_connect.php';

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'seller_display_name'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN seller_display_name VARCHAR(255) DEFAULT NULL AFTER phone");
    echo "Column 'seller_display_name' added successfully.";
} else {
    echo "Column already exists.";
}
?>
