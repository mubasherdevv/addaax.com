<?php
session_start();
require_once 'wishlist_functions.php';
require_once '../auth/db_connect.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to wishlist'
    ]);
    exit;
}

// Get POST data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Validate input
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Perform action
if ($action === 'add') {
    $result = addToWishlist($user_id, $product_id);
    $message = 'Added to wishlist';
} else if ($action === 'remove') {
    $result = removeFromWishlist($user_id, $product_id);
    $message = 'Removed from wishlist';
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

// Return response
if ($result) {
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating wishlist'
    ]);
}
?> 