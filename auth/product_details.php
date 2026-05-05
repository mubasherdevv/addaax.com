<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Helper function to format currency values
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: product_management.php");
    exit;
}

$product_id = intval($_GET['id']);
$product = null;
$images = [];
$category_name = 'Uncategorized';
$error_message = '';

try {
    // Get product details
    $product_sql = "SELECT p.*, c.name as category_name 
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   WHERE p.id = ?";
    $product_stmt = $conn->prepare($product_sql);
    $product_stmt->bind_param('i', $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    
    if ($product_result && $product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $category_name = $product['category_name'] ?? 'Uncategorized';
        
        // Get product images
        $images_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC";
        $images_stmt = $conn->prepare($images_sql);
        $images_stmt->bind_param('i', $product_id);
        $images_stmt->execute();
        $images_result = $images_stmt->get_result();
        
        if ($images_result) {
            while ($image = $images_result->fetch_assoc()) {
                $images[] = $image;
            }
        }
    } else {
        $error_message = "Product not found!";
    }
} catch (Exception $e) {
    $error_message = "Error loading product details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Product Details | <?php echo htmlspecialchars($website_name); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($favicon)): ?>
    <link rel="icon" href="../images/<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <script src="../js/responsive.js" defer></script>
    <style>
        .product-details-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-details-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            border-bottom: 1px solid #f0f0f0;
            background-color: #fafafa;
        }
        
        .product-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            line-height: 1.3;
        }
        
        .product-actions {
            display: flex;
            gap: 12px;
        }
        
        .product-info {
            display: flex;
            flex-wrap: wrap;
            padding: 0;
        }
        
        .product-gallery {
            flex: 1;
            min-width: 350px;
            padding: 30px;
            background-color: #fff;
        }
        
        .product-main-image {
            width: 100%;
            height: 450px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .product-main-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .product-main-image:hover img {
            transform: scale(1.02);
        }
        
        .product-thumbnails {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .thumbnail {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .thumbnail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .thumbnail.active {
            border-color: #3f51b5;
            box-shadow: 0 4px 12px rgba(63, 81, 181, 0.2);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-data {
            flex: 1;
            min-width: 350px;
            padding: 30px;
            background-color: #fff;
            border-left: 1px solid #f0f0f0;
        }

        .product-meta {
            margin-bottom: 30px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .meta-item:hover {
            background-color: #f5f5f5;
        }

        .meta-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 120px;
            margin-right: 15px;
        }

        .meta-value {
            color: #34495e;
            flex: 1;
        }

        .meta-value.highlighted {
            color: #3f51b5;
            font-weight: 500;
        }

        .price-value {
            font-size: 24px;
            font-weight: 600;
            color: #3f51b5;
        }

        .price-compare {
            font-size: 18px;
            color: #95a5a6;
            text-decoration: line-through;
            margin-left: 10px;
        }

        .dimension-item i {
            color: #3f51b5;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #555;
            margin-bottom: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 8px;
            background-color: #f5f5f5;
        }
        
        .btn-back:hover {
            color: #3f51b5;
            background-color: #e8eaf6;
            transform: translateX(-3px);
        }

        .btn-edit, .btn-delete {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background-color: #4caf50;
            color: white;
        }

        .btn-edit:hover {
            background-color: #43a047;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background-color: #e53935;
            transform: translateY(-2px);
        }

        @media (max-width: 992px) {
            .product-info {
                flex-direction: column;
            }
            
            .product-gallery, .product-data {
                width: 100%;
            }
            
            .product-main-image {
                height: 350px;
            }

            .product-data {
                border-left: none;
                border-top: 1px solid #f0f0f0;
            }
        }

        @media (max-width: 576px) {
            .product-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .product-actions {
                width: 100%;
                justify-content: center;
            }

            .product-main-image {
                height: 300px;
            }

            .thumbnail {
                width: 70px;
                height: 70px;
            }

            .meta-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .meta-label {
                min-width: auto;
            }
        }

        /* Product Information Section */
        .product-information {
            margin-top: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .info-section {
            padding: 30px;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-section h2 {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background-color: #f5f5f5;
            transform: translateY(-2px);
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: #3f51b5;
            font-size: 16px;
        }

        .info-value {
            color: #34495e;
            font-size: 15px;
            line-height: 1.5;
        }

        .description-content {
            color: #34495e;
            line-height: 1.8;
            font-size: 15px;
        }

        .specifications-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .specifications-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: #f9f9f9;
            color: #2c3e50;
            font-weight: 600;
            border-radius: 8px 0 0 8px;
            width: 200px;
        }

        .specifications-table td {
            padding: 12px 15px;
            background-color: #f9f9f9;
            color: #34495e;
            border-radius: 0 8px 8px 0;
        }

        .specifications-table tr:hover th,
        .specifications-table tr:hover td {
            background-color: #f5f5f5;
        }

        .additional-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .additional-info-item {
            background-color: #f9f9f9;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #34495e;
        }

        .additional-info-item i {
            color: #3f51b5;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .specifications-table th {
                width: 150px;
            }
        }

        /* Sidebar Styles */
        .admin-profile {
            padding: 20px;
            text-align: center;
            background-color: #1a1a2e;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto 10px;
            background-color: #fff;
            min-width: 80px;
            min-height: 80px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .admin-profile img:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .admin-profile h3 {
            margin: 0 0 5px;
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .admin-profile p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        /* Header Styles */
        header {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        header .logo:hover {
            color: #3f51b5;
        }

        header .logo img {
            height: 35px;
            margin-right: 8px;
            object-fit: contain;
        }

        header .user-actions {
            display: flex;
            gap: 10px;
        }

        header .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        header .btn i {
            margin-right: 6px;
        }

        header .btn-icon {
            background: #f5f5f5;
            color: #333;
        }

        header .btn-icon:hover {
            background: #e0e0e0;
        }

        header .btn-outline {
            border: 1px solid #ddd;
            background: transparent;
            color: #333;
        }

        header .btn-outline:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        .inventory-history {
            margin-top: 2rem;
        }

        .inventory-history table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .inventory-history th,
        .inventory-history td {
            padding: 0.5rem;
            text-align: left;
            border: 1px solid #ddd;
        }

        .inventory-history th {
            background-color: #f5f5f5;
        }

        .positive {
            color: green;
        }

        .negative {
            color: red;
        }

        .neutral {
            color: gray;
        }

        .product-details {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin: 2rem 0;
        }

        .product-details h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .product-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .product-info p {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 0;
            transition: all 0.3s ease;
        }

        .product-info p:hover {
            background: #f0f2f5;
            transform: translateY(-2px);
        }

        .product-info strong {
            color: #2c3e50;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .inventory-history {
            margin-top: 2rem;
        }

        .inventory-history h3 {
            color: #2c3e50;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
        }

        .inventory-history table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .inventory-history th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #f0f0f0;
        }

        .inventory-history td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            color: #34495e;
        }

        .inventory-history tr:last-child td {
            border-bottom: none;
        }

        .inventory-history tr:hover td {
            background: #f8f9fa;
        }

        .positive {
            color: #2ecc71;
            font-weight: 600;
        }

        .negative {
            color: #e74c3c;
            font-weight: 600;
        }

        .neutral {
            color: #95a5a6;
        }

        @media (max-width: 768px) {
            .product-details {
                padding: 1.5rem;
            }

            .product-info {
                grid-template-columns: 1fr;
            }

            .inventory-history {
                overflow-x: auto;
            }

            .inventory-history table {
                min-width: 600px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <a href="../index.php" class="logo">
                <?php if (!empty($website_logo)): ?>
                <img src="../images/<?php echo htmlspecialchars($website_logo); ?>" alt="<?php echo htmlspecialchars($website_name); ?> Logo">
                <?php else: ?>
                <img src="../images/logo.svg" alt="<?php echo htmlspecialchars($website_name); ?> Logo">
                <?php endif; ?>
                <span><?php echo htmlspecialchars($website_name); ?></span>
            </a>
            
            <div class="user-actions">
                <a href="dashboard.php" class="btn btn-icon"><i class="fas fa-home"></i> <span>User Dashboard</span></a>
                <a href="logout.php" class="btn btn-outline btn-icon"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </div>
        </div>
    </header>

    <!-- Admin Dashboard Container -->
    <div class="admin-container">
        <!-- Sidebar toggle button for mobile -->
        <button class="sidebar-toggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Sidebar overlay for mobile -->
        <div class="sidebar-overlay"></div>
        
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-profile">
                <?php 
                // Get admin profile image
                $admin_id = $_SESSION['user_id'];
                $profile_image = 'admin-avatar.jpg'; // Default image
                
                // Check if profile_image column exists and has a value
                $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
                if ($column_check && $column_check->num_rows > 0) {
                    // Get admin profile image
                    $img_query = "SELECT profile_image FROM users WHERE id = ?";
                    $stmt = $conn->prepare($img_query);
                    $stmt->bind_param("i", $admin_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $row = $result->fetch_assoc()) {
                        if (!empty($row['profile_image'])) {
                            $profile_image = $row['profile_image'];
                        }
                    }
                }
                ?>
                <img src="../images/<?php echo htmlspecialchars($profile_image); ?>" alt="Admin Profile" id="sidebar-profile-img">
                <h3><?php echo htmlspecialchars($_SESSION["user_name"]); ?></h3>
                <p>Super Admin</p>
            </div>
            
            <ul class="admin-menu">
                <li>
                    <a href="admin_dashboard.php?tab=dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php?tab=website_settings">
                        <i class="fas fa-cog"></i> Website Settings
                    </a>
                </li>
                <li>
                    <a href="admin_dashboard.php?tab=users">
                        <i class="fas fa-users"></i> User Management
                    </a>
                </li>
                <li>
                    <a href="product_management.php" class="active">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li>
                    <a href="category_management.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="featured_content.php">
                        <i class="fas fa-star"></i> Featured Content
                    </a>
                </li>
                <li>
                    <a href="scan.php">
                        <i class="fas fa-qrcode"></i> QR Code Scanner
                    </a>
                </li>
                <li>
                    <a href="generate_all_qr.php">
                        <i class="fas fa-qrcode"></i> Generate All QR Codes
                    </a>
                </li>
                <li>
                    <a href="products_without_qr.php">
                        <i class="fas fa-exclamation-circle"></i> Products Without QR
                    </a>
                </li>
                <li>
                    <a href="order_management.php">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
               
                <li>
                    <a href="admin_dashboard.php?tab=my_profile">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-user"></i> My Account
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content">
            <a href="product_management.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Product Management
            </a>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php elseif ($product): ?>
            
            <div class="product-details-container">
                <div class="product-header">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-icon">
                            <i class="fas fa-edit"></i> <span>Edit Product</span>
                        </a>
                        <a href="product_management.php?delete=<?php echo $product['id']; ?>" class="btn btn-danger btn-icon" onclick="return confirm('Are you sure you want to delete this product?');">
                            <i class="fas fa-trash"></i> <span>Delete</span>
                        </a>
                    </div>
                </div>
                
                <div class="product-info">
                    <div class="product-gallery">
                        <div class="product-main-image">
                            <?php if (!empty($images)): ?>
                            <img id="main-image" src="../<?php echo htmlspecialchars($images[0]['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                            <img src="../images/placeholder.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                        <div class="product-thumbnails">
                            <?php foreach ($images as $index => $image): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-image="../<?php echo htmlspecialchars($image['image_path']); ?>">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Thumbnail">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-data">
                        <div class="product-information">
                            <!-- Basic Information Section -->
                            <div class="info-section">
                                <h2>Basic Information</h2>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="fas fa-tag"></i>
                                            Category
                                        </div>
                                        <div class="info-value"><?php echo htmlspecialchars($category_name); ?></div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="fas fa-dollar-sign"></i>
                                            Price
                                        </div>
                                        <div class="info-value">
                                            <span class="price-value"><?php echo formatCurrency($product['price']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Specifications Section -->
                            <div class="info-section">
                                <h2>Specifications</h2>
                                <table class="specifications-table">
                                </table>
                            </div>

                            <!-- Description Section -->
                            <div class="info-section">
                                <h2>Description</h2>
                                <div class="description-content">
                                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description provided.')); ?>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="info-section">
                                <h2>Additional Information</h2>
                                <div class="additional-info">
                                    <div class="additional-info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        Created: <?php echo date('F j, Y', strtotime($product['created_at'])); ?>
                                    </div>
                                    <div class="additional-info-item">
                                        <i class="fas fa-clock"></i>
                                        Last Updated: <?php echo date('F j, Y', strtotime($product['updated_at'])); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- QR Code Section -->
                            <?php if (!empty($product['qr_code'])): ?>
                            <div class="info-section">
                                <h2>QR Code</h2>
                                <div class="qr-code-container" style="text-align: center; margin: 20px 0;">
                                    <img src="../<?php echo htmlspecialchars($product['qr_code']); ?>" alt="Product QR Code" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                                    <p style="margin-top: 10px; color: #666;">Scan this QR code to update inventory</p>
                                    <a href="../<?php echo htmlspecialchars($product['qr_code']); ?>" download class="btn btn-primary" style="margin-top: 15px; display: inline-block;">
                                        <i class="fas fa-download"></i> Download QR Code
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="info-section">
                                <h2>QR Code</h2>
                                <div class="qr-code-container" style="text-align: center; margin: 20px 0;">
                                    <p style="color: #666;">No QR code available for this product.</p>
                                    <a href="generate_qr.php?id=<?php echo $product['id']; ?>" class="btn btn-primary" style="margin-top: 15px; display: inline-block;">
                                        <i class="fas fa-qrcode"></i> Generate QR Code
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="product-details">
                <h2>Product Details</h2>
                <div class="product-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($product['name'] ?? ''); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                    <p><strong>Price:</strong> $<?php echo number_format($product['price'] ?? 0, 2); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($category_name ?? 'Uncategorized'); ?></p>
                </div>

                <div class="inventory-history">
                    <h3>Inventory History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Previous Quantity</th>
                                <th>New Quantity</th>
                                <th>Change</th>
                                <th>Updated By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $history_query = "SELECT * FROM inventory_logs WHERE product_id = ? ORDER BY created_at DESC LIMIT 10";
                            $history_stmt = $conn->prepare($history_query);
                            $history_stmt->bind_param("i", $product_id);
                            $history_stmt->execute();
                            $history_result = $history_stmt->get_result();

                            while ($log = $history_result->fetch_assoc()) {
                                $change = $log['new_quantity'] - $log['previous_quantity'];
                                $change_class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
                                ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo $log['previous_quantity']; ?></td>
                                    <td><?php echo $log['new_quantity']; ?></td>
                                    <td class="<?php echo $change_class; ?>"><?php echo $change; ?></td>
                                    <td><?php echo htmlspecialchars($log['updated_by'] ?? 'System'); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php endif; ?>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Mobile Sidebar Toggle
        $('.sidebar-toggle').on('click', function() {
            $('.admin-sidebar').toggleClass('active');
            $('.sidebar-overlay').toggleClass('active');
            $('body').toggleClass('sidebar-open');
        });
        
        $('.sidebar-overlay').on('click', function() {
            $('.admin-sidebar').removeClass('active');
            $('.sidebar-overlay').removeClass('active');
            $('body').removeClass('sidebar-open');
        });
        
        // Image gallery functionality
        $('.thumbnail').on('click', function() {
            // Update main image
            const imageSrc = $(this).data('image');
            $('#main-image').attr('src', imageSrc);
            
            // Update active thumbnail
            $('.thumbnail').removeClass('active');
            $(this).addClass('active');
        });
    });

    function updateProductQuantity(productId, quantity) {
        fetch('update_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the quantity display
                document.getElementById('current-quantity').textContent = data.new_quantity;
                
                // Add the new history entry to the table
                const tbody = document.querySelector('.inventory-history tbody');
                const newRow = document.createElement('tr');
                const change = data.new_quantity - data.previous_quantity;
                const changeClass = change > 0 ? 'positive' : (change < 0 ? 'negative' : 'neutral');
                
                newRow.innerHTML = `
                    <td>${new Date().toLocaleString()}</td>
                    <td>${data.previous_quantity}</td>
                    <td>${data.new_quantity}</td>
                    <td class="${changeClass}">${change}</td>
                    <td>${data.updated_by}</td>
                `;
                
                tbody.insertBefore(newRow, tbody.firstChild);
                
                // Remove the last row if there are more than 10 entries
                if (tbody.children.length > 10) {
                    tbody.removeChild(tbody.lastChild);
                }
                
                showNotification('Quantity updated successfully', 'success');
            } else {
                showNotification(data.message || 'Error updating quantity', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating quantity', 'error');
        });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    </script>
</body>
</html> 