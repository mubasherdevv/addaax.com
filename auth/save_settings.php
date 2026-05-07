<?php
require_once 'session.php';
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$settings = [];

// Only process settings that were actually submitted
foreach ($_POST as $key => $value) {
    if ($key !== 'submit') { // Skip submit button
        $settings[$key] = $value;
    }
}

// Handle file uploads
if (!empty($_FILES['website_logo']['name'])) {
    $target_dir = "../images/";
    $logo_file = $target_dir . basename($_FILES["website_logo"]["name"]);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($logo_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES["website_logo"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['success' => false, 'message' => 'File is not an image']);
        exit;
    }
    
    // Allow certain file formats
    if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif" && $image_file_type != "svg") {
        echo json_encode(['success' => false, 'message' => 'Sorry, only JPG, JPEG, PNG, GIF & SVG files are allowed']);
        exit;
    }
    
    // Check file size (limit to 2MB)
    if ($_FILES["website_logo"]["size"] > 2000000) {
        echo json_encode(['success' => false, 'message' => 'Sorry, your file is too large (max 2MB)']);
        exit;
    }
    
    if (move_uploaded_file($_FILES["website_logo"]["tmp_name"], $logo_file)) {
        $settings['website_logo'] = basename($_FILES["website_logo"]["name"]);
    }
}

// Handle favicon upload
if (!empty($_FILES['favicon']['name'])) {
    $target_dir = "../images/";
    $favicon_file = $target_dir . basename($_FILES["favicon"]["name"]);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($favicon_file, PATHINFO_EXTENSION));
    
    // Check file size (limit to 1MB)
    if ($_FILES["favicon"]["size"] > 1000000) {
        echo json_encode(['success' => false, 'message' => 'Sorry, your favicon is too large (max 1MB)']);
        exit;
    }
    
    if (move_uploaded_file($_FILES["favicon"]["tmp_name"], $favicon_file)) {
        $settings['favicon'] = basename($_FILES["favicon"]["name"]);
    }
}

// Map legacy keys to new columns
$key_to_column = [
    'website_name' => 'site_name',
    'company_name' => 'site_name', // Mapping both to site_name as they are often used interchangeably
    'website_logo' => 'logo',
    'favicon' => 'favicon',
    'company_email' => 'contact_email',
    'currency' => 'currency',
    'maintenance_mode' => 'maintenance_mode',
    'maintenance_message' => 'maintenance_message',
    'featured_ad_price' => 'featured_ad_price',
    'header_style' => 'header_style'
];

// We'll update the single row of settings
$update_parts = [];
$update_values = [];
$types = "";

foreach ($settings as $key => $value) {
    if (isset($key_to_column[$key])) {
        $col = $key_to_column[$key];
        $update_parts[] = "$col = ?";
        $update_values[] = $value;
        $types .= "s";
    }
}

if (!empty($update_parts)) {
    $sql = "UPDATE website_settings SET " . implode(", ", $update_parts) . " LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$update_values);
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Website settings saved successfully',
                'settings' => $settings
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update settings: ' . $conn->error]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'No settings to update']);
}

$conn->close();
?>
 