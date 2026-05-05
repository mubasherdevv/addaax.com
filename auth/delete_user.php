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
    $response['message'] = 'No user ID provided';
    echo json_encode($response);
    exit;
}

$user_id = intval($_POST['id']);

// Don't allow deletion of own account
if ($user_id == $_SESSION['user_id']) {
    $response['message'] = 'You cannot delete your own account';
    echo json_encode($response);
    exit;
}

try {
    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully';
        } else {
            $response['message'] = 'User not found';
        }
    } else {
        $response['message'] = 'Error executing deletion: ' . $stmt->error;
    }
    
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Close connection
$conn->close();

// Return response as JSON
echo json_encode($response);
?> 