<?php
// Include website settings
require_once 'includes/website_settings.php';

// Check if the user is already logged in as admin
session_start();
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// If maintenance mode is disabled, redirect to home page
if (!$MAINTENANCE_MODE && !isset($_GET['preview'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - <?php echo htmlspecialchars($WEBSITE_NAME); ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (!empty($WEBSITE_SETTINGS['favicon'])): ?>
    <link rel="icon" href="images/<?php echo htmlspecialchars($WEBSITE_SETTINGS['favicon']); ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
        /* Maintenance page specific styles */
        .maintenance-container {
            text-align: center;
            padding: 50px 20px;
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .maintenance-logo {
            margin-bottom: 30px;
        }
        
        .maintenance-logo img {
            max-width: 200px;
            height: auto;
        }
        
        .maintenance-title {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .maintenance-message {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 30px;
        }
        
        .maintenance-icon {
            font-size: 72px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .maintenance-footer {
            margin-top: 40px;
            font-size: 14px;
            color: #777;
        }
        
        .admin-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .admin-link:hover {
            background-color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-logo">
            <?php if (!empty($WEBSITE_LOGO)): ?>
            <img src="images/<?php echo htmlspecialchars($WEBSITE_LOGO); ?>" alt="<?php echo htmlspecialchars($WEBSITE_NAME); ?> Logo">
            <?php else: ?>
            <img src="images/logo.svg" alt="<?php echo htmlspecialchars($WEBSITE_NAME); ?> Logo">
            <?php endif; ?>
        </div>
        
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1 class="maintenance-title">We're Under Maintenance</h1>
        
        <div class="maintenance-message">
            <p>We're currently performing scheduled maintenance on our website to bring you an even better shopping experience.</p>
            <p>Please check back soon. We apologize for any inconvenience.</p>
        </div>
        
        <?php if ($is_admin): ?>
        <a href="index.php" class="admin-link">View Site Anyway (Admin)</a>
        <?php endif; ?>
        
        <div class="maintenance-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($WEBSITE_NAME); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 