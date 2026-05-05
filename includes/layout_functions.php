<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/website_settings.php';

function renderHeader($page_title = 'Adaax Premium', $active_page = 'home') {
    global $conn;
    $user_name = $_SESSION['user_name'] ?? 'Account';
    $is_logged_in = isset($_SESSION['user_id']);
    
    // Dynamic SEO Fetch
    $current_page = basename($_SERVER['PHP_SELF']);
    $request_uri = $_SERVER['REQUEST_URI'];
    $meta_description = "";
    
    if (isset($conn)) {
        // Try matching by script name first, then by URI
        $seo_query = "SELECT meta_title, meta_description FROM seo_settings WHERE page_name = ? OR page_name = ? LIMIT 1";
        $seo_stmt = $conn->prepare($seo_query);
        if ($seo_stmt) {
            $seo_stmt->bind_param("ss", $current_page, $request_uri);
            $seo_stmt->execute();
            $seo_res = $seo_stmt->get_result();
            if ($seo_row = $seo_res->fetch_assoc()) {
                if (!empty($seo_row['meta_title'])) {
                    $page_title = $seo_row['meta_title'];
                }
                if (!empty($seo_row['meta_description'])) {
                    $meta_description = $seo_row['meta_description'];
                }
            }
            $seo_stmt->close();
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($page_title); ?></title>
        <?php if (!empty($meta_description)): ?>
        <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
        <?php endif; ?>
        <?php 
        $website_settings = getWebsiteSettings();
        $favicon = !empty($website_settings['favicon']) ? $website_settings['favicon'] : '';
        if (!empty($favicon)): ?>
        <link rel="icon" href="/images/<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
        <?php endif; ?>
        <link rel="stylesheet" href="/css/modern-directory.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    </head>
    <body>
        <!-- Header (Exact Dashboard Style) -->
        <header class="premium-header">
            <div class="container-wide header-inner">
        <div class="header-top-row">
            <?php 
            $website_settings = getWebsiteSettings();
            $logo_to_show = !empty($website_settings['website_logo']) ? $website_settings['website_logo'] : 'logo.jpg';
            $site_name = !empty($website_settings['website_name']) ? $website_settings['website_name'] : 'ADAAX';
            ?>
            <a href="/index.php" class="logo">
                <img src="<?php echo BASE_URL; ?>/images/<?php echo htmlspecialchars($logo_to_show); ?>" alt="Logo" height="35" style="vertical-align: middle; margin-right: 10px;" onerror="this.src='<?php echo BASE_URL; ?>/images/logo.jpg'; this.onerror=null;">
                <span><?php echo htmlspecialchars($site_name); ?></span>
            </a>
            <div class="mobile-nav-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
                <nav class="desktop-nav">
                    <a href="/index.php" class="nav-link <?php echo $active_page == 'home' ? 'active' : ''; ?>">Home</a>
                    <a href="/products.php" class="nav-link <?php echo $active_page == 'explore' ? 'active' : ''; ?>">Explore</a>
                    <a href="/cities.php" class="nav-link <?php echo $active_page == 'cities' ? 'active' : ''; ?>">Cities</a>
                </nav>
                <div class="header-actions">
                    <?php if ($is_logged_in): ?>
                        <a href="/auth/dashboard.php" class="user-profile-link <?php echo $active_page == 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($user_name); ?></span>
                        </a>
                    <?php else: ?>
                        <a href="/auth/login.php" class="login-btn">Login</a>
                    <?php endif; ?>
                    <a href="/post-ad.php" class="post-ad-btn <?php echo $active_page == 'post-ad' ? 'active' : ''; ?>">Post Your Ad</a>
                </div>
            </div>
        </header>

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenu">
            <div class="mobile-menu-top" style="padding: 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <a href="/index.php" class="logo">ADAAX</a>
                <button class="close-menu" id="closeMenu" style="position: static;"><i class="fas fa-times"></i></button>
            </div>
            <ul class="menu-links">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/products.php">Explore</a></li>
                <li><a href="/cities.php">Cities</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="/auth/dashboard.php">My Account</a></li>
                    <li><a href="/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/auth/login.php">Login / Register</a></li>
                <?php endif; ?>
                <li><a href="/post-ad.php" class="menu-post-ad">Post Your Ad</a></li>
            </ul>
        </div>
    <?php
}

function renderFooter() {
    ?>
        <!-- Footer -->
        <footer class="main-footer">
            <div class="container-wide">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <a href="/index.php" class="logo">ADAAX</a>
                        <p class="desc">Pakistan's most trusted premium classified marketplace. Reach millions of users with our verified and secure platform.</p>
                        <div class="trust-badges">
                            <div class="trust-badge">Verified</div>
                            <div class="trust-badge">Secure</div>
                        </div>
                    </div>
                    <div class="footer-col">
                        <h4>Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="/products.php">Explore Ads</a></li>
                            <li><a href="/post-ad.php">Post an Ad</a></li>
                            <li><a href="/cities.php">Cities</a></li>
                            <li><a href="/categories.php">Categories</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Support</h4>
                        <ul class="footer-links">
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Safety Tips</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Top Cities</h4>
                        <ul class="footer-links">
                            <li><a href="#">Lahore</a></li>
                            <li><a href="#">Karachi</a></li>
                            <li><a href="#">Islamabad</a></li>
                            <li><a href="#">Multan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>© Adaax™ 2026 - Premium Classifieds Marketplace. All Rights Reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Mobile Bottom Nav (Exact Dashboard Style) -->
        <div class="mobile-bottom-nav">
            <a href="/index.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="/products.php" class="nav-item">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </a>
            <a href="/post-ad.php" class="nav-item">
                <div class="nav-post-btn"><i class="fas fa-plus"></i></div>
                <span>Post Ad</span>
            </a>
            <a href="/auth/dashboard.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <div class="nav-item" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </div>
        </div>

        <script>
            // Navigation Logic
            const menuToggle = document.getElementById('menuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            const closeMenu = document.getElementById('closeMenu');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');

            function toggleM(show) {
                if (show) {
                    mobileMenu.classList.add('active');
                    document.body.classList.add('no-scroll');
                } else {
                    mobileMenu.classList.remove('active');
                    document.body.classList.remove('no-scroll');
                }
            }
            if (menuToggle) menuToggle.addEventListener('click', () => toggleM(true));
            if (closeMenu) closeMenu.addEventListener('click', () => toggleM(false));
            if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', () => toggleM(true));
        </script>
    </body>
    </html>
    <?php
}
?>
