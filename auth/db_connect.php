<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration file if it exists
if (file_exists(__DIR__ . '/../config/database.php')) {
    include_once __DIR__ . '/../config/database.php';
}

// Database configuration
$host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost');
$dbname = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'addazjgu_call_girls');
$username = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'addazjgu_root2');
$password = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : 'addazjgu_call_girls');

// Log database configuration (without password)
// error_log("Database config - Host: $host, DB: $dbname, User: $username");

// Create connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    error_log("Database connection successful");
    
    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Failed to set charset: " . $conn->error);
        throw new Exception("Failed to set charset: " . $conn->error);
    }
    
    // Test the connection with a simple query
    $test_query = "SELECT 1";
    if (!$conn->query($test_query)) {
        error_log("Test query failed: " . $conn->error);
        throw new Exception("Test query failed: " . $conn->error);
    }
    
    error_log("Database connection test successful");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die("Database connection failed: " . $e->getMessage());
}