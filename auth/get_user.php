<?php
/**
 * get_user.php - Robust JSON endpoint for user data
 */

// Force JSON and suppress any potential text errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    // These might have internal error_reporting calls, we'll override them after
    require_once 'session.php';
    require_once 'db_connect.php';

    // Re-override to ensure clean output
    error_reporting(0);
    ini_set('display_errors', 0);

    // Check if user is logged in and is an admin
    if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Check if user ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    $user_id = intval($_GET['id']);

    // Get user details
    $sql = "SELECT id, first_name, last_name, email, company_name, role, is_verified 
            FROM users WHERE id = ?";
    
    if (!$conn) {
        throw new Exception("Database connection not available");
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Database execute error: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $user = $result->fetch_assoc();
    echo json_encode(['success' => true, 'user' => $user]);

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    // Catch-all for any errors (database, syntax, etc)
    echo json_encode([
        'success' => false, 
        'message' => 'Internal Server Error: ' . $e->getMessage()
    ]);
}
?>