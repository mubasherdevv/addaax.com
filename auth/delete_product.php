<?php
require_once 'session.php';
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    $response = [
        'success' => false,
        'message' => 'Unauthorized access'
    ];
    echo json_encode($response);
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        'success' => false,
        'message' => 'Invalid request method'
    ];
    echo json_encode($response);
    exit;
}

// Get product ID
$product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($product_id <= 0) {
    $response = [
        'success' => false,
        'message' => 'Invalid product ID'
    ];
    echo json_encode($response);
    exit;
}

// Get product details to delete image if exists
$get_sql = "SELECT image FROM products WHERE id = ?";
$get_stmt = $conn->prepare($get_sql);
$get_stmt->bind_param('i', $product_id);
$get_stmt->execute();
$result = $get_stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Delete the product
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $product_id);
    
    if ($delete_stmt->execute()) {
        // Delete product image if exists
        if (!empty($product['image'])) {
            $image_path = '../images/products/' . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $response = [
            'success' => true,
            'message' => 'Product deleted successfully'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error deleting product: ' . $conn->error
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Product not found'
    ];
}

echo json_encode($response);

// Close connection
$conn->close();
?> 