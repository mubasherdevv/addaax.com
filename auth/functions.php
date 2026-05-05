<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection if not already included
if (!function_exists('getDBConnection')) {
    require_once 'db_connect.php';
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

/**
 * Check if user is admin
 * @return bool True if user is admin, false otherwise
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

/**
 * Get specific website setting by key
 * @param string $key Setting key
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value or default
 */
if (!function_exists('getSetting')) {
    function getSetting($key, $default = null) {
        $settings = getWebsiteSettings();
        return $settings[$key] ?? $default;
    }
}

/**
 * Get all website settings
 * @return array Array of all website settings
 */
if (!function_exists('getWebsiteSettings')) {
    function getWebsiteSettings() {
        global $conn;
        
        if (!$conn) {
            require_once 'db_connect.php';
        }
        
        // Use the new columnar structure
        $sql = "SELECT * FROM website_settings LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Map new columns to legacy keys
            $settings = [
                'website_name' => $row['site_name'] ?? '',
                'company_name' => $row['site_name'] ?? '',
                'website_logo' => $row['logo'] ?? '',
                'favicon' => $row['favicon'] ?? '',
                'company_email' => $row['contact_email'] ?? '',
                'currency' => $row['currency'] ?? 'Rs',
                'maintenance_mode' => $row['maintenance_mode'] ?? 0,
                'maintenance_message' => $row['maintenance_message'] ?? '',
                'featured_ad_price' => $row['featured_ad_price'] ?? 0,
            ];

            // Handle social links
            if (!empty($row['social_links'])) {
                $social = json_decode($row['social_links'], true);
                if (is_array($social)) {
                    $settings = array_merge($settings, $social);
                }
            }

            return $settings;
        }
        
        return [];
    }
}


/**
 * Get user information by ID
 * @param int $user_id User ID
 * @return array|null User data or null if not found
 */
if (!function_exists('getUserById')) {
    function getUserById($user_id) {
        global $conn;
        
        if (!$conn) {
            require_once 'db_connect.php';
        }
        
        $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, phone, address, city, state, zip, country, role, status, created_at FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        
        return null;
    }
}

/**
 * Format price with currency symbol
 * @param float $price Price to format
 * @param string $currency Currency symbol
 * @return string Formatted price
 */
if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = '$') {
        return $currency . number_format($price, 2);
    }
}

/**
 * Format currency amount with currency symbol
 * @param float $amount Amount to format
 * @param string $currency Currency symbol
 * @return string Formatted amount
 */
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = '$') {
        return $currency . number_format((float)$amount, 2, '.', ',');
    }
}

/**
 * Calculate subtotal from cart items
 * @param array $items Cart items
 * @return float Subtotal
 */
if (!function_exists('calculateSubtotal')) {
    function calculateSubtotal($items) {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }
}

/**
 * Log activity to database
 * @param string $activity Activity description
 * @param int $user_id User ID (optional)
 * @return bool True if logged successfully, false otherwise
 */
if (!function_exists('logActivity')) {
    function logActivity($activity, $user_id = null) {
        global $conn;
        
        if (!$conn) {
            require_once 'db_connect.php';
        }
        
        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity, ip_address, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt->bind_param("iss", $user_id, $activity, $ip);
            return $stmt->execute();
        }
        
        return false;
    }
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

/**
 * Generate a random alphanumeric string
 * @param int $length Length of the string
 * @return string Random string
 */
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        
        return $randomString;
    }
}

// Common utility functions

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Format date to readable format
 */
function format_date($date) {
    return date('M d, Y H:i', strtotime($date));
}

/**
 * Format number with thousands separator
 */
function format_number($number) {
    return number_format($number, 0, '.', ',');
}

/**
 * Get current date and time in MySQL format
 */
function get_current_datetime() {
    return date('Y-m-d H:i:s');
}

/**
 * Log error message
 */
function log_error($message) {
    error_log($message);
}

/**
 * Send JSON response
 */
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?> 