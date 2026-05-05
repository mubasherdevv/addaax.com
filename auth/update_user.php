<?php
/**
 * update_user.php - Robust user update endpoint
 */

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once 'session.php';
    require_once 'db_connect.php';

    // Re-override to ensure clean output
    error_reporting(0);
    ini_set('display_errors', 0);

    if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $user_id = intval($_POST['id'] ?? 0);
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $is_verified = isset($_POST['is_verified']) ? intval($_POST['is_verified']) : 1;
    $password = $_POST['password'] ?? '';

    if ($user_id <= 0 || empty($first_name) || empty($last_name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        exit;
    }

    // Check if email is already used by another user
    $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($email_check);
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use by another user']);
        exit;
    }
    $stmt->close();

    // Prevent self-role change from admin if needed
    if ($user_id == $_SESSION['user_id'] && $role != 'admin') {
        echo json_encode(['success' => false, 'message' => 'You cannot change your own role from admin']);
        exit;
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, is_verified = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisi", $first_name, $last_name, $email, $role, $is_verified, $hashed_password, $user_id);
    } else {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, is_verified = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $first_name, $last_name, $email, $role, $is_verified, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>