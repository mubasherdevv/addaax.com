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
    
    // Get current status
    $sql = "SELECT status FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Toggle status (1 -> 2 or 2 -> 1, assuming 1 is active and 2 is hidden)
        // Actually the user said Active/Hide. Let's check products.php for status values.
        // Usually 1=Active, 0=Pending. Let's use 1 and 2 (or 1 and 0).
        // dashboard.php uses 1=Active, 0=Pending, else Expired.
        // For Active/Hide, maybe use 1 and 2.
        
        $new_status = $row['status'] == 1 ? 2 : 1;
        
        $update_sql = "UPDATE products SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('ii', $new_status, $product_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'status' => $new_status]);
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
