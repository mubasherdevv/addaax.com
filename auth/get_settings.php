<?php
require_once 'db_connect.php';

/**
 * Get a specific website setting or all settings
 * 
 * @param string $key Optional setting key to retrieve
 * @return mixed The setting value or array of all settings
 */
function getWebsiteSettings($key = null) {
    global $conn;
    
    // Check if settings table exists
    $table_check = "SHOW TABLES LIKE 'website_settings'";
    $result = $conn->query($table_check);
    
    if ($result->num_rows == 0) {
        return ($key === null) ? [] : null;
    }
    
    // Get the first row of settings (the new structure stores everything in one row)
    $sql = "SELECT * FROM website_settings LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Map new columns to legacy keys for compatibility
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

        // Merge in social links if they exist
        if (!empty($row['social_links'])) {
            $social = json_decode($row['social_links'], true);
            if (is_array($social)) {
                $settings = array_merge($settings, $social);
            }
        }

        if ($key !== null) {
            return $settings[$key] ?? null;
        }
        
        return $settings;
    }
    
    return ($key === null) ? [] : null;
}


// Handle AJAX requests for settings
if (isset($_GET['key'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'value' => getWebsiteSettings($_GET['key'])
    ]);
    exit;
} elseif (isset($_GET['all']) && $_GET['all'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'settings' => getWebsiteSettings()
    ]);
    exit;
}
?> 