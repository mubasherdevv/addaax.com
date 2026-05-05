<?php
// Only start session if one hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the current URL to redirect back after login
    // Extract just the path part after /backup/ to avoid double paths
    $current_url = $_SERVER['REQUEST_URI'];
    if (preg_match('#/backup/(.*)#', $current_url, $matches)) {
        $_SESSION['redirect_url'] = $matches[1];
    } else {
        $_SESSION['redirect_url'] = $current_url;
    }
    
    // Redirect to login page with the correct path
    header("Location: /backup/auth/login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Optional: You can add additional user verification here
// For example, checking if the user exists in the database
// or if their account is active
?> 