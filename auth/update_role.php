<?php
require_once 'session.php';
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$role = isset($_POST['role']) ? $_POST['role'] : '';
$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

// Validate inputs
if ($user_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if (!in_array($role, ['admin', 'product_manager', 'order_manager', 'user'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

// Convert permissions array to JSON
$permissions_json = !empty($permissions) ? json_encode($permissions) : NULL;

// Update user role and permissions
$sql = "UPDATE users SET role = ?, permissions = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $role, $permissions_json, $user_id);

$response = [];
if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => 'User role and permissions updated successfully'
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Failed to update user role: ' . $conn->error
    ];
}

// Close database connection
$stmt->close();
$conn->close();

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 