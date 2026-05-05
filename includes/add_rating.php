<?php
session_start();
require_once __DIR__ . '/../auth/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to return JSON
header('Content-Type: application/json');

// Log the request
error_log("Rating request received: " . print_r($_POST, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'You must be logged in to rate products']);
    exit;
}

// Check if request is POST and contains required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id']) || !isset($_POST['rating'])) {
    error_log("Invalid request method or missing data");
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);
$rating = intval($_POST['rating']);

// Log the received data
error_log("Rating request - User ID: $user_id, Product ID: $product_id, Rating: $rating");

// Validate rating value
if ($rating < 1 || $rating > 5) {
    error_log("Invalid rating value: $rating");
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

// Check if product exists
$product_check = $conn->prepare("SELECT id FROM products WHERE id = ?");
$product_check->bind_param("i", $product_id);
$product_check->execute();
$product_result = $product_check->get_result();

if ($product_result->num_rows === 0) {
    error_log("Product not found: $product_id");
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if product_ratings table exists
$table_check = $conn->query("SHOW TABLES LIKE 'product_ratings'");
if ($table_check->num_rows === 0) {
    error_log("Product ratings table does not exist, creating it now");
    // Create the table if it doesn't exist
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
    
    if (!$conn->query($create_table)) {
        error_log("Error creating product_ratings table: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error: Table not found']);
        exit;
    }
    error_log("Product ratings table created successfully");
}

// Insert or update rating
$stmt = $conn->prepare("INSERT INTO product_ratings (product_id, user_id, rating) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE rating = ?");
$stmt->bind_param("iiii", $product_id, $user_id, $rating, $rating);

if ($stmt->execute()) {
    error_log("Rating saved successfully");
    // Calculate new average rating
    $avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM product_ratings WHERE product_id = ?");
    $avg_stmt->bind_param("i", $product_id);
    $avg_stmt->execute();
    $avg_result = $avg_stmt->get_result();
    $avg_rating = $avg_result->fetch_assoc()['avg_rating'];
    
    error_log("New average rating: $avg_rating");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Rating saved successfully',
        'new_avg_rating' => round($avg_rating, 1)
    ]);
} else {
    error_log("Error saving rating: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Error saving rating: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?> 