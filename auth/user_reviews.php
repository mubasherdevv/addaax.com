<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php';

// Require login to access this page
requireLogin();

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Get user information
$user_id = $_SESSION['user_id'];

// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$reviews_per_page = 5;
$offset = ($page - 1) * $reviews_per_page;

// Get total number of reviews
$total_reviews_query = "SELECT COUNT(*) as total FROM reviews WHERE user_id = ?";
$stmt = $conn->prepare($total_reviews_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_reviews / $reviews_per_page);

// Get user's reviews and ratings with pagination
$reviews_query = "SELECT r.*, p.name as product_name, p.id as product_id, 
                 COALESCE(pr.rating, 0) as rating,
                 pi.image_path as product_image
                 FROM reviews r
                 JOIN products p ON r.product_id = p.id
                 LEFT JOIN product_ratings pr ON r.product_id = pr.product_id AND pr.user_id = r.user_id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                 WHERE r.user_id = ?
                 ORDER BY r.created_at DESC
                 LIMIT ? OFFSET ?";

$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("iii", $user_id, $reviews_per_page, $offset);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews | <?php echo htmlspecialchars($website_name); ?></title>
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

        /* Reviews Page Specific Styles */
        .reviews-container {
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Sidebar Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            min-height: calc(100vh - 80px);
        }
        
        .sidebar {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
            transition: all 0.3s ease;
        }
        
        .sidebar-user {
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        
        .sidebar-user:hover .user-avatar {
            transform: scale(1.05);
        }
        
        .user-details h3 {
            margin-bottom: 5px;
            font-size: 18px;
        }
        
        .user-details p {
            color: var(--text-muted);
            margin-bottom: 0;
            font-size: 14px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: var(--radius-md);
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding-left: 20px;
        }
        
        .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99;
            animation: fadeIn 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Mobile responsive styles */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: fixed;
                top: 70px;
                left: -280px;
                bottom: 0;
                width: 280px;
                z-index: 100;
                overflow-y: auto;
                border-radius: 0;
                height: calc(100vh - 70px);
            }
            
            .sidebar.active {
                left: 0;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar-toggle {
                display: flex;
            }
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

        .review-card {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .review-card:last-child {
            border-bottom: none;
        }

        .review-card:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }

        .product-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            background: #f5f5f5;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .review-content {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .product-name a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .product-name a:hover {
            color: #2c64fc;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .review-rating .stars {
            color: #ffd700;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
            margin: 0.5rem 0;
        }

        .review-date {
            color: #888;
            font-size: 0.9rem;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-reviews i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-reviews p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #2c64fc;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #1a4fc7;
            transform: translateY(-2px);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .review-card {
                grid-template-columns: 1fr;
            }

            .product-image {
                width: 100%;
                height: 200px;
            }

            .reviews-container {
                padding: 1rem;
            }
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
            padding: 1rem;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background-color: #e9ecef;
            border-color: #ced4da;
        }

        .page-info {
            color: #666;
            font-size: 0.9rem;
        }

        .pagination-btn i {
            font-size: 0.8rem;
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

    <!-- Dashboard -->
    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>My Reviews & Ratings</h1>
            <p>View and manage your product reviews and ratings</p>
        </div>
        
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
                            $initials = strtoupper(substr($_SESSION['user_name'], 0, 1));
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
                    <li><a href="user_reviews.php" class="active"><i class="fas fa-star"></i> My Reviews</a></li>
                </ul>
            </div>
            
            <div class="dashboard-content">
                <div class="reviews-container">
                    <?php if (empty($reviews)): ?>
                        <div class="no-reviews">
                            <i class="fas fa-star"></i>
                            <p>You haven't reviewed any products yet.</p>
                            <a href="../products.php" class="btn-primary">Browse Products</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="product-image">
                                    <?php if (!empty($review['product_image'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars(str_replace('uploads/', '', $review['product_image'])); ?>" 
                                             alt="<?php echo htmlspecialchars($review['product_name']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="review-content">
                                    <h3 class="product-name">
                                        <a href="../product_details.php?id=<?php echo $review['product_id']; ?>">
                                            <?php echo htmlspecialchars($review['product_name']); ?>
                                        </a>
                                    </h3>
                                    <div class="review-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star" style="color: <?php echo $i <= $review['rating'] ? '#ffd700' : '#ddd'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-value"><?php echo number_format($review['rating'], 1); ?>/5</span>
                                    </div>
                                    <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                    <div class="review-date">
                                        Reviewed on <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <span class="page-info">
                                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
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

    <!-- Sidebar Toggle for Mobile -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
        
        // Close mobile menu on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992 && mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                if (mobileMenuToggle) {
                    mobileMenuToggle.classList.remove('active');
                }
            }
        });
    });
    </script>
</body>
</html> 