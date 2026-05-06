<?php
/**
 * Product Listing Page - Modern Aesthetic
 */
require_once 'auth/db_connect.php';
require_once 'includes/website_settings.php';
require_once 'includes/layout_functions.php';

// Pagination settings
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter handling (simplified for now)
$city = isset($_GET['city']) ? $conn->real_escape_string($_GET['city']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

// Build Query
$where_clauses = ["p.status = 1"];
if ($city) $where_clauses[] = "p.city = '$city'";
if ($category) $where_clauses[] = "c.slug = '$category'";

$where_sql = implode(" AND ", $where_clauses);

$query = "SELECT p.*, c.name as category_name, u.first_name, u.last_name, u.profile_image, p.badges,
          IFNULL(NULLIF(p.image, ''), (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC LIMIT 1)) as display_image
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.seller_id = u.id
          WHERE $where_sql
          ORDER BY p.is_featured DESC, p.created_at DESC 
          LIMIT $limit OFFSET $offset";

$result = $conn->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Get total for pagination
$total_query = "SELECT COUNT(*) as count FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where_sql";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_rows / $limit);

renderHeader('All Listings | ADDAAX Premium', 'browse');
?>

<main class="browse-listings page-header-offset">
    <div class="container-wide">
        <div class="breadcrumb-container">
            <nav class="breadcrumbs">
                <a href="/">Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <a href="/products.php">Escort</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span class="current">All Listings</span>
            </nav>
        </div>

        <div class="listing-layout">
            <!-- Sidebar / Filters -->
            <aside class="listing-sidebar">
                <div class="filter-card">
                    <h3 class="filter-title">Ads in Pakistan</h3>
                    <ul class="city-list">
                        <?php 
                        $city_list_query = "SELECT name, (SELECT COUNT(*) FROM products WHERE city = cities.name AND status = 1) as ad_count FROM cities WHERE status = 1 ORDER BY ad_count DESC LIMIT 15";
                        $city_list_result = $conn->query($city_list_query);
                        while($cl = $city_list_result->fetch_assoc()):
                        ?>
                        <li><a href="/escorts/<?php echo strtolower(str_replace(' ', '-', $cl['name'])); ?>"><?php echo $cl['name']; ?> Escort</a></li>
                        <?php endwhile; ?>
                        <li><a href="/cities.php" class="see-more">+ see more</a></li>
                    </ul>
                </div>
            </aside>

            <!-- Results Section -->
            <section class="listing-results">
                <div class="results-header">
                    <h1 class="results-title">All Listings</h1>
                    
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
                    $img = !empty($raw_img) ? "/uploads/products/" . $raw_img : '';
                    $is_featured = (isset($ad['is_featured']) && $ad['is_featured'] == 1);
                ?>
                    <a href="/product_details.php?id=<?php echo $ad['id']; ?>" class="product-card <?php echo $is_featured ? 'featured-card' : ''; ?>">
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
                            <?php if($is_featured): ?><div class="featured-badge">FEATURED</div><?php endif; ?>
                        </div>

                        <div class="product-info">
                            <div class="info-top">
                                <span class="category-path"><?php echo strtoupper(htmlspecialchars($ad['category_name'] ?? 'Escorts')); ?></span>
                                <span class="post-time"><?php echo time_ago($ad['created_at']); ?></span>
                            </div>
                            
                            <h3 class="product-title"><?php echo htmlspecialchars($ad['name']); ?></h3>
                            <p class="product-desc"><?php echo mb_strimwidth(strip_tags($ad['description'] ?? ''), 0, 180, "..."); ?></p>
                            
                            <div class="info-bottom">
                                <div class="product-price">PKR <?php echo number_format($ad['price']); ?></div>
                                
                                <div class="seller-pill-wrap">
                                    <?php if ($is_featured && !empty($ad['phone'])): ?>
                                        <object>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $ad['phone']); ?>" target="_blank" class="wa-btn-main">
                                                <img src="/svg-icon/whatsapp-icon/dektop.svg" alt="WA">
                                            </a>
                                        </object>
                                    <?php endif; ?>
                                    <div class="seller-pill">
                                        <i class="fas fa-user-circle"></i>
                                        <span class="seller-name"><?php echo htmlspecialchars(($ad['first_name'] ?? 'Admin') . ' ' . ($ad['last_name'] ?? '')); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<script>
document.getElementById('listViewBtn').addEventListener('click', function() {
    document.getElementById('listingItems').classList.remove('grid-view');
    this.classList.add('active');
    document.getElementById('gridViewBtn').classList.remove('active');
});

document.getElementById('gridViewBtn').addEventListener('click', function() {
    document.getElementById('listingItems').classList.add('grid-view');
    this.classList.add('active');
    document.getElementById('listViewBtn').classList.remove('active');
});
</script>

<?php renderFooter(); ?>