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

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['category_name'])) {
        $response['message'] = 'Category name is required';
        echo json_encode($response);
        exit;
    }
    
    // Get and sanitize form data
    $category_name = trim($_POST['category_name']);
    $category_description = isset($_POST['category_description']) ? trim($_POST['category_description']) : null;
    $parent_id = isset($_POST['parent_category']) && intval($_POST['parent_category']) > 0 ? intval($_POST['parent_category']) : null;
    $category_icon = isset($_POST['category_icon']) ? trim($_POST['category_icon']) : null;
    $category_status = isset($_POST['category_status']) ? intval($_POST['category_status']) : 1;
    
    // Handle image upload if present
    $image_filename = null;
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $upload_dir = '../images/categories/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION));
        
        // Validate file extension
        if (in_array($file_extension, $allowed_extensions)) {
            // Generate a unique filename
            $image_filename = 'category_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $upload_path)) {
                // Image upload successful
            } else {
                $response['message'] = 'Failed to upload image';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
            echo json_encode($response);
            exit;
        }
    }
    
    try {
        // Prepare SQL statement
        $sql = "INSERT INTO categories (name, description, parent_id, icon, image, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $category_name, $category_description, $parent_id, $category_icon, $image_filename, $category_status);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Category added successfully';
            $response['category_id'] = $conn->insert_id;
        } else {
            $response['message'] = 'Error adding category: ' . $stmt->error;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Close connection
$conn->close();

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?> 