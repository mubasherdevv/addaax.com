<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php';

// Require login to access this API
requireLogin();

// Set header for JSON response
header('Content-Type: application/json');

// Function to upload image and return the filename
function uploadImage($file, $target_dir = "../images/") {
    // Create target directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Get file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Only allow certain file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
        ];
    }
    
    // Generate a unique filename
    $new_filename = uniqid('profile_') . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if file is an actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return [
            'success' => false,
            'message' => 'File is not an image'
        ];
    }
    
    // Check file size (limit to 5MB)
    if ($file['size'] > 5000000) {
        return [
            'success' => false,
            'message' => 'File is too large (max 5MB)'
        ];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return [
            'success' => true,
            'filename' => $new_filename
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error uploading file'
        ];
    }
}

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get user ID from session
        $user_id = $_SESSION['user_id'];
        
        // Get form data
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'First name, last name, and email are required'
            ]);
            exit;
        }
        
        // Validate email format
        if (!isValidEmail($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email format'
            ]);
            exit;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Email is already in use by another account'
            ]);
            $conn->rollback();
            exit;
        }
        
        // Handle password change if requested
        if (!empty($new_password)) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!password_verify($current_password, $user['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
                $conn->rollback();
                exit;
            }
            
            // Password requirements
            if (strlen($new_password) < 8) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Password must be at least 8 characters long'
                ]);
                $conn->rollback();
                exit;
            }
            
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        // Handle profile image upload if provided
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['profile_image']);
            
            if (!$upload_result['success']) {
                echo json_encode([
                    'success' => false,
                    'message' => $upload_result['message']
                ]);
                $conn->rollback();
                exit;
            }
            
            $profile_image = $upload_result['filename'];
        }
        
        // Check if profile_image column exists in users table
        $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
        if ($column_check->num_rows === 0) {
            // Add profile_image column if it doesn't exist
            $add_column_query = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg'";
            if (!$conn->query($add_column_query)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to add profile_image column: ' . $conn->error
                ]);
                if ($conn->inTransaction()) {
                    $conn->rollback();
                }
                exit;
            }
        }
        
        // Build update query based on what's changing
        $query_parts = [
            "first_name = ?",
            "last_name = ?",
            "email = ?",
            "phone = ?"
        ];
        $param_types = "ssss";
        $params = [$first_name, $last_name, $email, $phone];
        
        // Add password to update if changing
        if (!empty($new_password)) {
            $query_parts[] = "password = ?";
            $param_types .= "s";
            $params[] = $hashed_password;
        }
        
        // Add profile image to update if provided
        if ($profile_image !== null) {
            $query_parts[] = "profile_image = ?";
            $param_types .= "s";
            $params[] = $profile_image;
        }
        
        // Add user_id parameter for WHERE clause
        $param_types .= "i";
        $params[] = $user_id;
        
        // Build and execute update query
        $query = "UPDATE users SET " . implode(", ", $query_parts) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($param_types, ...$params);
        $result = $stmt->execute();
        
        if ($result) {
            // Update session display name
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            
            // Prepare success response
            $response = [
                'success' => true,
                'message' => 'Profile updated successfully',
                'user_name' => $_SESSION['user_name']
            ];
            
            // Include profile image in response if updated
            if ($profile_image !== null) {
                $response['profile_image'] = $profile_image;
            }
            
            echo json_encode($response);
            $conn->commit();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating profile: ' . $conn->error
            ]);
            $conn->rollback();
        }
    } catch (Exception $e) {
        // Handle any unexpected errors
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    // Handle non-POST requests
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 