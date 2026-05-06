<?php
require_once 'session.php';
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $product_id = intval($_POST['id']);
    
    // Get current featured status
    $sql = "SELECT is_featured FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $new_status = $row['is_featured'] ? 0 : 1;
        
        $update_sql = "UPDATE products SET is_featured = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('ii', $new_status, $product_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'is_featured' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
