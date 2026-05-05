<?php
// Enable error reporting for debugging but don't display errors to users
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set content type to JSON
header('Content-Type: application/json');

try {
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

    // Get and validate product data
    $product_name = trim($_POST['product_name'] ?? '');
    $product_description = trim($_POST['product_description'] ?? '');
    $product_category = !empty($_POST['product_category']) ? intval($_POST['product_category']) : null;
    $product_price = !empty($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
    $product_sale_price = !empty($_POST['product_sale_price']) ? floatval($_POST['product_sale_price']) : null;
    $product_status = isset($_POST['product_status']) ? intval($_POST['product_status']) : 1;

    // Validate required fields
    if (empty($product_name)) {
        $response = [
            'success' => false,
            'message' => 'Product name is required'
        ];
        echo json_encode($response);
        exit;
    }

    // Handle image upload
    $product_image = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type and size
        if (!in_array($_FILES['product_image']['type'], $allowed_types)) {
            $response = [
                'success' => false,
                'message' => 'Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.'
            ];
            echo json_encode($response);
            exit;
        }
        
        if ($_FILES['product_image']['size'] > $max_size) {
            $response = [
                'success' => false,
                'message' => 'Image is too large. Maximum size is 5MB.'
            ];
            echo json_encode($response);
            exit;
        }
        
        // Create product images directory if it doesn't exist
        $upload_dir = '../images/products/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $product_image = $new_filename;
        } else {
            throw new Exception("Failed to upload image: " . error_get_last()['message']);
        }
    }

    // Insert product into database
    $insert_sql = "INSERT INTO products (name, description, category_id, price, sale_price, image, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($insert_sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param('ssiddsii', $product_name, $product_description, $product_category, $product_price, $product_sale_price, $product_image, $product_status);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $product_id = $stmt->insert_id;
    $response = [
        'success' => true,
        'message' => 'Product added successfully',
        'product_id' => $product_id
    ];

    echo json_encode($response);

    // Close connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log error
    error_log("Product save error: " . $e->getMessage());
    
    // Return error response
    $response = [
        'success' => false,
        'message' => 'Error adding product: ' . $e->getMessage()
    ];
    
    echo json_encode($response);
    
    // Close connection if exists
    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($check_stmt) && $check_stmt) $check_stmt->close();
    if (isset($conn) && $conn) $conn->close();
}
?> 