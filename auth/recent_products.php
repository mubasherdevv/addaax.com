<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php';
require_once '../includes/functions.php';

// Require login to access this page
requireLogin();

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get recently ordered products
$sql_products = "SELECT p.*, oi.quantity, oi.price as ordered_price, o.order_date, pi.image_path
                 FROM products p
                 JOIN order_items oi ON p.id = oi.product_id
                 JOIN orders o ON oi.order_id = o.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                 WHERE o.user_id = ?
                 ORDER BY o.order_date DESC";
$stmt_products = $conn->prepare($sql_products);
$stmt_products->bind_param("i", $user_id);
$stmt_products->execute();
$result_products = $stmt_products->get_result();
$recent_products = $result_products->fetch_all(MYSQLI_ASSOC);

// Helper function to format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recently Ordered Products | <?php echo htmlspecialchars($website_name); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($favicon)): ?>
    <link rel="icon" href="../images/<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
        :root {
            --primary-color: #3f51b5;
            --primary-light: #757de8;
            --primary-dark: #002984;
            --secondary-color: #2196f3;
            --accent-color: #ff4081;
            --text-primary: #2c3e50;
            --text-secondary: #666;
            --text-light: #999;
            --background-light: #f5f5f5;
            --background-white: #ffffff;
            --border-color: #eee;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --error-color: #f44336;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .product-card {
            background: var(--background-white);
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-color);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid var(--border-color);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px var(--shadow-color);
        }
        
        .product-image {
            height: 180px;
            overflow: hidden;
            position: relative;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: 100%;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .no-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #999;
            font-size: 2rem;
            opacity: 0.5;
        }
        
        .product-info {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .product-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 600;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 700;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
        }
        
        .product-price::before {
            content: "$";
            font-size: 0.9rem;
            margin-right: 2px;
        }
        
        .product-quantity {
            color: var(--text-secondary);
            margin: 0.25rem 0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        
        .product-quantity::before {
            content: "×";
            margin-right: 4px;
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .order-date {
            color: var(--text-light);
            font-size: 0.8rem;
            margin: 0.25rem 0;
            display: flex;
            align-items: center;
        }
        
        .order-date::before {
            content: "\f073";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 6px;
            color: var(--primary-color);
            font-size: 0.8rem;
        }
        
        .btn-view {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
            margin-top: 0.75rem;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8rem;
        }
        
        .btn-view:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(63, 81, 181, 0.2);
        }
        
        .btn-view::after {
            content: "\f061";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-left: 6px;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }
        
        .btn-view:hover::after {
            transform: translateX(3px);
        }
        
        .product-card .product-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 500;
            z-index: 1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
                padding: 1rem;
            }
            
            .product-image {
                height: 150px;
            }
            
            .product-info {
                padding: 0.75rem;
            }
            
            .product-info h3 {
                font-size: 0.9rem;
            }
            
            .product-price {
                font-size: 1rem;
            }
            
            .product-quantity {
                font-size: 0.8rem;
            }
            
            .order-date {
                font-size: 0.75rem;
            }
            
            .btn-view {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }
        }
        
        .page-header {
            padding: 2rem;
            background: #f8f9fa;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            margin: 0;
            color: #333;
        }
        
        .page-header p {
            margin: 0.5rem 0 0 0;
            color: #666;
        }
        
        /* Dashboard Profile Animations */
        .sidebar-user {
            transition: all 0.3s ease;
            padding: 15px;
            border-radius: 8px;
        }
        
        .sidebar-user:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .user-avatar {
            width: 50px !important;
            height: 50px !important;
            border-radius: 50% !important;
            background-color: #3f51b5;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: 600;
            margin-right: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .user-details h3 {
            transition: color 0.3s ease;
            animation: slideIn 0.5s ease;
            color: var(--text-primary);
        }
        
        .user-details p {
            transition: color 0.3s ease;
            animation: slideIn 0.7s ease;
        }
        
        .sidebar-menu li a {
            transition: all 0.3s ease;
            color: var(--text-secondary);
        }
        
        .sidebar-menu li a:hover {
            padding-left: 25px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .dashboard-content {
            animation: fadeIn 0.8s ease;
        }
        
        .stat-card {
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card i {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover i {
            transform: scale(1.2);
            color: var(--primary-color);
        }
        
        .product-card {
            transition: all 0.3s ease;
            transform: translateY(30px);
            opacity: 0;
            animation: slideUpFadeIn 0.8s ease forwards;
        }
        
        .product-card:nth-child(1) { animation-delay: 0.8s; }
        .product-card:nth-child(2) { animation-delay: 1s; }
        .product-card:nth-child(3) { animation-delay: 1.2s; }
        
        @keyframes slideUpFadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .recent-products h2 {
            position: relative;
            overflow: hidden;
        }
        
        .recent-products h2:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-color);
            animation: expandWidth 1.2s ease-out 1.5s forwards;
        }
        
        @keyframes expandWidth {
            from { width: 0; }
            to { width: 100px; }
        }
        
        /* Enhance profile animations */
        .sidebar {
            position: relative;
            overflow: hidden;
        }
        
        .sidebar:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            transform: translateX(-100%);
            animation: slideBorder 1.5s ease 0.5s forwards;
        }
        
        @keyframes slideBorder {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }

        /* Sidebar Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            min-height: calc(100vh - 80px);
        }
        
        .sidebar {
            background: var(--background-white);
            border-radius: 10px;
            box-shadow: 0 2px 4px var(--shadow-color);
            padding: 1rem;
            position: sticky;
            top: 1rem;
            height: fit-content;
        }
        
        .sidebar-user {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-details h3 {
            margin: 0;
            font-size: 1rem;
            color: var(--text-primary);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--background-light);
            color: var(--primary-color);
        }
        
        .sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--primary-color);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                bottom: 0;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
        }
        
        /* Status badge colors */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: var(--warning-color);
            color: white;
        }
        
        .status-processing {
            background: var(--primary-color);
            color: white;
        }
        
        .status-shipped {
            background: var(--secondary-color);
            color: white;
        }
        
        .status-delivered {
            background: var(--success-color);
            color: white;
        }
        
        .status-canceled {
            background: var(--error-color);
            color: white;
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
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav id="mainNav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../products.php">Products</a></li>
                    <li><a href="../categories.php">Categories</a></li>
                    <li><a href="../about.php">About Us</a></li>
                    <li><a href="../contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="user-actions">
                <a href="dashboard.php" class="btn btn-icon btn-primary"><i class="fas fa-user"></i> <span>My Account</span></a>
                <a href="logout.php" class="btn btn-icon"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-container">
        <div class="dashboard-grid">
            <div class="sidebar">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        <?php 
                        // Check if profile_image column exists and has a value
                        $has_profile_image = false;
                        $profile_image = '';
                        
                        // Get column information
                        $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
                        if ($column_check && $column_check->num_rows > 0) {
                            // Get user profile image
                            $img_query = "SELECT profile_image FROM users WHERE id = ?";
                            $stmt = $conn->prepare($img_query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result && $row = $result->fetch_assoc()) {
                                if (!empty($row['profile_image'])) {
                                    $has_profile_image = true;
                                    $profile_image = $row['profile_image'];
                                }
                            }
                        }
                        
                        if ($has_profile_image):
                        ?>
                        <img src="../images/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" id="sidebar-profile-img">
                        <?php else: 
                            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                            echo $initials;
                        endif; ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                    </div>
                </div>
                
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="user_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="user_profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                    <li><a href="wishlist.php"><i class="fas fa-heart"></i> My Wishlist</a></li>
                    <li><a href="user_reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li><a href="recent_products.php" class="active"><i class="fas fa-box"></i> Recent Products</a></li>
                </ul>
            </div>
            
            <!-- Sidebar Toggle for Mobile -->
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Sidebar Overlay for Mobile -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Recently Ordered Products</h1>
                    <p>View all products you've ordered in the past</p>
                </div>
                
                <div class="products-grid">
                    <?php if (empty($recent_products)): ?>
                        <div class="no-products">
                            <p>You haven't ordered any products yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_products as $product): ?>
                            <div class="product-card">
                                <div class="product-badge">Ordered</div>
                                <div class="product-image">
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars(str_replace('uploads/', '', $product['image_path'])); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-price"><?php echo number_format($product['ordered_price'], 2); ?></p>
                                    <p class="product-quantity"><?php echo $product['quantity']; ?> units</p>
                                    <p class="order-date"><?php echo date('M d, Y', strtotime($product['order_date'])); ?></p>
                                    <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn-view">View Product</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="copyright">
                <p>&copy; 2023 Wholesale E-commerce. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mainNav = document.getElementById('mainNav');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                    this.classList.toggle('active');
                });
            }
            
            // Sidebar toggle functionality
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                
                if (sidebar.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && 
                        !sidebarToggle.contains(event.target) && 
                        sidebar.classList.contains('active')) {
                        toggleSidebar();
                    }
                }
            });
        });
    </script>
</body>
</html> 