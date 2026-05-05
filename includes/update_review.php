<?php
session_start();
require_once __DIR__ . '/config.php';

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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, 'You must be logged in to edit a review');
}

// Validate input
if (!isset($_POST['review_id']) || !isset($_POST['review_text'])) {
    sendJsonResponse(false, 'Missing required fields');
}

$review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
$review_text = trim($_POST['review_text']);
$user_id = $_SESSION['user_id'];

// Validate review_id
if (!$review_id) {
    sendJsonResponse(false, 'Invalid review ID');
}

// Validate review text
if (strlen($review_text) < 10 || strlen($review_text) > 1000) {
    sendJsonResponse(false, 'Review must be between 10 and 1000 characters');
}

try {
    // Check if review exists and belongs to the user
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $review_id, $user_id);
    if (!$stmt->execute()) {
        sendJsonResponse(false, 'Database error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Review not found or you do not have permission to edit it');
    }

    // Update the review
    $stmt = $conn->prepare("UPDATE reviews SET review_text = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("sii", $review_text, $review_id, $user_id);
    if (!$stmt->execute()) {
        sendJsonResponse(false, 'Database error: ' . $stmt->error);
    }

    // Return success response with updated review details
    sendJsonResponse(true, 'Review updated successfully', [
        'review' => [
            'text' => htmlspecialchars($review_text)
        ]
    ]);

} catch (Exception $e) {
    sendJsonResponse(false, 'Error updating review: ' . $e->getMessage());
} 