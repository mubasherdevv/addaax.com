<?php
require_once 'session.php';
require_once 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check if user is logged in and is an admin
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Check if the request is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get product ID
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }

    // Check if product exists
    $check_sql = "SELECT * FROM products WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $check_stmt->bind_param('i', $product_id);
    if (!$check_stmt->execute()) {
        throw new Exception("Execute failed: " . $check_stmt->error);
    }
    
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        throw new Exception('Product not found');
    }

    $current_product = $check_result->fetch_assoc();

    // Get and validate product data
    $product_name = trim($_POST['product_name'] ?? '');
    $product_description = trim($_POST['product_description'] ?? '');
    $product_category = !empty($_POST['product_category']) ? intval($_POST['product_category']) : null;
    $product_price = !empty($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
    $product_sale_price = isset($_POST['product_sale_price']) && $_POST['product_sale_price'] !== '' ? floatval($_POST['product_sale_price']) : null;
    $product_status = isset($_POST['product_status']) ? intval($_POST['product_status']) : 1;

    // Validate required fields
    if (empty($product_name)) {
        throw new Exception('Product name is required');
    }

    // Handle image upload
    $product_image = $current_product['image']; // Keep existing image by default
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type and size
        if (!in_array($_FILES['product_image']['type'], $allowed_types)) {
            throw new Exception('Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.');
        }
        
        if ($_FILES['product_image']['size'] > $max_size) {
            throw new Exception('Image is too large. Maximum size is 5MB.');
        }
        
        // Create product images directory if it doesn't exist
        $upload_dir = '../images/products/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            // Delete old image if it exists
            if (!empty($current_product['image']) && file_exists($upload_dir . $current_product['image'])) {
                unlink($upload_dir . $current_product['image']);
            }
            $product_image = $new_filename;
        } else {
            throw new Exception('Failed to upload image');
        }
    }

    // Debug info
    error_log("Updating product: ID=$product_id, Name=$product_name, Category=$product_category, Price=$product_price, Sale_price=" . var_export($product_sale_price, true));

    // Update product in database
    if ($product_sale_price === null) {
        $update_sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    category_id = ?, 
                    price = ?, 
                    sale_price = NULL, 
                    image = ?, 
                    status = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param('ssidsii', $product_name, $product_description, $product_category, $product_price, $product_image, $product_status, $product_id);
    } else {
        $update_sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    category_id = ?, 
                    price = ?, 
                    sale_price = ?, 
                    image = ?, 
                    status = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param('ssiddsii', $product_name, $product_description, $product_category, $product_price, $product_sale_price, $product_image, $product_status, $product_id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $response = [
        'success' => true,
        'message' => 'Product updated successfully',
        'product_id' => $product_id
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    // Log error
    error_log("Product update error: " . $e->getMessage());
    
    // Return error response
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    echo json_encode($response);
}

// Close connection
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?> 