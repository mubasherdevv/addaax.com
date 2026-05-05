<?php
// Prevent any output before headers
ob_start();

// Disable error display but log errors
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Clear any previous output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

// Debug function to log errors
function logError($message) {
    error_log("Review Error: " . $message);
}

// Log the request method and POST data
logError("Request method: " . $_SERVER['REQUEST_METHOD']);
logError("POST data: " . print_r($_POST, true));
logError("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    logError("User not logged in");
    sendJsonResponse(false, 'You must be logged in to submit a review');
}

// Validate input
if (!isset($_POST['product_id']) || !isset($_POST['review_text'])) {
    logError("Missing required fields: " . print_r($_POST, true));
    sendJsonResponse(false, 'Missing required fields');
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$review_text = trim($_POST['review_text']);
$user_id = $_SESSION['user_id'];

// Log request data for debugging
logError("Request data - product_id: $product_id, user_id: $user_id, review_text length: " . strlen($review_text));

// Validate product_id
if (!$product_id) {
    logError("Invalid product ID: " . $_POST['product_id']);
    sendJsonResponse(false, 'Invalid product ID');
}

// Validate review text
if (strlen($review_text) < 10 || strlen($review_text) > 1000) {
    logError("Invalid review text length: " . strlen($review_text));
    sendJsonResponse(false, 'Review must be between 10 and 1000 characters');
}

try {
    // Check if product exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    if (!$stmt) {
        logError("Prepare error: " . $conn->error);
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        logError("Execute error: " . $stmt->error);
        sendJsonResponse(false, 'Database error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        logError("Product not found: " . $product_id);
        sendJsonResponse(false, 'Product not found');
    }

    // Insert the review
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, review_text, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        logError("Prepare error: " . $conn->error);
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("iis", $user_id, $product_id, $review_text);
    if (!$stmt->execute()) {
        logError("Execute error: " . $stmt->error);
        sendJsonResponse(false, 'Database error: ' . $stmt->error);
    }

    // Get user's name for the response
    $stmt = $conn->prepare("SELECT first_name, last_name, profile_image FROM users WHERE id = ?");
    if (!$stmt) {
        logError("Prepare error: " . $conn->error);
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        logError("Execute error: " . $stmt->error);
        sendJsonResponse(false, 'Database error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        logError("User not found: " . $user_id);
        sendJsonResponse(false, 'Error retrieving user information');
    }
    
    $user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    $profile_image = !empty($user['profile_image']) ? $user['profile_image'] : '';

    // Return success response with review details
    logError("Review added successfully for user: $user_id, product: $product_id");
    sendJsonResponse(true, 'Review added successfully', [
        'review' => [
            'user_name' => $user_name,
            'text' => htmlspecialchars($review_text),
            'created_at' => date('M d, Y h:i A'),
            'profile_image' => $profile_image
        ]
    ]);

} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    sendJsonResponse(false, 'Error saving review: ' . $e->getMessage());
} 