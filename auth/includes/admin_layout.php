<?php
require_once __DIR__ . '/../get_settings.php';
/**
 * Admin Layout Functions for ADAAX
 */

/**
 * Renders the Admin Sidebar
 * @param string $active_tab The currently active menu item
 */
function renderAdminSidebar($active_tab = 'dashboard') {
    global $conn;
    
    // Get admin profile image and name from session
    $admin_id = $_SESSION['user_id'] ?? 0;
    $admin_name = $_SESSION['user_name'] ?? 'Admin';
    $profile_image = 'admin-avatar.jpg'; // Default
    
    // Check for actual profile image in DB
    $img_query = "SELECT profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($img_query);
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['profile_image'])) {
                $profile_image = $row['profile_image'];
            }
        }
    }
    ?>
    <!-- Sidebar toggle button for mobile -->
    <button class="sidebar-toggle" aria-label="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay"></div>
    
    <aside class="admin-sidebar">
        <div class="admin-profile">
            <div class="profile-img-wrapper">
                <img src="<?php echo BASE_URL; ?>/images/<?php echo htmlspecialchars($profile_image); ?>" alt="Admin Profile">
            </div>
            <h3><?php echo htmlspecialchars($admin_name); ?></h3>
            <span class="admin-badge">Super Admin</span>
        </div>
        
        <ul class="admin-menu">
            <li class="menu-label">Main</li>
            <li>
                <a href="admin_dashboard.php?tab=dashboard" class="<?php echo ($active_tab == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li class="menu-label">Management</li>
            <li>
                <a href="product_management.php" class="<?php echo ($active_tab == 'products') ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>
            <li>
                <a href="city_management.php" class="<?php echo ($active_tab == 'cities') ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> Cities
                </a>
            </li>
            <li>
                <a href="user_management.php" class="<?php echo ($active_tab == 'users') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
            <li>
                <a href="seo_management.php" class="<?php echo ($active_tab == 'seo') ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i> SEO Settings
                </a>
            </li>
            
            <li class="menu-label">Settings</li>
            <li>
                <a href="admin_dashboard.php?tab=website_settings" class="<?php echo ($active_tab == 'settings') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Web Settings
                </a>
            </li>
            <li>
                <a href="admin_dashboard.php?tab=my_profile" class="<?php echo ($active_tab == 'profile') ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
            </li>
            
            <li class="menu-label">Exit</li>
            <li>
                <a href="../index.php">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
            </li>
            <li>
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>
    <?php
}

/**
 * Renders the Admin Header
 * @param string $page_title The title of the page
 */
function renderAdminHeader($page_title = 'Admin Panel') {
    global $conn, $website_name, $website_logo, $favicon;
    
    // Fetch settings if not already available
    if (!isset($website_name) || !isset($website_logo)) {
        if (function_exists('getWebsiteSettings')) {
            $settings = getWebsiteSettings();
            $website_name = $website_name ?? $settings['website_name'] ?? 'ADAAX';
            $website_logo = $website_logo ?? $settings['website_logo'] ?? 'logo.jpg';
            $favicon = $favicon ?? $settings['favicon'] ?? '';
        }
    }
    
    // Get admin profile image from session/DB for the header pill
    $admin_id = $_SESSION['user_id'] ?? 0;
    $profile_image = 'admin-avatar.jpg'; // Default
    
    if (isset($conn)) {
        $img_query = "SELECT profile_image FROM users WHERE id = ?";
        $stmt = $conn->prepare($img_query);
        if ($stmt) {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (!empty($row['profile_image'])) {
                    $profile_image = $row['profile_image'];
                }
            }
        }
    }
    
    // Final check for logo
    $logo_to_show = $website_logo ?? 'logo.jpg';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($page_title); ?> | <?php echo htmlspecialchars($website_name ?? 'ADAAX'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php if (!empty($favicon)): ?>
    <link rel="icon" href="<?php echo BASE_URL; ?>/images/<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
        .admin-top-nav { 
            background: #ffffff !important; 
            height: 70px !important; 
            display: flex !important; 
            align-items: center !important; 
            position: sticky !important; 
            top: 0 !important; 
            z-index: 1001 !important; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important; 
            padding: 0 20px !important;
            width: 100% !important;
            left: 0 !important;
        }
        .admin-logo { 
            display: flex !important; 
            align-items: center !important; 
            gap: 12px !important; 
            text-decoration: none !important; 
        }
        .admin-logo img { 
            height: 40px !important; 
            width: auto !important; 
            object-fit: contain !important; 
        }
        .admin-logo span { 
            font-weight: 800 !important; 
            font-size: 1.25rem !important; 
            color: #0f172a !important;
            display: inline-block !important;
        }
        .admin-body { 
            background-color: #f1f5f9 !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            font-family: 'Outfit', sans-serif !important;
        }
        .admin-nav-actions {
            display: flex !important;
            align-items: center !important;
            gap: 20px !important;
            margin-left: auto !important;
        }
        .admin-container { display: flex !important; flex-direction: row !important; min-height: calc(100vh - 70px) !important; width: 100% !important; position: relative !important; }
        .admin-sidebar { width: 260px !important; background: #0f172a !important; flex-shrink: 0 !important; color: #ffffff !important; z-index: 1000 !important; }
        .admin-content { flex: 1 !important; padding: 30px !important; background: #f1f5f9 !important; min-width: 0 !important; position: relative !important; }
        .sidebar-toggle { z-index: 2000 !important; }
    </style>
</head>
<body class="admin-body">
    <header class="admin-top-nav">
        <div class="container-fluid" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <a href="<?php echo BASE_URL; ?>/index.php" class="admin-logo">
                <img src="<?php echo BASE_URL; ?>/images/<?php echo htmlspecialchars($logo_to_show); ?>" alt="Logo" height="40" onerror="this.src='<?php echo BASE_URL; ?>/images/logo.jpg'; this.onerror=null;">
                <span>ADAAX ADMIN</span>
            </a>
            
            <div class="admin-nav-actions">
                <a href="admin_dashboard.php?tab=my_profile" class="btn-user-dash" title="User Dashboard"><i class="fas fa-user"></i></a>
                <div class="admin-user-pill" style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 5px 15px 5px 5px; border-radius: 100px; border: 1px solid #e2e8f0;">
                    <img src="<?php echo BASE_URL; ?>/images/<?php echo htmlspecialchars($profile_image); ?>" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=random';">
                    <span style="font-weight: 600; font-size: 0.9rem; color: #1e293b;"><?php echo htmlspecialchars($_SESSION["user_name"] ?? 'Admin'); ?></span>
                </div>
            </div>
        </div>
    </header>
    <div class="admin-container">
<?php
}

/**
 * Renders the Admin Footer
 */
function renderAdminFooter() {
    ?>
    </div> <!-- .admin-container -->
    <script>
        $(document).ready(function() {
            // Sidebar Toggle
            $('.sidebar-toggle, .sidebar-overlay').on('click', function() {
                $('.admin-sidebar').toggleClass('active');
                $('.sidebar-overlay').toggleClass('active');
                $('body').toggleClass('sidebar-open');
            });
        });
    </script>
</body>
</html>
    <?php
}
