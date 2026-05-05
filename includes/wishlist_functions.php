<?php
require_once __DIR__ . '/../auth/db_connect.php';

/**
 * Check if a product is in user's wishlist
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool True if product is in wishlist, false otherwise
 */
function isInWishlist($user_id, $product_id) {
    global $conn;
    
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Add a product to user's wishlist
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool True if successful, false otherwise
 */
function addToWishlist($user_id, $product_id) {
    global $conn;
    
    // Check if already in wishlist
    if (isInWishlist($user_id, $product_id)) {
        return true;
    }
    
    $sql = "INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    
    return $stmt->execute();
}

/**
 * Remove a product from user's wishlist
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool True if successful, false otherwise
 */
function removeFromWishlist($user_id, $product_id) {
    global $conn;
    
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    
    return $stmt->execute();
}

/**
 * Get all products in user's wishlist
 * @param int $user_id User ID
 * @return array Array of products in wishlist
 */
function getWishlistProducts($user_id) {
    global $conn;
    
    $sql = "SELECT p.*, pi.image_path, w.created_at as added_date 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE w.user_id = ? 
            ORDER BY w.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
} 