<?php
// Include database connection and session
require_once 'session.php';
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check if ID was provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $response['message'] = 'No category ID provided';
    echo json_encode($response);
    exit;
}

$category_id = intval($_POST['id']);

try {
    // Start transaction
    $conn->begin_transaction();
    
    // First, get the category to find its image file
    $get_sql = "SELECT image FROM categories WHERE id = ?";
    $get_stmt = $conn->prepare($get_sql);
    $get_stmt->bind_param("i", $category_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $category = $result->fetch_assoc();
    $get_stmt->close();
    
    // Update products with this category to have no category
    $update_products_sql = "UPDATE products SET category_id = NULL WHERE category_id = ?";
    $update_products_stmt = $conn->prepare($update_products_sql);
    $update_products_stmt->bind_param("i", $category_id);
    $update_products_stmt->execute();
    $update_products_stmt->close();
    
    // Update subcategories to have no parent
    $update_subcategories_sql = "UPDATE categories SET parent_id = NULL WHERE parent_id = ?";
    $update_subcategories_stmt = $conn->prepare($update_subcategories_sql);
    $update_subcategories_stmt->bind_param("i", $category_id);
    $update_subcategories_stmt->execute();
    $update_subcategories_stmt->close();
    
    // Delete the category
    $delete_sql = "DELETE FROM categories WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $category_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            // Delete the image file if exists
            if (!empty($category['image'])) {
                $image_path = '../images/categories/' . $category['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Category deleted successfully';
        } else {
            $response['message'] = 'Category not found';
        }
    } else {
        $response['message'] = 'Error deleting category: ' . $delete_stmt->error;
    }
    
    $delete_stmt->close();
    
    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Close connection
$conn->close();

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?> 