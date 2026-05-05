<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php';
require_once '../includes/wishlist_functions.php';
require_once '../includes/functions.php';

// Require login to access this page
requireLogin();

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Get user's wishlist products
$user_id = $_SESSION['user_id'];
$wishlist_products = getWishlistProducts($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | <?php echo htmlspecialchars($website_name); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($favicon)): ?>
    <link rel="icon" href="../images/<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .wishlist-item {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .wishlist-image {
            height: 200px;
            overflow: hidden;
            position: relative;
            background: #f8f9fa;
        }

        .wishlist-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .wishlist-item:hover .wishlist-image img {
            transform: scale(1.05);
        }

        .wishlist-info {
            padding: 1rem;
        }

        .wishlist-info h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .wishlist-price {
            font-size: 1.2rem;
            color: #3f51b5;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .wishlist-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn-remove-wishlist {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-remove-wishlist:hover {
            background: #c82333;
        }

        .btn-add-cart {
            background: #3f51b5;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-add-cart:hover {
            background: #303f9f;
        }

        .empty-wishlist {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .empty-wishlist i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .empty-wishlist h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .empty-wishlist p {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }

        .btn-browse {
            background: #3f51b5;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-browse:hover {
            background: #303f9f;
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
            <h1>My Wishlist</h1>
            <p>View and manage your saved products</p>
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
                    <li><a href="wishlist.php" class="active"><i class="fas fa-heart"></i> My Wishlist</a></li>
                    <li><a href="user_reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li><a href="recent_products.php"><i class="fas fa-box"></i> Recent Products</a></li>
                </ul>
            </div>
            
            <!-- Sidebar Toggle for Mobile -->
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Sidebar Overlay for Mobile -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            
            <div class="dashboard-content">
                <?php if (empty($wishlist_products)): ?>
                <div class="empty-wishlist">
                    <i class="fas fa-heart-broken"></i>
                    <h2>Your wishlist is empty</h2>
                    <p>Start adding products to your wishlist to save them for later.</p>
                    <a href="../products.php" class="btn-browse">Browse Products</a>
                </div>
                <?php else: ?>
                <div class="wishlist-grid">
                    <?php foreach ($wishlist_products as $product): ?>
                    <div class="wishlist-item" data-product-id="<?php echo $product['id']; ?>">
                        <div class="wishlist-image">
                            <?php if (!empty($product['image_path'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars(str_replace('uploads/', '', $product['image_path'])); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="wishlist-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="wishlist-price">
                                $<?php echo number_format($product['price'], 2); ?>
                            </div>
                            <div class="wishlist-actions">
                                <button class="btn-remove-wishlist" onclick="removeFromWishlist(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                                <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn-add-cart">
                                    <i class="fas fa-eye"></i> View Product
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
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
    });

    function removeFromWishlist(productId) {
        if (confirm('Are you sure you want to remove this item from your wishlist?')) {
            $.ajax({
                url: '../includes/remove_from_wishlist.php',
                method: 'POST',
                data: { product_id: productId },
                success: function(response) {
                    if (response.success) {
                        // Remove the item from the DOM
                        $(`.wishlist-item[data-product-id="${productId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if there are any items left
                            if ($('.wishlist-item').length === 0) {
                                location.reload(); // Reload to show empty state
                            }
                        });
                        
                        // Show success message
                        alert('Item removed from wishlist');
                    } else {
                        alert(response.message || 'Failed to remove item from wishlist');
                    }
                },
                error: function() {
                    alert('An error occurred while removing item from wishlist');
                }
            });
        }
    }
    </script>
</body>
</html> 