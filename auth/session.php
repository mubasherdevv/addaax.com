<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    error_log("Starting new session");
    
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    
    session_start();
    error_log("Session started with ID: " . session_id());
}

// Function to check if user is logged in
function isLoggedIn() {
    $loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    error_log("isLoggedIn check: " . ($loggedIn ? "true" : "false"));
    if ($loggedIn) {
        error_log("User ID: " . $_SESSION['user_id']);
    }
    return $loggedIn;
}

// Function to check if user is admin
function isAdmin() {
    $isAdmin = isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    error_log("isAdmin check: " . ($isAdmin ? "true" : "false"));
    if ($isAdmin) {
        error_log("User role: " . $_SESSION['user_role']);
    }
    return $isAdmin;
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        error_log("Login required - redirecting to login page");
        $_SESSION['error'] = "You must be logged in to access this page";
        header("Location: login.php");
        exit;
    }
}

// Function to redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        error_log("Admin access required - redirecting to index");
        $_SESSION['error'] = "You don't have permission to access this page";
        header("Location: ../index.php");
        exit;
    }
}

// Function to set flash message
function setFlashMessage($type, $message) {
    error_log("Setting flash message - Type: $type, Message: $message");
    $_SESSION[$type] = $message;
}

// Function to display flash message and clear it
function displayFlashMessage($type) {
    if (isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        error_log("Displaying flash message - Type: $type, Message: $message");
        unset($_SESSION[$type]);
        return $message;
    }
    return null;
}
?> 