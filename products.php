<?php
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

// Filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$city_name = isset($_GET['city']) ? trim($_GET['city']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Build Query
$query_parts = ["p.status = 1"];
$params = [];
$types = '';

if (!empty($search)) {
    $query_parts[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $s = '%' . $search . '%';
    $params[] = $s; $params[] = $s;
    $types .= 'ss';
}

// SEO Slug Matching for City
if (!empty($city_name)) {
    $query_parts[] = "REPLACE(LOWER(p.city), ' ', '-') = ?";
    $params[] = $city_name;
    $types .= 's';
}

$where = "WHERE " . implode(" AND ", $query_parts);

// Fetch Products
$query = "SELECT p.*, c.name as category_name, u.first_name, u.last_name, u.profile_image, p.badges,
          IFNULL(NULLIF(p.image, ''), (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC LIMIT 1)) as display_image
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.seller_id = u.id
          $where 
          ORDER BY p.is_featured DESC, p.created_at DESC 
          LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Sidebar Cities
$sidebar_cities = $conn->query("SELECT name FROM cities WHERE status = 1 LIMIT 15")->fetch_all(MYSQLI_ASSOC);

renderHeader('Browse Ads | ADDAAX Premium', 'explore');
?>

    <main class="container-wide">
        <div class="listing-container">
            
            <!-- Main Content -->
            <div class="listing-main">
                
                <!-- Breadcrumbs & Search Header -->
                <nav class="breadcrumbs">
                    <a href="/index.php">Home</a> 
                    <span>></span> 
                    <a href="/escorts/">Escort</a>
                    <?php if (!empty($city_name)): ?>
                        <span>></span> 
                        <span class="current"><?php echo str_replace('-', ' ', htmlspecialchars($city_name)); ?></span>
                    <?php else: ?>
                        <span>></span> 
                        <span class="current">All Listings</span>
                    <?php endif; ?>
                </nav>

                <div class="listing-header">
                    <h2><?php echo $search ? 'Results for: "' . htmlspecialchars($search) . '"' : ($city_name ? ucwords(str_replace('-', ' ', htmlspecialchars($city_name))) . ' Escorts' : 'All Listings'); ?></h2>
                    
                    <!-- View Toggle (Mobile Only) -->
                    <div class="view-toggle">
                        <div class="view-btn active" id="listViewBtn" title="List View">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="view-btn" id="gridViewBtn" title="Grid View">
                            <i class="fas fa-th-large"></i>
                        </div>
                    </div>
                </div>

                <div class="listing-items-wrapper" id="listingItems">

                <!-- Product Cards -->
                <?php if(empty($products)): ?>
                    <div style="padding: 100px; text-align: center; color: var(--text-muted); background: var(--glass); border-radius: 20px;">
                        No listings found matching your criteria.
                    </div>
                <?php else: foreach($products as $ad): 
                    $raw_img = !empty($ad['display_image']) ? $ad['display_image'] : '';
                    $img = !empty($raw_img) ? (str_starts_with($raw_img, 'http') ? $raw_img : '/' . ltrim($raw_img, '/')) : '/images/placeholder.png';
                ?>
                    <a href="<?php echo getProductUrl($ad['id'], $ad['name']); ?>" class="product-card <?php echo $ad['is_featured'] ? 'featured-card' : ''; ?>">
                        <?php if($ad['is_featured']): ?>
                            <div class="featured-badge-svg">
                                <img src="/svg-icon/featured-desktop.svg" class="desktop-featured" alt="Featured Ad">
                                <img src="/svg-icon/featured-mobile.svg" class="mobile-featured" alt="Featured Ad">
                            </div>
                        <?php endif; ?>
                        <div class="product-image">
                            <?php if(!empty($raw_img)): ?>
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($ad['name']); ?>" width="300" height="200" loading="lazy">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #111; color: #444;">
                                    <i class="fas fa-image" style="font-size: 48px; opacity: 0.2;"></i>
                                </div>
                            <?php endif; ?>
                             
                             <!-- Special Badges -->
                            <?php if(!empty($ad['badges'])): 
                                $badges = explode(',', $ad['badges']);
                                ?>
                                <div class="special-badges-container">
                                    <?php foreach($badges as $badge): 
                                        $svg_file = '';
                                        if($badge == 'Hot Premium') $svg_file = 'hot.svg';
                                        if($badge == 'High Demand') $svg_file = 'high_demand.svg';
                                        if($badge == 'Recommended') $svg_file = 'recommended.svg';
                                        if($badge == 'Popular') $svg_file = 'popular.svg';
                                        
                                        if(!empty($svg_file)):
                                        ?>
                                        <div class="special-badge-svg">
                                            <img src="/svg-icon/<?php echo $svg_file; ?>" alt="<?php echo htmlspecialchars($badge); ?>">
                                            <div class="badge-shine"></div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="city-badge"><?php echo strtoupper(htmlspecialchars($ad['city'] ?? 'Lahore')); ?></div>
                        </div>
                        <div class="product-info">
                            <div class="info-top">
                                <span class="category-path"><?php echo strtoupper(htmlspecialchars($ad['category_name'] ?? 'Escorts')); ?></span>
                                <span class="post-time"><?php echo time_ago($ad['created_at']); ?></span>
                            </div>
                            
                            <h3 class="product-title"><?php echo htmlspecialchars($ad['name']); ?></h3>
                            
                            <p class="product-desc"><?php echo mb_strimwidth(strip_tags($ad['description'] ?? ''), 0, 180, "..."); ?></p>
                            
                            <div class="info-bottom">
                                <div class="product-price">
                                    <span>PKR <?php echo number_format($ad['price']); ?></span>
                                    
                                    <!-- Desktop WhatsApp -->
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $ad['phone'] ?? ''); ?>" class="whatsapp-desktop" onclick="event.stopPropagation();" target="_blank">
                                        <img src="/svg-icon/whatsapp-icon/dektop.svg" alt="WhatsApp">
                                    </a>

                                    <!-- Mobile List WhatsApp -->
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $ad['phone'] ?? ''); ?>" class="whatsapp-mobile-list" onclick="event.stopPropagation();" target="_blank">
                                        <img src="/svg-icon/whatsapp-icon/mobile-list.svg" alt="WhatsApp">
                                    </a>

                                    <!-- Mobile Grid WhatsApp -->
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $ad['phone'] ?? ''); ?>" class="whatsapp-mobile-grid" onclick="event.stopPropagation();" target="_blank">
                                        <img src="/svg-icon/whatsapp-icon/mobile-grid.svg" alt="WhatsApp">
                                    </a>
                                </div>
                                
                                <!-- User Profile (Name Only) -->
                                <div class="seller-pill">
                                    <i class="fas fa-user-circle"></i>
                                    <span class="seller-name"><?php echo htmlspecialchars(($ad['first_name'] ?? 'Admin') . ' ' . ($ad['last_name'] ?? '')); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
                </div>

            </div>

            <!-- Sidebar -->
            <aside class="sidebar-desktop">
                <div class="sidebar-card">
                    <div class="sidebar-title">ADS IN PAKISTAN</div>
                    <div class="sidebar-list">
                        <?php foreach($sidebar_cities as $sc): ?>
                            <a href="/escorts/<?php echo urlencode(strtolower(str_replace(' ', '-', $sc['name']))); ?>">
                                <?php echo htmlspecialchars($sc['name']); ?> Escort
                            </a>
                        <?php endforeach; ?>
                        <a href="/cities.php" style="color: var(--accent-gold); font-weight: 800;">+ see more</a>
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <script>
        // View Toggle Logic
        const listViewBtn = document.getElementById('listViewBtn');
        const gridViewBtn = document.getElementById('gridViewBtn');
        const listingItems = document.getElementById('listingItems');

        if (listViewBtn && gridViewBtn && listingItems) {
            listViewBtn.addEventListener('click', () => {
                listViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
                listingItems.classList.remove('grid-view');
            });

            gridViewBtn.addEventListener('click', () => {
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
                listingItems.classList.add('grid-view');
            });
        }
    </script>

<?php
renderFooter();
?>