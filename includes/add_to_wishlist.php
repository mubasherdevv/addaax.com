<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/wishlist_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);

if (addToWishlist($user_id, $product_id)) {
    echo json_encode(['success' => true, 'message' => 'Product added to wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to wishlist']);
} 