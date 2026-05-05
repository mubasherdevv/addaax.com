<?php
require_once '../config/database.php';
require_once 'session.php';
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$products_text = isset($_POST['products_text']) ? trim($_POST['products_text']) : '';
$default_category = isset($_POST['default_category']) ? intval($_POST['default_category']) : 0;

// Validate input
if (empty($products_text)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No products provided']);
    exit;
}

if ($default_category <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid default category']);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Prepare the insert statement
    $insert_sql = "INSERT INTO products (name, category_id, price, created_at, updated_at) 
                   VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insert_sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    // Split the input into lines
    $lines = explode("\n", $products_text);
    $success_count = 0;
    $error_messages = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Split the line into parts
        $parts = array_map('trim', explode('|', $line));
        
        // Validate parts
        if (count($parts) < 3) {
            $error_messages[] = "Invalid format for line: $line";
            continue;
        }

        // Extract product details
        $name = $parts[0];
        $category_name = $parts[1];
        $price = floatval($parts[2]);

        // Validate required fields
        if (empty($name) || $price <= 0) {
            $error_messages[] = "Invalid name or price for product: $name";
            continue;
        }

        // Get category ID
        $category_id = $default_category;
        if (!empty($category_name)) {
            $category_sql = "SELECT id FROM categories WHERE name = ?";
            $category_stmt = $conn->prepare($category_sql);
            $category_stmt->bind_param('s', $category_name);
            $category_stmt->execute();
            $category_result = $category_stmt->get_result();
            
            if ($category_result->num_rows > 0) {
                $category_id = $category_result->fetch_assoc()['id'];
            }
        }

        // Insert the product
        $stmt->bind_param('sid', $name, $category_id, $price);
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_messages[] = "Failed to add product: $name";
        }
    }

    // Commit transaction
    $conn->commit();

    // Prepare response
    $message = "Successfully added $success_count products.";
    if (!empty($error_messages)) {
        $message .= " Errors: " . implode(", ", $error_messages);
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'added_count' => $success_count,
        'errors' => $error_messages
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close statement and connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 