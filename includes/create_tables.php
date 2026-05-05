<?php
require_once __DIR__ . '/../auth/db_connect.php';

// Check if product_ratings table exists
$check_table = $conn->query("SHOW TABLES LIKE 'product_ratings'");
if ($check_table->num_rows == 0) {
    // Create product_ratings table
    $create_table = "CREATE TABLE `product_ratings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `rating` int(1) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `product_user_unique` (`product_id`, `user_id`),
        KEY `product_id` (`product_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_table)) {
        echo "Product ratings table created successfully.<br>";
    } else {
        echo "Error creating product ratings table: " . $conn->error . "<br>";
    }
} else {
    echo "Product ratings table already exists.<br>";
}

// Check if product_reviews table exists
$check_table = $conn->query("SHOW TABLES LIKE 'product_reviews'");
if ($check_table->num_rows == 0) {
    // Create product_reviews table
    $create_table = "CREATE TABLE `product_reviews` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `review_text` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `product_user_unique` (`product_id`, `user_id`),
        KEY `product_id` (`product_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($create_table)) {
        echo "Product reviews table created successfully.<br>";
    } else {
        echo "Error creating product reviews table: " . $conn->error . "<br>";
    }
} else {
    echo "Product reviews table already exists.<br>";
}

$conn->close();
?> 