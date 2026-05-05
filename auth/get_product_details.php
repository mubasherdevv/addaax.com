<?php
require_once 'db_connect.php';
require_once 'session.php';
require_once 'functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the start of the request
error_log("Starting get_product_details.php request");

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    error_log("Unauthorized access attempt");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get product ID from query parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    error_log("Invalid or missing product ID");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

$product_id = intval($_GET['id']);
error_log("Fetching details for product ID: " . $product_id);

try {
    // Get product details
    error_log("Preparing SELECT statement");
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    error_log("Binding parameters");
    if (!$stmt->bind_param("i", $product_id)) {
        error_log("Bind failed: " . $stmt->error);
        throw new Exception("Bind failed: " . $stmt->error);
    }
    
    error_log("Executing query");
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("Product not found: " . $product_id);
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $product = $result->fetch_assoc();
    error_log("Product found: " . print_r($product, true));

    // Format the product data
    $formatted_product = [
        'id' => $product['id'],
        'name' => $product['name'],
        'category_name' => $product['category_name'],
        'price' => $product['price'],
        'description' => $product['description'],
        'image' => !empty($product['image']) ? '../uploads/products/' . $product['image'] : null
    ];

    error_log("Sending response");
    echo json_encode([
        'success' => true,
        'product' => $formatted_product
    ]);
} catch (Exception $e) {
    error_log("Error in get_product_details.php: " . $e->getMessage());
    error_log("SQL Error: " . ($conn->error ?? 'No SQL error'));
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching product details: ' . $e->getMessage(),
        'debug_info' => [
            'sql_error' => $conn->error ?? 'No SQL error',
            'error_code' => $conn->errno ?? 0,
            'stack_trace' => $e->getTraceAsString()
        ]
    ]);
}
?> 