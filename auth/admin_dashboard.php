<?php
require_once '../config/database.php';  // Database constants
require_once '../config/constants.php'; // Application constants
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php'; // Include website settings

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    // Redirect to login page if not admin
    header("Location: login.php");
    exit;
}

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$header_style = $website_settings['header_style'] ?? 'logo';
$favicon = $website_settings['favicon'] ?? '';

// Fetch all users from the database
$users = [];
$users_sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, company_name AS company, role, 
                    IF(is_verified = 1, 'Active', 'Inactive') AS status, 
                    created_at AS last_login 
               FROM users ORDER BY id";
$users_result = $conn->query($users_sql);
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get admin details
$admin_id = $_SESSION["user_id"];
$sql = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Active tab handling
$active_tab = $_GET['tab'] ?? 'dashboard';

// Helper function to format currency values
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}

// Close connection
// $conn->close();
?>

<?php
require_once 'includes/admin_layout.php';

// Map tab names for the sidebar active state
$tab_map = [
    'dashboard' => 'dashboard',
    'website_settings' => 'settings',
    'my_profile' => 'profile'
];
$sidebar_active = $tab_map[$active_tab] ?? 'dashboard';

renderAdminHeader('Admin Dashboard');
renderAdminSidebar($sidebar_active);
?>

        <!-- Add styles for the profile tab -->
        <style>
        /* Profile Styles */
        .profile-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 0;
            overflow: hidden;
            margin: 20px 0;
        }
        
        /* Fix for sidebar profile image */
        .admin-sidebar .admin-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto 10px;
            background-color: #fff; /* Ensure background while loading */
            min-width: 80px; /* Prevent collapse */
            min-height: 80px; /* Prevent collapse */
        }
        
        .profile-header {
            display: flex;
            padding: 40px;
            background: linear-gradient(135deg, #3f51b5 0%, #2196f3 100%);
            border-bottom: 1px solid #e5e7eb;
            color: white;
            align-items: center;
        }
        
        .profile-avatar {
            margin-right: 40px;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .profile-avatar img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .avatar-upload {
            margin-top: 15px;
            position: relative;
        }
        
        .avatar-upload label {
            background-color: rgba(255, 255, 255, 0.9);
            color: #3f51b5;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .avatar-upload label:hover {
            background-color: white;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-info h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 32px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .profile-info p {
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        
        .profile-info p i {
            width: 24px;
            color: rgba(255, 255, 255, 0.7);
            margin-right: 10px;
            font-size: 18px;
        }
        
        .profile-form-container {
            padding: 40px;
            background-color: #f9fafb;
        }
        
        .profile-form-container h3 {
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            color: #1e293b;
            font-weight: 600;
        }
        
        .form-row {
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #4b5563;
            font-size: 15px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .form-group input:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.2);
            outline: none;
        }
        
        .form-group input:hover {
            border-color: #6b7280;
        }
        
        button[type="submit"] {
            background-color: #3f51b5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }
        
        button[type="submit"]:hover {
            background-color: #303f9f;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        #profile-update-message {
            margin-top: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 15px;
            display: none;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        #profile-update-message.success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        #profile-update-message.error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        #profile-update-message.info {
            background-color: #e0f2fe;
            color: #0c4a6e;
            border-left: 4px solid #38bdf8;
        }
        
        /* Style form sections with cards */
        .profile-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .profile-section:last-child {
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }
            
            .profile-avatar {
                margin-right: 0;
                margin-bottom: 25px;
            }
            
            .profile-avatar img {
                width: 120px;
                height: 120px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .profile-form-container {
                padding: 25px 20px;
            }
            
            .profile-info h2 {
                font-size: 24px;
            }
        }
        </style>
        
        <!-- Main Content -->
        <main class="admin-content">
            <!-- Dashboard Tab -->
            <div class="admin-tab <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                <div class="admin-header">
                    <h1>Admin Dashboard</h1>
                    <div class="admin-actions">
                        <a href="#" class="btn btn-outline btn-icon"><i class="fas fa-download"></i> <span>Export Report</span></a>
                    </div>
                </div>
                
                <div class="dashboard-stats">
                    <a href="user_management.php" class="stat-card" style="text-decoration: none; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            // Get total users count
                            $users_count_sql = "SELECT COUNT(*) as total FROM users";
                            $users_count_result = $conn->query($users_count_sql);
                            $users_count = $users_count_result->fetch_assoc()['total'] ?? 0;
                            ?>
                            <h3><?php echo $users_count; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </a>
                    
                    <a href="product_management.php" class="stat-card" style="text-decoration: none; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            // Get total products count
                            $products_count_sql = "SELECT COUNT(*) as total FROM products";
                            $products_count_result = $conn->query($products_count_sql);
                            $products_count = $products_count_result->fetch_assoc()['total'] ?? 0;
                            ?>
                            <h3><?php echo $products_count; ?></h3>
                            <p>Total Ads</p>
                        </div>
                    </a>

                    <a href="product_management.php?featured=1" class="stat-card" style="text-decoration: none; transition: transform 0.2s; cursor: pointer; border-left: 4px solid #fbbf24;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.1); color: #fbbf24;">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            // Get featured products count
                            $featured_count_sql = "SELECT COUNT(*) as total FROM products WHERE is_featured = 1";
                            $featured_count_result = $conn->query($featured_count_sql);
                            $featured_count = $featured_count_result->fetch_assoc()['total'] ?? 0;
                            ?>
                            <h3 style="color: #fbbf24;"><?php echo $featured_count; ?></h3>
                            <p>Featured Ads</p>
                        </div>
                    </a>
                    
                    <a href="category_management.php" class="stat-card" style="text-decoration: none; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <?php
                            // Get total categories count
                            $categories_count_sql = "SELECT COUNT(*) as total FROM categories";
                            $categories_count_result = $conn->query($categories_count_sql);
                            $categories_count = $categories_count_result->fetch_assoc()['total'] ?? 0;
                            ?>
                            <h3><?php echo $categories_count; ?></h3>
                            <p>Categories</p>
                        </div>
                    </a>
                    

                </div>
                
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    
                    <?php
                    // Get recent activities (limit to 5)
                    $activities = array();
                    
                    // Get recent user registrations
                    $user_query = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, created_at FROM users ORDER BY created_at DESC LIMIT 3";
                    $user_result = $conn->query($user_query);
                    if ($user_result && $user_result->num_rows > 0) {
                        while ($user = $user_result->fetch_assoc()) {
                            $activities[] = array(
                                'type' => 'user',
                                'icon' => 'fas fa-user-plus',
                                'title' => 'New User Registration',
                                'details' => htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['email']) . ') has registered as a new user.',
                                'time' => strtotime($user['created_at']),
                                'time_str' => $user['created_at']
                            );
                        }
                    }
                    

                    
                    // Get recent product additions/updates
                    $product_query = "SELECT id, name, created_at, updated_at FROM products ORDER BY GREATEST(created_at, updated_at) DESC LIMIT 3";
                    $product_result = $conn->query($product_query);
                    if ($product_result && $product_result->num_rows > 0) {
                        while ($product = $product_result->fetch_assoc()) {
                            $is_new = $product['created_at'] == $product['updated_at'];
                            $activities[] = array(
                                'type' => 'product',
                                'icon' => 'fas fa-box',
                                'title' => $is_new ? 'New Product Added' : 'Product Updated',
                                'details' => htmlspecialchars($product['name']) . ' has been ' . ($is_new ? 'added to the store.' : 'updated.'),
                                'time' => $is_new ? strtotime($product['created_at']) : strtotime($product['updated_at']),
                                'time_str' => $is_new ? $product['created_at'] : $product['updated_at']
                            );
                        }
                    }
                    
                    // Get recent category additions/updates
                    $category_query = "SELECT id, name, created_at, updated_at FROM categories ORDER BY GREATEST(created_at, updated_at) DESC LIMIT 3";
                    $category_result = $conn->query($category_query);
                    if ($category_result && $category_result->num_rows > 0) {
                        while ($category = $category_result->fetch_assoc()) {
                            $is_new = $category['created_at'] == $category['updated_at'];
                            $activities[] = array(
                                'type' => 'category',
                                'icon' => 'fas fa-tag',
                                'title' => $is_new ? 'New Category Added' : 'Category Updated',
                                'details' => 'Category "' . htmlspecialchars($category['name']) . '" has been ' . ($is_new ? 'added.' : 'updated.'),
                                'time' => $is_new ? strtotime($category['created_at']) : strtotime($category['updated_at']),
                                'time_str' => $is_new ? $category['created_at'] : $category['updated_at']
                            );
                        }
                    }
                    
                    // Sort activities by time (most recent first)
                    usort($activities, function($a, $b) {
                        return $b['time'] - $a['time'];
                    });
                    
                    // Display activities (limit to 5)
                    $count = 0;
                    foreach ($activities as $activity) {
                        if ($count >= 5) break;
                        
                        // Format the time difference
                        $time_diff = time() - $activity['time'];
                        if ($time_diff < 60) {
                            $time_str = 'Just now';
                        } elseif ($time_diff < 3600) {
                            $mins = floor($time_diff / 60);
                            $time_str = $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
                        } elseif ($time_diff < 86400) {
                            $hours = floor($time_diff / 3600);
                            $time_str = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                        } elseif ($time_diff < 172800) {
                            $time_str = 'Yesterday';
                        } else {
                            $days = floor($time_diff / 86400);
                            $time_str = $days . ' days ago';
                        }
                        ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                                <i class="<?php echo $activity['icon']; ?>"></i>
                        </div>
                        <div class="activity-details">
                                <h3><?php echo $activity['title']; ?></h3>
                                <p><?php echo $activity['details']; ?></p>
                        </div>
                            <div class="activity-time"><?php echo $time_str; ?></div>
                    </div>
                        <?php
                        $count++;
                    }
                    
                    if (count($activities) == 0) {
                        echo '<div class="no-activity">No recent activity found</div>';
                    }
                    ?>
                </div>
                
            </div>
            
            <!-- Website Settings Tab -->
            <div class="admin-tab <?php echo $active_tab === 'website_settings' ? 'active' : ''; ?>" id="website_settings">
                <div class="admin-header">
                    <h1>Website Settings</h1>
                    <div class="admin-actions">
                        <a href="#" class="btn btn-outline btn-icon"><i class="fas fa-sync-alt"></i> <span>Reset to Default</span></a>
                    </div>
                </div>
                
                <div class="settings-panel" style="max-width: 900px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; border: 1px solid #f0f0f0;">
                    <div style="margin-bottom: 30px; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px;">
                        <h2 style="font-size: 1.5rem; color: #1e293b; font-weight: 800; margin-bottom: 5px;">General Settings</h2>
                        <p style="color: #64748b; font-size: 0.9rem;">Configure your website's primary identity and branding assets.</p>
                    </div>
                    
                    <form class="settings-form" id="general-settings-form" method="post" action="save_settings.php" enctype="multipart/form-data">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
                            <div class="form-group">
                                <label for="website_name" style="font-weight: 700; color: #334155; margin-bottom: 10px; display: block;">Website Name</label>
                                <input type="text" id="website_name" name="website_name" value="<?php echo htmlspecialchars($website_name); ?>" placeholder="Your website name" class="form-control" style="height: 50px; border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 0 20px; font-weight: 500;">
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 700; color: #334155; margin-bottom: 10px; display: block;">Header Display Style</label>
                                <div style="display: flex; gap: 20px; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1.5px solid #e2e8f0;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="radio" name="header_style" value="logo" <?php echo $header_style === 'logo' ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                                        <span style="font-weight: 600; color: #475569;">Show Logo Image</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="radio" name="header_style" value="text" <?php echo $header_style === 'text' ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                                        <span style="font-weight: 600; color: #475569;">Show Website Name (Text)</span>
                                    </label>
                                </div>
                                <p style="font-size: 11px; color: #64748b; margin-top: 8px;"><i class="fas fa-info-circle"></i> Choose what appears in the top navigation bar.</p>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                                <div class="form-group">
                                    <label style="font-weight: 700; color: #334155; margin-bottom: 10px; display: block;">Website Logo</label>
                                    <div style="background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 25px; text-align: center;">
                                        <?php if (!empty($website_logo)): ?>
                                        <div style="margin-bottom: 15px;">
                                            <img src="../images/<?php echo htmlspecialchars($website_logo); ?>" alt="Current Logo" style="max-height: 40px; max-width: 100%; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" id="website_logo" name="website_logo" style="width: 100%; font-size: 0.8rem; color: #64748b;">
                                        <p style="font-size: 11px; color: #94a3b8; margin-top: 10px;">PNG or SVG recommended (Max 2MB)</p>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label style="font-weight: 700; color: #334155; margin-bottom: 10px; display: block;">Favicon</label>
                                    <div style="background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 25px; text-align: center;">
                                        <?php if (!empty($favicon)): ?>
                                        <div style="margin-bottom: 15px;">
                                            <img src="../images/<?php echo htmlspecialchars($favicon); ?>" alt="Current Favicon" style="height: 32px; width: 32px; border-radius: 4px;">
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" id="favicon" name="favicon" style="width: 100%; font-size: 0.8rem; color: #64748b;">
                                        <p style="font-size: 11px; color: #94a3b8; margin-top: 10px;">ICO or 32x32 PNG (Max 1MB)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 40px; border-top: 1px solid #f0f0f0; pt: 30px; display: flex; align-items: center; justify-content: space-between;">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 35px; border-radius: 12px; font-weight: 700; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);">Save All Changes</button>
                            <div class="settings-response" id="general-settings-response" style="font-weight: 500; font-size: 0.9rem;"></div>
                        </div>
                    </form>
                </div>
            </div>
            
             <!-- My Profile Tab -->
             <div class="admin-tab <?php echo $active_tab === 'my_profile' ? 'active' : ''; ?>" id="my_profile">
                <div class="admin-header">
                    <h1>My Profile</h1>
                </div>
                
                <div class="profile-container" style="max-width: 900px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #f0f0f0;">
                    <div class="profile-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 50px 40px; display: flex; align-items: center; gap: 30px; position: relative;">
                        <div class="profile-avatar-wrapper" style="position: relative;">
                            <?php 
                            $profile_image = !empty($admin['profile_image']) ? $admin['profile_image'] : 'admin-avatar.jpg';
                            ?>
                            <div style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.2); overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                <img src="../images/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" id="profile-image-preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <label for="profile_image" style="position: absolute; bottom: 0; right: 0; background: #3b82f6; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid #1e293b; transition: all 0.3s ease;">
                                <i class="fas fa-camera" style="font-size: 0.9rem;"></i>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                            </label>
                        </div>
                        <div class="profile-info">
                            <h2 style="color: white; font-size: 1.8rem; font-weight: 800; margin: 0;"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></h2>
                            <p style="color: rgba(255,255,255,0.7); margin: 5px 0 0; font-size: 1rem;"><i class="fas fa-user-shield" style="margin-right: 8px;"></i> Administrator</p>
                        </div>
                    </div>
                    
                    <div class="profile-body" style="padding: 40px;">
                        <form id="update-profile-form" method="post" enctype="multipart/form-data">
                            <div style="margin-bottom: 40px;">
                                <h3 style="font-size: 1.1rem; color: #1e293b; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center;">
                                    <i class="fas fa-id-card" style="margin-right: 10px; color: #3b82f6;"></i> Personal Information
                                </h3>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                                    <div class="form-group">
                                        <label for="first_name" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">First Name</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($admin['first_name']); ?>" required class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($admin['last_name']); ?>" required class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                    </div>
                                </div>
                                <div style="margin-top: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                                    <div class="form-group">
                                        <label for="email" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Email Address</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Phone Number</label>
                                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 40px; padding-top: 30px; border-top: 1px solid #f1f5f9;">
                                <h3 style="font-size: 1.1rem; color: #1e293b; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center;">
                                    <i class="fas fa-lock" style="margin-right: 10px; color: #f59e0b;"></i> Security Settings
                                </h3>
                                <div class="form-group" style="margin-bottom: 25px;">
                                    <label for="current_password" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" placeholder="Leave blank if not changing" class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                                    <div class="form-group">
                                        <label for="new_profile_password" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">New Password</label>
                                        <input type="password" id="new_profile_password" name="new_password" class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_profile_password" style="font-weight: 600; color: #64748b; margin-bottom: 8px; display: block;">Confirm New Password</label>
                                        <input type="password" id="confirm_profile_password" name="confirm_password" class="form-control" style="height: 48px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0 15px; font-weight: 500;">
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 20px;">
                                <div id="profile-update-message" style="font-weight: 600; font-size: 0.9rem;"></div>
                                <button type="submit" class="btn btn-primary" style="padding: 14px 40px; border-radius: 12px; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2); transition: all 0.3s ease;">Save Profile Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
     
    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal" id="addUserForm">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button class="modal-close" id="closeAddUserModal">&times;</button>
            </div>
            
            <form class="settings-form" id="add-user-form">
                <div class="form-group">
                    <label for="new_first_name">First Name</label>
                    <input type="text" id="new_first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="new_last_name">Last Name</label>
                    <input type="text" id="new_last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="new_email">Email Address</label>
                    <input type="email" id="new_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="new_company">Company</label>
                    <input type="text" id="new_company" name="company_name" required>
                </div>
                
                <div class="form-group">
                    <label for="new_role">Role</label>
                    <select id="new_role" name="role" required>
                        <option value="admin">Super Admin</option>
                        <option value="product_manager">Product Manager</option>
                        <option value="order_manager">Order Manager</option>
                        <option value="user" selected>Regular User</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_user_password">Password</label>
                    <input type="password" id="new_user_password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_user_confirm_password">Confirm Password</label>
                    <input type="password" id="new_user_confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editUserModal">
        <div class="modal" id="editUserForm">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button class="modal-close" id="closeEditUserModal">&times;</button>
            </div>
            
            <form class="settings-form" id="edit-user-form" onsubmit="updateUser(event)">
                <input type="hidden" id="edit_user_id">
                
                <div class="form-group">
                    <label for="edit_first_name">First Name</label>
                    <input type="text" id="edit_first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_last_name">Last Name</label>
                    <input type="text" id="edit_last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_company">Company</label>
                    <input type="text" id="edit_company" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" required>
                        <option value="admin">Super Admin</option>
                        <option value="product_manager">Product Manager</option>
                        <option value="order_manager">Order Manager</option>
                        <option value="user">Regular User</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_user_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_user_password">
                </div>
                
                <div class="form-group">
                    <label for="edit_user_confirm_password">Confirm New Password</label>
                    <input type="password" id="edit_user_confirm_password">
                </div>
                
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Notification function moved to global scope -->
    <script>
    // Modal functions
    function showAddUserModal() {
        console.log('showAddUserModal function called');
        $('#addUserModal').css('display', 'block');
        $('#addUserForm').css('display', 'block');
    }
    
    function hideAddUserModal() {
        console.log('hideAddUserModal function called');
        $('#addUserModal').css('display', 'none');
        $('#addUserForm').css('display', 'none');
    }
    
    function showEditUserModal() {
        console.log('showEditUserModal function called');
        $('#editUserModal').css('display', 'block');
        $('#editUserForm').css('display', 'block');
    }
    
    function hideEditUserModal() {
        console.log('hideEditUserModal function called');
        $('#editUserModal').css('display', 'none');
        $('#editUserForm').css('display', 'none');
    }
    
    // Edit user function
    function editUser(userId) {
        console.log('Debug: editUser called with ID:', userId);
        
        // Show loading notification
        showNotification('Loading user data...', 'info');
        
        // Fetch user data
        $.ajax({
            url: 'get_user.php',
            type: 'GET',
            data: { id: userId },
            dataType: 'json',
            success: function(response) {
                try {
                    if (response.success && response.user) {
                        // Populate form with user data
                        $('#edit_user_id').val(response.user.id);
                        $('#edit_first_name').val(response.user.first_name);
                        $('#edit_last_name').val(response.user.last_name);
                        $('#edit_email').val(response.user.email);
                        $('#edit_company').val(response.user.company_name);
                        $('#edit_role').val(response.user.role);
                        $('#edit_status').val(response.user.is_verified);
                    
                        // Clear password fields
                        $('#edit_user_password').val('');
                        $('#edit_user_confirm_password').val('');
                    
                        // Show edit user modal
                        showEditUserModal();
                        
                        // Ensure close button has event listener
                        $("#closeEditUserModal").off('click').on('click', function() {
                            hideEditUserModal();
                        });
                } else {
                        showNotification(response.message || 'Error loading user data', 'error');
                    }
                } catch (e) {
                    console.error('Error processing response:', e);
                    showNotification('Error loading user data', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                showNotification('Error loading user data: ' + error, 'error');
            }
        });
    }
    
    // Update user function
    function updateUser(event) {
        event.preventDefault();
        
        // Validation
        const password = document.getElementById('edit_user_password').value;
        const confirmPassword = document.getElementById('edit_user_confirm_password').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('id', document.getElementById('edit_user_id').value);
        formData.append('first_name', document.getElementById('edit_first_name').value);
        formData.append('last_name', document.getElementById('edit_last_name').value);
        formData.append('email', document.getElementById('edit_email').value);
        formData.append('role', document.getElementById('edit_role').value);
        formData.append('is_verified', document.getElementById('edit_status').value);
        
        if (password) {
            formData.append('password', password);
        }
        
        // Show that we're processing
        showNotification('Updating user...', 'info');
        
        // Submit the form
        fetch('update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal first
                hideEditUserModal();
                
                // Then show success message
                showNotification('User updated successfully!', 'success');
                
                // Reload the page to show updated data
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating the user', 'error');
        });
    }
    
    // Delete user function
    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            // Create form data
            const formData = new FormData();
            formData.append('id', userId);
            
            // Submit the request
            fetch('delete_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully');
                    
                    // Remove the row from the table
                    document.querySelector(`tr[data-user-id="${userId}"]`).remove();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the user');
            });
        }
    }
    
    // Notification function
    function showNotification(message, type = 'success') {
        console.log('Notification:', message, type);
        
        // Remove existing notifications
        $('.notification').remove();
        
        // Create notification element
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        // Show notification
        setTimeout(function() {
            notification.addClass('active');
        }, 10);
        
        // Hide after 3 seconds
        setTimeout(function() {
            notification.removeClass('active');
            
            // Remove after fade out
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Format currency for JavaScript
    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Sidebar Toggle Functionality
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        
        if (sidebarToggle && sidebar) {
            // Toggle sidebar when button is clicked
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            });
            
            // Close sidebar when clicking on overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                });
            }
            
            // Close sidebar when pressing escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
                
                // Also close modals with Escape key
                if (e.key === 'Escape') {
                    hideAddUserModal();
                    hideEditUserModal();
                }
            });
        }
        
        // Ensure modal close buttons work
        $('#closeAddUserModal').off('click').on('click', function() {
            hideAddUserModal();
        });
        
        $('#closeEditUserModal').off('click').on('click', function() {
            hideEditUserModal();
        });
        
        // Improve clicking outside modals to close
        $(document).on('click', function(e) {
            // Close Add User modal when clicking outside
            if ($(e.target).is('#addUserModal')) {
                hideAddUserModal();
            }
            
            // Close Edit User modal when clicking outside
            if ($(e.target).is('#editUserModal')) {
                hideEditUserModal();
            }
        });
        
        // Setup form submission with proper modal closing
        $('#edit-user-form').on('submit', function(e) {
            e.preventDefault();
            updateUser(e);
        });
        
        // Toggle order details
        const toggleButtons = document.querySelectorAll('.toggle-icon');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderRow = this.closest('.order-row');
                const detailsRow = orderRow.nextElementSibling;
                
                // Toggle details row
                detailsRow.style.display = detailsRow.style.display === 'table-row' ? 'none' : 'table-row';
                
                // Toggle icon
                this.classList.toggle('active');
            });
        });
        
        // Expand all orders button
        const expandAllButton = document.getElementById('expandAllOrders');
        if (expandAllButton) {
            expandAllButton.addEventListener('click', function() {
                const detailsRows = document.querySelectorAll('.order-details-row');
                const toggleIcons = document.querySelectorAll('.toggle-icon');
                const isExpanded = this.getAttribute('data-expanded') === 'true';
                
                detailsRows.forEach(row => {
                    row.style.display = isExpanded ? 'none' : 'table-row';
                });
                
                toggleIcons.forEach(icon => {
                    if (isExpanded) {
                        icon.classList.remove('active');
                } else {
                        icon.classList.add('active');
                    }
                });
                
                this.setAttribute('data-expanded', isExpanded ? 'false' : 'true');
                this.innerHTML = isExpanded ? 
                    '<i class="fas fa-expand-alt"></i> Expand All' : 
                    '<i class="fas fa-compress-alt"></i> Collapse All';
            });
        }
        
        // Cancel order buttons
        const cancelButtons = document.querySelectorAll('.cancel-order');
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                if (confirm('Are you sure you want to cancel this order?')) {
                    // Here you would add AJAX code to cancel the order
                    // For now, just logging to console
                    console.log('Cancelling order #' + orderId);
                    
                    // Example of how you would implement this with AJAX:
                    /*
                    fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                        body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                            // Update the UI to reflect the canceled order
                            const orderRow = this.closest('.order-details').parentElement.parentElement.previousElementSibling;
                            const statusCell = orderRow.querySelector('td:nth-child(7)');
                            statusCell.innerHTML = '<span class="status-badge status-canceled">Canceled</span>';
                            
                            // Remove the cancel button
                            this.remove();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                        alert('An error occurred while canceling the order.');
                    });
                    */
                }
            });
        });
        
        // Handle order status updates
        $('.update-status').on('click', function() {
            const orderId = $(this).data('id');
            const newStatus = $(this).data('status');
            const button = $(this);
            const orderAmount = parseFloat(button.data('amount'));
            
            // Show loading state
            button.html('<i class="fas fa-spinner fa-spin"></i>');
            button.prop('disabled', true);
            
            // Send AJAX request to update order status
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: {
                    order_id: orderId,
                    status: newStatus,
                    amount: orderAmount
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Remove the row with a fade-out effect
                            button.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if this was the last row
                                if ($('.orders-table tbody tr').length === 0) {
                                    $('.orders-table tbody').html('<tr><td colspan="6" class="no-data">No pending orders found</td></tr>');
                                }
                                
                                // Update pending orders count
                                const pendingCountEl = $('.stat-card.pending-orders .stat-info h3');
                                let currentCount = parseInt(pendingCountEl.text());
                                pendingCountEl.text(--currentCount);
                                
                                // Update pending amount (decrease)
                                const pendingAmountEl = $('.stat-card.pending-amount .stat-info h3');
                                let currentPendingAmount = parseFloat(pendingAmountEl.text().replace('$', '').replace(/,/g, ''));
                                let newPendingAmount = currentPendingAmount - orderAmount;
                                if (newPendingAmount < 0) newPendingAmount = 0;
                                pendingAmountEl.text(formatCurrency(newPendingAmount));
                                
                                // Update total spent (increase) if order is processed or completed
                                if (newStatus === 'processing' || newStatus === 'completed' || newStatus === 'delivered') {
                                    const totalSpentEl = $('.stat-card:contains("Total") .stat-info h3');
                                    if (totalSpentEl.length) {
                                        let currentTotalSpent = parseFloat(totalSpentEl.text().replace('$', '').replace(/,/g, ''));
                                        totalSpentEl.text(formatCurrency(currentTotalSpent + orderAmount));
                                    }
                                }
                                
                                // Show notification
                                showNotification('Order status updated successfully!', 'success');
                            });
                        } else {
                            showNotification(data.message || 'Error updating order status', 'error');
                            
                            // Reset button
                            button.html('Process');
                            button.prop('disabled', false);
                        }
                    } catch (e) {
                        showNotification('Invalid server response', 'error');
                        console.error(e);
                        
                        // Reset button
                        button.html('Process');
                        button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Server error: ' + error, 'error');
                    console.error(xhr.responseText);
                    
                    // Reset button
                    button.html('Process');
                    button.prop('disabled', false);
                }
            });
        });
    });
    </script>

    <style>
    .order-details-row {
        display: none;
    }

    .order-details {
        padding: 20px;
        background-color: #f9fafb;
        border-radius: 8px;
    }

    .order-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .order-details-section h4,
    .order-items-section h4,
    .order-notes h4 {
        font-size: 16px;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .order-details-section p {
        margin-bottom: 8px;
        font-size: 14px;
    }

    .order-items-section {
        margin-bottom: 20px;
    }

    .order-items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-items-table th,
    .order-items-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .product-cell {
        display: flex;
        align-items: center;
    }

    .product-cell img,
    .product-no-image {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        margin-right: 10px;
        object-fit: cover;
    }

    .product-no-image {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f5f5f5;
        color: #ccc;
        font-size: 18px;
    }

    .order-notes {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 4px;
        background-color: #fff;
        border: 1px solid #e5e7eb;
    }

    .order-actions {
        display: flex;
        gap: 10px;
    }

    .toggle-details {
        width: 40px;
        text-align: center;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
    }

    .toggle-icon.active {
        transform: rotate(180deg);
    }

    @media (max-width: 992px) {
        .order-details-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    .order-number-link {
        color: #1a56db;
        text-decoration: none;
        font-weight: 500;
    }

    .order-number-link:hover {
        text-decoration: underline;
    }
    
    /* Notification styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-10px);
        opacity: 0;
        transition: transform 0.3s, opacity 0.3s;
        max-width: 300px; /* Limit width */
        white-space: nowrap; /* Keep on single line */
        overflow: hidden; /* Hide overflow */
        text-overflow: ellipsis; /* Add ellipsis for overflowing text */
    }
    
    .notification.active {
        transform: translateY(0);
        opacity: 1;
    }
    
    .notification.success {
        background-color: #4caf50;
    }
    
    .notification.error {
        background-color: #f44336;
    }
    
    .notification.info {
        background-color: #2196f3;
    }
    
    .notification.warning {
        background-color: #ff9800;
    }
    </style>
    
    <!-- Debug script for user management -->
    <script>
    // Define showNotification function if it doesn't exist
    if (typeof showNotification !== 'function') {
        window.showNotification = function(message, type = 'success') {
            // Remove existing notifications
            $('.notification').remove();
            
            // Create notification element
            const notification = $('<div class="notification ' + type + '">' + message + '</div>');
            $('body').append(notification);
            
            // Show notification
            setTimeout(function() {
                notification.addClass('active');
            }, 10);
            
            // Hide after 3 seconds
            setTimeout(function() {
                notification.removeClass('active');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
            
            console.log('Notification:', type, message);
        };
    }
    
    $(document).ready(function() {
        console.log('Debug: Document ready for user management');
        
        // Handle modal close button
        $('#closeEditUserModal').click(function() {
            $('#editUserModal').removeClass('active');
            $('#editUserForm').removeClass('active');
        });
        
        // Direct fix for edit user functionality
        $('.edit-user-btn').click(function(e) {
            e.preventDefault();
            var userId = $(this).data('id');
            console.log('Edit user clicked for ID:', userId);
            
            if (userId) {
                // Show loading
                showNotification('Loading user data...', 'info');
                
                // Fetch user data
                $.ajax({
                    url: 'get_user.php',
                    type: 'GET',
                    data: { id: userId },
                    success: function(response) {
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success && data.user) {
                                // Populate form with user data
                                $('#edit_user_id').val(data.user.id);
                                $('#edit_first_name').val(data.user.first_name);
                                $('#edit_last_name').val(data.user.last_name);
                                $('#edit_email').val(data.user.email);
                                $('#edit_company').val(data.user.company_name);
                                $('#edit_role').val(data.user.role);
                                $('#edit_status').val(data.user.is_verified);
                                
                                // Clear password fields
                                $('#edit_user_password').val('');
                                $('#edit_user_confirm_password').val('');
                                
                                // Show edit modal by adding active class
                                $('#editUserModal').css('display', 'block');
                                $('#editUserForm').css('display', 'block');
                            } else {
                                showNotification(data.message || 'Error loading user data', 'error');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            showNotification('Error loading user data', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        showNotification('Error loading user data: ' + error, 'error');
                    }
                });
            }
        });
        
        // Handle form submission for updating user
        $('#edit-user-form').submit(function(e) {
            e.preventDefault();
            console.log('Submit form for updating user');
            
            // Get form data
            var userId = $('#edit_user_id').val();
            var firstName = $('#edit_first_name').val();
            var lastName = $('#edit_last_name').val();
            var email = $('#edit_email').val();
            var company = $('#edit_company').val();
            var role = $('#edit_role').val();
            var status = $('#edit_status').val();
            var password = $('#edit_user_password').val();
            var confirmPassword = $('#edit_user_confirm_password').val();
            
            // Validate passwords match if provided
            if (password && password !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            
            // Show loading
            showNotification('Updating user...', 'info');
            
            // Send AJAX request
            $.ajax({
                url: 'update_user.php',
                type: 'POST',
                data: {
                    id: userId,
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    company_name: company,
                    role: role,
                    is_verified: status,
                    password: password
                },
                success: function(response) {
                    try {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.success) {
                            showNotification('User updated successfully', 'success');
                            
                            // Close modal
                            $('#editUserModal').css('display', 'none');
                            $('#editUserForm').css('display', 'none');
                            
                            // Reload page to see changes
                            location.reload();
                        } else {
                            showNotification(data.message || 'Error updating user', 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showNotification('Error updating user', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    showNotification('Error updating user: ' + error, 'error');
                }
            });
        });
        
        // Debug click handlers
        $('a[onclick^="editUser"]').on('click', function(e) {
            console.log('Debug: Edit user clicked');
            // The onclick attribute will still run
        });
        
        // Alternative approach in case onclick isn't working
        $('a[title="Edit"]').on('click', function(e) {
            console.log('Debug: Edit user clicked (alternative)');
            
            // Get user ID from the parent row
            const userId = $(this).closest('tr').data('user-id');
            if (userId) {
                editUser(userId);
            }
        });
        
        $('a[title="Delete"]').on('click', function(e) {
            console.log('Debug: Delete user clicked (alternative)');
            
            // Get user ID from the parent row
            const userId = $(this).closest('tr').data('user-id');
            if (userId) {
                deleteUser(userId);
            }
        });
        
        // Override editUser and deleteUser functions with debugging
        
            console.log('Debug: editUser called with ID:', userId);
            
            // Show loading notification
            showNotification('Loading user data...', 'info');
            
            // Fetch user data
                $.ajax({
                    url: 'get_user.php',
                    type: 'GET',
                    data: { id: userId },
                dataType: 'json',
                    success: function(response) {
                        try {
                        // Make sure we're working with a JavaScript object
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success && data.user) {
                            // Populate form with user data
                                $('#edit_user_id').val(data.user.id);
                                $('#edit_first_name').val(data.user.first_name);
                                $('#edit_last_name').val(data.user.last_name);
                                $('#edit_email').val(data.user.email);
                                $('#edit_company').val(data.user.company_name);
                                $('#edit_role').val(data.user.role);
                                $('#edit_status').val(data.user.is_verified);
                            
                            // Clear password fields
                            $('#edit_user_password').val('');
                            $('#edit_user_confirm_password').val('');
                            
                            // Show the edit user modal
                            $('#editUserModal').css('display', 'block');
                            $('#editUserForm').css('display', 'block');
                            } else {
                            showNotification(data.message || 'Error loading user data', 'error');
                            }
                        } catch (e) {
                        console.error('Error parsing response:', e);
                        showNotification('Error loading user data', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    showNotification('Error loading user data: ' + error, 'error');
                    }
                });
        };
        
            console.log('Debug: deleteUser called with ID:', userId);
            
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                // Show loading notification
                if (typeof showNotification === 'function') {
                    showNotification('Deleting user...', 'info');
                }
                
                // Send AJAX request to delete user
                $.ajax({
                    url: 'delete_user.php',
                    type: 'POST',
                    data: { id: userId },
                    success: function(response) {
                        console.log('Debug: Delete user response received');
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success) {
                                if (typeof showNotification === 'function') {
                                    showNotification('User deleted successfully!', 'success');
                                }
                                
                                // Remove user from the table
                                $(`tr[data-user-id="${userId}"]`).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                console.error('Debug: Error deleting user:', data.message);
                                if (typeof showNotification === 'function') {
                                    showNotification(data.message || 'Error deleting user', 'error');
                                }
                            }
                        } catch (e) {
                            console.error('Debug: Error parsing response:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Debug: AJAX error:', error);
                    }
                });
            }
        };
    });
    </script>
    
    <!-- User Management Script -->
    <script>
    $(document).ready(function() {
        console.log('User management script loaded');
        
        // ===== Add User Functionality =====
        
        // Show Add User Modal
        $('#addUserBtn, button[onclick="showAddUserModal()"]').click(function(e) {
            e.preventDefault();
            console.log('Add User button clicked!');
            $('#addUserModal').css('display', 'block');
            $('#addUserForm').css('display', 'block');
        });
        
        // Close Add User Modal
        $('#closeAddUserModal').click(function() {
            console.log('Close Add User button clicked!');
            $('#addUserModal').css('display', 'none');
            $('#addUserForm').css('display', 'none');
        });
        
        // Direct button click function in case event binding fails
        window.showAddUserModal = function() {
            console.log('showAddUserModal function called directly');
            $('#addUserModal').css('display', 'block');
            $('#addUserForm').css('display', 'block');
        };
        
        window.hideAddUserModal = function() {
            console.log('hideAddUserModal function called directly');
            $('#addUserModal').css('display', 'none');
            $('#addUserForm').css('display', 'none');
        };
        
        // Also bind directly to the button
        $(document).on('click', '#addUserBtn', function(e) {
            e.preventDefault();
            console.log('Add User button clicked via document delegation!');
            $('#addUserModal').css('display', 'block');
            $('#addUserForm').css('display', 'block');
        });
        
        // Add User Form Submission
        $('#add-user-form').submit(function(e) {
            e.preventDefault();
            
            // Validate passwords match
            var password = $('#new_user_password').val();
            var confirmPassword = $('#new_user_confirm_password').val();
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            // Show loading message
            var loadingNotification = $('<div class="notification info active">Adding...</div>');
            $('body').append(loadingNotification);
            
            // Get form data
            var formData = $(this).serialize();
            
            // Submit form via AJAX
            $.ajax({
                url: 'add_user.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    loadingNotification.remove();
                    
                    if (response.success) {
                        // Show success message
                        var successNotification = $('<div class="notification success active">User added!</div>');
                        $('body').append(successNotification);
                        
                        // Hide notification after 3 seconds
                        setTimeout(function() {
                            successNotification.removeClass('active');
                            setTimeout(function() {
                                successNotification.remove();
                            }, 300);
                        }, 3000);
                        
                        // Close modal
                        $('#addUserModal').css('display', 'none');
                        $('#addUserForm').css('display', 'none');
                        
                        // Reset form
                        $('#add-user-form')[0].reset();
                        
                        // Add new user to table
                        var user = response.user;
                        var newUserRow = `
                            <tr data-user-id="${user.id}">
                                <td>${user.id}</td>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.company || 'N/A'}</td>
                                <td>
                                    <span class="user-role role-${user.role}">${getRoleDisplay(user.role)}</span>
                                </td>
                                <td>${user.status}</td>
                                <td>${user.last_login || 'Never'}</td>
                                <td class="user-actions">
                                    <a href="#" title="Edit" class="edit-user-btn" data-id="${user.id}"><i class="fas fa-edit"></i></a>
                                    <a href="#" title="View Orders"><i class="fas fa-shopping-cart"></i></a>
                                    <a href="#" title="Delete" class="delete-user-btn" data-id="${user.id}"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        `;
                        
                        // Add to table
                        var tbody = $('.users-table tbody');
                        var noDataRow = tbody.find('tr td[colspan="8"]').parent();
                        
                        if (noDataRow.length) {
                            // If there's a "no data" row, replace it
                            noDataRow.replaceWith(newUserRow);
                        } else {
                            // Otherwise add to top
                            tbody.prepend(newUserRow);
                        }
                        
                        // Attach event handlers to the new row
                        attachUserActionHandlers();
                    } else {
                        // Show error message
                        var errorNotification = $('<div class="notification error active">' + (response.message || 'Error adding user') + '</div>');
                        $('body').append(errorNotification);
                        
                        // Hide notification after 3 seconds
                        setTimeout(function() {
                            errorNotification.removeClass('active');
                            setTimeout(function() {
                                errorNotification.remove();
                            }, 300);
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    loadingNotification.remove();
                    
                    // Parse error message
                    var errorMessage = 'Error adding user';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    // Show error message
                    var errorNotification = $('<div class="notification error active">' + errorMessage + '</div>');
                    $('body').append(errorNotification);
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        errorNotification.removeClass('active');
                        setTimeout(function() {
                            errorNotification.remove();
                        }, 300);
                    }, 3000);
                }
            });
        });
        
        // ===== Edit User Functionality =====
        
        // Close Edit User Modal
        $('#closeEditUserModal').click(function() {
            $('#editUserModal').removeClass('active');
            $('#editUserForm').removeClass('active');
        });
        
        // Edit User Form Submission
        $('#edit-user-form').submit(function(e) {
            e.preventDefault();
            
            // Validate passwords match if provided
            var password = $('#edit_user_password').val();
            var confirmPassword = $('#edit_user_confirm_password').val();
            
            if (password && password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            // Show loading message
            var loadingNotification = $('<div class="notification info active">Updating...</div>');
            $('body').append(loadingNotification);
            
            // Get form data
            var userId = $('#edit_user_id').val();
            var formData = {
                id: userId,
                first_name: $('#edit_first_name').val(),
                last_name: $('#edit_last_name').val(),
                email: $('#edit_email').val(),
                company_name: $('#edit_company').val(),
                role: $('#edit_role').val(),
                is_verified: $('#edit_status').val(),
                password: password
            };
            
            // Submit form via AJAX
            $.ajax({
                url: 'update_user.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    loadingNotification.remove();
                    
                    if (response.success) {
                        // Show success message
                        var successNotification = $('<div class="notification success active">User updated!</div>');
                        $('body').append(successNotification);
                        
                        // Hide notification after 3 seconds
                        setTimeout(function() {
                            successNotification.removeClass('active');
                            setTimeout(function() {
                                successNotification.remove();
                            }, 300);
                        }, 3000);
                        
                        // Close modal
                        $('#editUserModal').css('display', 'none');
                        $('#editUserForm').css('display', 'none');
                        
                        // Update user in the table
                        var userRow = $('tr[data-user-id="' + userId + '"]');
                        if (userRow.length) {
                            var fullName = formData.first_name + ' ' + formData.last_name;
                            userRow.find('td:nth-child(2)').text(fullName);
                            userRow.find('td:nth-child(3)').text(formData.email);
                            userRow.find('td:nth-child(4)').text(formData.company_name || 'N/A');
                            
                            // Update role display
                            var roleDisplay = getRoleDisplay(formData.role);
                            var roleClass = 'role-' + formData.role;
                            
                            userRow.find('td:nth-child(5) .user-role')
                                .text(roleDisplay)
                                .removeClass('role-admin role-product-manager role-order-manager role-user')
                                .addClass(roleClass);
                            
                            // Update status
                            userRow.find('td:nth-child(6)').text(formData.is_verified == 1 ? 'Active' : 'Inactive');
                        }
                    } else {
                        // Show error message
                        var errorNotification = $('<div class="notification error active">' + (response.message || 'Error updating user') + '</div>');
                        $('body').append(errorNotification);
                        
                        // Hide notification after 3 seconds
                        setTimeout(function() {
                            errorNotification.removeClass('active');
                            setTimeout(function() {
                                errorNotification.remove();
                            }, 300);
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    loadingNotification.remove();
                    
                    // Parse error message
                    var errorMessage = 'Error updating user';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    // Show error message
                    var errorNotification = $('<div class="notification error active">' + errorMessage + '</div>');
                    $('body').append(errorNotification);
                    
                    // Hide notification after 3 seconds
                    setTimeout(function() {
                        errorNotification.removeClass('active');
                        setTimeout(function() {
                            errorNotification.remove();
                        }, 300);
                    }, 3000);
                }
            });
        });
        
        // ===== Delete User Functionality =====
        
        // Helper function to show notification
        function showNotification(message, type) {
            // Remove existing notifications
            $('.notification').remove();
            
            // Create notification element with shorter message if needed
            if (message.length > 40) {
                if (type === 'success') {
                    message = 'Success!';
                } else if (type === 'error') {
                    message = 'Error! See console for details.';
                    console.error(message);
                } else if (type === 'info') {
                    message = 'Loading...';
                }
            }
            
            var notification = $('<div class="notification ' + type + ' active">' + message + '</div>');
            $('body').append(notification);
            
            // Hide after 3 seconds
            setTimeout(function() {
                notification.removeClass('active');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        }
        
        // Helper function to get role display text
        function getRoleDisplay(role) {
            switch(role) {
                case 'admin': return 'Super Admin';
                case 'product_manager': return 'Product Manager';
                case 'order_manager': return 'Order Manager';
                default: return 'Regular User';
            }
        }
        
        // Attach event handlers to user action buttons
        function attachUserActionHandlers() {
            // Edit User Button Click
            $('.edit-user-btn').off('click').on('click', function(e) {
                e.preventDefault();
                var userId = $(this).data('id');
                console.log('Edit user clicked for ID:', userId);
                
                if (userId) {
                    // Show loading
                    showNotification('Loading user data...', 'info');
                    
                    // Fetch user data
                    $.ajax({
                        url: 'get_user.php',
                        type: 'GET',
                        data: { id: userId },
                        success: function(response) {
                            try {
                                if (response.success && response.user) {
                                    // Populate form with user data
                                    $('#edit_user_id').val(response.user.id);
                                    $('#edit_first_name').val(response.user.first_name);
                                    $('#edit_last_name').val(response.user.last_name);
                                    $('#edit_email').val(response.user.email);
                                    $('#edit_company').val(response.user.company_name);
                                    $('#edit_role').val(response.user.role);
                                    $('#edit_status').val(response.user.is_verified);
                                    
                                    // Clear password fields
                                    $('#edit_user_password').val('');
                                    $('#edit_user_confirm_password').val('');
                                    
                                    // Show edit modal by adding active class
                                    $('#editUserModal').css('display', 'block');
                                    $('#editUserForm').css('display', 'block');
                                } else {
                                    showNotification(response.message || 'Error loading user data', 'error');
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                showNotification('Error loading user data', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', error);
                            showNotification('Error loading user data: ' + error, 'error');
                        }
                    });
                }
            });
            
            // Delete User Button Click
            $('.delete-user-btn, a[title="Delete"]').off('click').on('click', function(e) {
                e.preventDefault();
                var userId = $(this).data('id');
                if (!userId) {
                    // Try to get ID from onclick attribute (for backward compatibility)
                    var onclick = $(this).attr('onclick');
                    if (onclick) {
                        var match = onclick.match(/deleteUser\((\d+)\)/);
                        if (match && match[1]) {
                            userId = match[1];
                        }
                    }
                }
                
                console.log('Delete user clicked for ID:', userId);
                
                if (userId && confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    // Show loading notification
                    showNotification('Deleting user...', 'info');
                    
                    // Send AJAX request to delete user
                    $.ajax({
                        url: 'delete_user.php',
                        type: 'POST',
                        data: { id: userId },
                        success: function(response) {
                            try {
                                if (response.success) {
                                    showNotification('User deleted successfully!', 'success');
                                    
                                    // Remove user from the table
                                    $('tr[data-user-id="' + userId + '"]').fadeOut(300, function() {
                                        $(this).remove();
                                        
                                        // Check if table is empty
                                        if ($('.users-table tbody tr').length === 0) {
                                            $('.users-table tbody').html('<tr><td colspan="8" style="text-align: center;">No users found</td></tr>');
                                        }
                                    });
                                } else {
                                    showNotification(response.message || 'Error deleting user', 'error');
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                showNotification('Error deleting user', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', error);
                            showNotification('Error deleting user: ' + error, 'error');
                        }
                    });
                }
            });
        }
        
        // Initialize event handlers
        attachUserActionHandlers();
    });
    </script>
    
    <!-- Profile Update Script -->
    <script>
    $(document).ready(function() {
        // Profile image preview with animation
        $('#profile_image').on('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                
                // Show loading state
                $('#profile-image-preview').css('opacity', '0.5');
                
                reader.onload = function(e) {
                    // Animate the image change
                    $('#profile-image-preview').fadeOut(300, function() {
                        $(this).attr('src', e.target.result);
                        $(this).fadeIn(300);
                        $(this).css('opacity', '1');
                    });
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Enhance button effects
        $('.avatar-upload label').on('mousedown', function() {
            $(this).css('transform', 'translateY(1px)');
        }).on('mouseup mouseleave', function() {
            $(this).css('transform', '');
        });
        
        // Trigger file input when button is clicked
        $('.avatar-upload label').on('click', function(e) {
            e.preventDefault();
            $('#profile_image').click();
        });
        
        // Add form field animation on focus
        $('.form-group input').on('focus', function() {
            $(this).closest('.form-group').find('label').css({
                'color': '#3f51b5',
                'transition': 'color 0.3s'
            });
        }).on('blur', function() {
            $(this).closest('.form-group').find('label').css('color', '');
        });
        
        // Submit profile update form with better feedback
        $('#update-profile-form').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading button state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            submitBtn.prop('disabled', true);
            
            // Password validation
            var currentPassword = $('#current_password').val();
            var newPassword = $('#new_profile_password').val();
            var confirmPassword = $('#confirm_profile_password').val();
            
            // If changing password, validate
            if (newPassword || confirmPassword) {
                if (!currentPassword) {
                    showProfileMessage('Current password is required to change password', 'error');
                    resetButton();
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showProfileMessage('New passwords do not match', 'error');
                    resetButton();
                    return;
                }
                
                if (newPassword.length < 8) {
                    showProfileMessage('Password must be at least 8 characters long', 'error');
                    resetButton();
                    return;
                }
            }
            
            // Create form data including the file
            var formData = new FormData();
            formData.append('first_name', $('#first_name').val());
            formData.append('last_name', $('#last_name').val());
            formData.append('email', $('#email').val());
            formData.append('phone', $('#phone').val());
            
            // Add password fields if changing password
            if (newPassword) {
                formData.append('current_password', currentPassword);
                formData.append('new_password', newPassword);
            }
            
            // Add profile image if selected
            if ($('#profile_image')[0].files.length > 0) {
                formData.append('profile_image', $('#profile_image')[0].files[0]);
            }
            
            // Show loading message
            showProfileMessage('Updating profile...', 'info');
            
            // Send AJAX request
            $.ajax({
                url: 'update_profile.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        // Success animation
                        showProfileMessage('<i class="fas fa-check-circle"></i> ' + response.message, 'success');
                        
                        // Clear password fields
                        $('#current_password').val('');
                        $('#new_profile_password').val('');
                        $('#confirm_profile_password').val('');
                        
                        // Update session name if available
                        if (response.user_name) {
                            $('.admin-profile h3').fadeOut(300, function() {
                                $(this).text(response.user_name).fadeIn(300);
                            });
                            
                            // Also update the profile header
                            $('.profile-info h2').fadeOut(300, function() {
                                $(this).text(response.user_name).fadeIn(300);
                            });
                        }
                        
                        // Update sidebar profile image if available
                        if (response.profile_image) {
                            console.log('Updating profile image to:', response.profile_image);
                            
                            // Create a new Image object to ensure the image is loaded before showing it
                            var newImg = new Image();
                            newImg.onload = function() {
                                // Image loaded successfully, now update the actual image elements
                                $('.admin-profile img').fadeOut(300, function() {
                                    $(this).attr('src', '../images/' + response.profile_image)
                                           .on('load', function() {
                                                $(this).fadeIn(300);
                                           })
                                           .on('error', function() {
                                                console.error('Failed to load image:', '../images/' + response.profile_image);
                                                // Fallback to default image if load fails
                                                $(this).attr('src', '../images/admin-avatar.jpg').fadeIn(300);
                                           });
                                });
                                
                                // Also ensure the profile tab image is updated with the same source
                                if ($('#profile-image-preview').attr('src') !== '../images/' + response.profile_image) {
                                    $('#profile-image-preview').attr('src', '../images/' + response.profile_image);
                                }
                            };
                            
                            newImg.onerror = function() {
                                console.error('Failed to preload image:', '../images/' + response.profile_image);
                                // Don't update if preload fails
                                showProfileMessage('<i class="fas fa-exclamation-circle"></i> Profile updated but image couldn\'t be loaded', 'warning');
                            };
                            
                            // Start loading the image
                            newImg.src = '../images/' + response.profile_image;
                        }
                        
                        // Highlight updated fields with subtle animation
                        $('.form-group input').not('#current_password, #new_profile_password, #confirm_profile_password')
                            .css('background-color', 'rgba(209, 250, 229, 0.3)')
                            .delay(1000)
                            .animate({backgroundColor: 'white'}, 1000);
                    } else {
                        showProfileMessage('<i class="fas fa-exclamation-circle"></i> ' + response.message, 'error');
                    }
                    resetButton();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    try {
                        var response = JSON.parse(xhr.responseText);
                        showProfileMessage('<i class="fas fa-exclamation-triangle"></i> ' + (response.message || 'An error occurred'), 'error');
                    } catch (e) {
                        showProfileMessage('<i class="fas fa-exclamation-triangle"></i> An error occurred while updating profile', 'error');
                    }
                    resetButton();
                }
            });
            
            // Helper function to reset button state
            function resetButton() {
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
        
        // Function to show profile update messages with animation
        function showProfileMessage(message, type) {
            var messageElement = $('#profile-update-message');
            messageElement.removeClass('success error info');
            messageElement.addClass(type);
            messageElement.html(message);
            
            // Slide down animation
            if (messageElement.is(':hidden')) {
                messageElement.slideDown(300);
            } else {
                messageElement.fadeOut(200, function() {
                    $(this).html(message).fadeIn(200);
                });
            }
            
            // Hide after 5 seconds if success
            if (type === 'success') {
                setTimeout(function() {
                    messageElement.slideUp(300);
                }, 5000);
            }
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const settingsForm = document.getElementById('general-settings-form');
        
        if (settingsForm) {
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('save_settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification('Settings saved successfully!', 'success');
                        
                        // Update the form values with the saved values
                        Object.keys(data.settings).forEach(key => {
                            const input = document.querySelector(`[name="${key}"]`);
                            if (input) {
                                if (input.type === 'checkbox') {
                                    input.checked = data.settings[key] === '1';
                                } else {
                                    input.value = data.settings[key];
                                }
                            }
                        });
                        
                        // Update logo preview if logo was changed
                        if (data.settings.website_logo) {
                            const logoPreview = document.querySelector('.logo-preview img');
                            if (logoPreview) {
                                logoPreview.src = `../images/${data.settings.website_logo}`;
                            }
                        }
                    } else {
                        showNotification(data.message || 'Failed to save settings', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while saving settings', 'error');
                });
            });
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
    </script>

    <style>
    .notification {
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: 12px;
        backdrop-filter: blur(10px);
    }

    .notification.success {
        background-color: #4CAF50;
    }

    .notification.error {
        background-color: #f44336;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle form submission
        function handleFormSubmit(formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                
                // Show loading state
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitButton.disabled = true;
                
                fetch('save_settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification('Settings saved successfully!', 'success');
                        if (submitButton) showButtonSuccess(submitButton);
                        
                        // Update only the form values that were submitted
                        for (let [key, value] of formData.entries()) {
                            const input = document.querySelector(`[name="${key}"]`);
                            if (input) {
                                if (input.type === 'checkbox') {
                                    input.checked = value === 'on';
                                } else {
                                    input.value = value;
                                }
                            }
                        }
                    } else {
                        showNotification(data.message || 'Failed to save settings', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while saving settings', 'error');
                })
                .finally(() => {
                    // Reset button state
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
            });
        }

        // Handle all settings forms
        const settingsForms = [
            'general-settings-form',
            'contact-settings-form',
            'shipping-settings-form',
            'features-settings-form',
            'homepage-sections-form'
        ];

        settingsForms.forEach(handleFormSubmit);

        // Notification function
        function showNotification(message, type = 'success') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 4000);
        }

        // Add visual success state to buttons
        function showButtonSuccess(button) {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Saved!';
            button.style.backgroundColor = '#10b981';
            button.style.borderColor = '#10b981';
            
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.style.backgroundColor = '';
                button.style.borderColor = '';
            }, 2000);
        }
    });
    </script>

    <script>
    // ... existing code ...
        // Override editUser and deleteUser functions with debugging
        window.editUser = function(userId) {
            console.log('Debug: editUser called with ID:', userId);
            
            // Show loading notification
            showNotification('Loading user data...', 'info');
            
            // Fetch user data
            $.ajax({
                url: 'get_user.php',
                type: 'GET',
                data: { id: userId },
                dataType: 'json',
                success: function(response) {
                    try {
                        // Make sure we're working with a JavaScript object
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.success && data.user) {
                            // Populate form with user data
                            $('#edit_user_id').val(data.user.id);
                            $('#edit_first_name').val(data.user.first_name);
                            $('#edit_last_name').val(data.user.last_name);
                            $('#edit_email').val(data.user.email);
                            $('#edit_company').val(data.user.company_name);
                            $('#edit_role').val(data.user.role);
                            $('#edit_status').val(data.user.is_verified);
                            
                            // Clear password fields
                            $('#edit_user_password').val('');
                            $('#edit_user_confirm_password').val('');
                            
                            // Show the edit user modal
                            $('#editUserModal').css('display', 'block');
                            $('#editUserForm').css('display', 'block');
                        } else {
                            showNotification(data.message || 'Error loading user data', 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showNotification('Error loading user data', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    showNotification('Error loading user data: ' + error, 'error');
                }
            });
        };
        
        window.deleteUser = function(userId) {
            console.log('Debug: deleteUser called with ID:', userId);
            
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                // Show loading notification
                if (typeof showNotification === 'function') {
                    showNotification('Deleting user...', 'info');
                }
                
                // Send AJAX request to delete user
                $.ajax({
                    url: 'delete_user.php',
                    type: 'POST',
                    data: { id: userId },
                    success: function(response) {
                        console.log('Debug: Delete user response received');
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success) {
                                if (typeof showNotification === 'function') {
                                    showNotification('User deleted successfully!', 'success');
                                }
                                
                                // Remove user from the table
                                $(`tr[data-user-id="${userId}"]`).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                console.error('Debug: Error deleting user:', data.message);
                                if (typeof showNotification === 'function') {
                                    showNotification(data.message || 'Error deleting user', 'error');
                                }
                            }
                        } catch (e) {
                            console.error('Debug: Error parsing response:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Debug: AJAX error:', error);
                    }
                });
            }
        };
    // ... existing code ...
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle form submission
        function handleFormSubmit(formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                
                // Show loading state
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitButton.disabled = true;
                
                // For homepage sections form, ensure checkbox values are properly set
                if (formId === 'homepage-sections-form') {
                    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        formData.set(checkbox.name, checkbox.checked ? '1' : '0');
                    });
                }
                
                fetch('save_settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification('Homepage Sections saved successfully!', 'success');
                        
                        // Update form values with the saved values
                        for (let [key, value] of formData.entries()) {
                            const input = document.querySelector(`[name="${key}"]`);
                            if (input) {
                                if (input.type === 'checkbox') {
                                    input.checked = value === '1';
                                } else {
                                    input.value = value;
                                }
                            }
                        }
                    } else {
                        showNotification(data.message || 'Failed to save Homepage Sections', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while saving Homepage Sections', 'error');
                })
                .finally(() => {
                    // Reset button state
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
            });
        }

        // Handle all settings forms
        const settingsForms = [
            'general-settings-form',
            'contact-settings-form',
            'shipping-settings-form',
            'features-settings-form',
            'homepage-sections-form'
        ];

        settingsForms.forEach(handleFormSubmit);

        // Notification function
        function showNotification(message, type = 'success') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    });
    </script>
<?php renderAdminFooter(); ?>
