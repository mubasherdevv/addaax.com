<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/wishlist_functions.php';
require_once 'includes/layout_functions.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get product details with seller and location info
$product_query = "
    SELECT p.*, 
           c.name as category_name, 
           u.first_name as seller_name, 
           u.profile_image as seller_image, 
           u.created_at as seller_created_at,
           u.phone as seller_phone,
           p.badges
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.seller_id = u.id
    WHERE p.id = ? AND p.status = 1";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit;
}

// Get images
$img_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id");
$img_stmt->bind_param("i", $product_id);
$img_stmt->execute();
$images = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$primary_image = !empty($images) ? $images[0]['image_path'] : 'images/placeholder.png';

// Related Products
$related_stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.status = 1 LIMIT 6");
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// SEO Meta
$PAGE_TITLE = htmlspecialchars($product['name'] ?? '') . " | ADDAAX Premium Directory";
$META_DESC = mb_strimwidth(strip_tags($product['description'] ?? ''), 0, 160, "...");

renderHeader($PAGE_TITLE, 'explore');
?>

<!-- Product Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "<?php echo htmlspecialchars($product['name']); ?>",
  "image": [
    "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . ltrim($primary_image, '/'); ?>"
  ],
  "description": "<?php echo htmlspecialchars(strip_tags($product['description'])); ?>",
  "sku": "ADX-<?php echo $product['id']; ?>",
  "brand": {
    "@type": "Brand",
    "name": "ADDAAX"
  },
  "offers": {
    "@type": "Offer",
    "url": "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>",
    "priceCurrency": "PKR",
    "price": "<?php echo $product['price']; ?>",
    "availability": "https://schema.org/InStock",
    "itemCondition": "https://schema.org/NewCondition"
  }
}
</script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [{
    "@type": "ListItem",
    "position": 1,
    "name": "Home",
    "item": "https://addaax.com/"
  },{
    "@type": "ListItem",
    "position": 2,
    "name": "Ads",
    "item": "https://addaax.com/products.php"
  },{
    "@type": "ListItem",
    "position": 3,
    "name": "<?php echo htmlspecialchars($product['category_name']); ?>",
    "item": "https://addaax.com/products.php?category=<?php echo $product['category_id']; ?>"
  },{
    "@type": "ListItem",
    "position": 4,
    "name": "<?php echo htmlspecialchars($product['name']); ?>"
  }]
}
</script>

    <style>
        .recommend-scroll {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 20px;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
        }
        .recommend-scroll::-webkit-scrollbar { display: none; }
        .recommend-scroll > .product-card {
            min-width: 250px;
            flex: 0 0 250px;
            scroll-snap-align: start;
            height: auto;
            display: block;
        }

        .mobile-sticky-bar {
            position: fixed;
            bottom: 65px; /* Above bottom nav */
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.95);
            backdrop-filter: blur(20px);
            padding: 15px 20px;
            display: none;
            gap: 12px;
            z-index: 1000;
            border-top: 1px solid var(--glass-border);
        }

        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Breadcrumbs handled by modern-directory.css */
        
        @media (max-width: 768px) {
            .mobile-sticky-bar { display: flex; }
            body { padding-bottom: 140px; }
        }
    </style>

    <main class="product-detail-container container-wide">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="/index.php">Home</a> 
            <span>></span> 
            <a href="/products.php">Ads</a>
            <span>></span> 
            <a href="/escorts/">Escorts</a>
            <span>></span> 
            <span class="current"><?php echo htmlspecialchars($product['name'] ?? ''); ?></span>
        </nav>

        <div class="detail-grid">
            <!-- Left: Gallery & Main Info -->
            <div class="detail-left">
                <!-- Gallery Section -->
                <div class="gallery-card" style="max-width: 800px; margin-left: auto; margin-right: auto;">
                    <div class="main-image-box">
                        <img id="mainImage" src="<?php echo str_starts_with($primary_image, 'http') ? $primary_image : '/' . $primary_image; ?>" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" width="800" height="500" fetchpriority="high">
                    </div>
                    <?php if(count($images) > 1): ?>
                    <div class="thumb-strip hide-scrollbar">
                        <?php foreach($images as $idx => $img): 
                            $img_path = str_starts_with($img['image_path'], 'http') ? $img['image_path'] : '/' . $img['image_path'];
                        ?>
                            <div class="thumb-box <?php echo $idx == 0 ? 'active' : ''; ?>" onclick="changeImage('<?php echo htmlspecialchars($img_path); ?>', this)">
                                <img src="<?php echo htmlspecialchars($img_path); ?>" alt="" width="80" height="60" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Main Info Section -->
                <div class="detail-main-info">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div>
                            <div class="detail-price" style="margin-bottom: 10px;">PKR <?php echo number_format($product['price']); ?></div>
                            
                            <!-- Product Badges -->
                            <?php if(!empty($product['badges'])): 
                                $badges = explode(',', $product['badges']);
                                ?>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
                                    <?php foreach($badges as $badge): 
                                        $svg_file = '';
                                        if($badge == 'Hot Premium') $svg_file = 'hot.svg';
                                        if($badge == 'High Demand') $svg_file = 'high_demand.svg';
                                        if($badge == 'Recommended') $svg_file = 'recommended.svg';
                                        if($badge == 'Popular') $svg_file = 'popular.svg';
                                        
                                        if(!empty($svg_file)):
                                        ?>
                                        <div class="special-badge-svg" style="position: relative; overflow: hidden; border-radius: 5px;">
                                            <img src="/svg-icon/<?php echo $svg_file; ?>" alt="<?php echo htmlspecialchars($badge); ?>" style="display: block; height: 28px; width: auto;">
                                            <div class="badge-shine"></div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="action-btns-group">
                            <div class="action-icon-btn"><i class="far fa-heart"></i> Save</div>
                            <div class="action-icon-btn"><i class="fas fa-share-alt"></i> Share</div>
                            <div class="action-icon-btn"><i class="far fa-flag"></i> Report</div>
                        </div>
                    </div>

                    <h1 class="detail-title"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h1>
                    
                    <div class="detail-meta-list">
                        <div class="detail-meta-item"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                        <div class="detail-meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['city'] ?? ''); ?></div>
                        <div class="detail-meta-item"><i class="far fa-calendar"></i> <?php echo date('j/n/Y', strtotime($product['created_at'])); ?></div>
                    </div>

                    <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--glass-border);">
                        <h3 style="color: var(--white); font-size: 18px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">Description</h3>
                        <div class="description-box">
                            <?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?>
                        </div>
                        
                        <a href="#" style="color: #ff4d00; text-decoration: none; display: flex; align-items: center; gap: 8px; margin-top: 30px; font-weight: 700; font-size: 14px;">
                            <i class="fas fa-external-link-alt"></i> Visit Ad Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Sidebar -->
            <div class="detail-sidebar">
                <div class="sidebar-sticky">
                    <!-- Seller Card -->
                    <div class="contact-card">
                        <div class="seller-mini-profile">
                            <div class="avatar-wrap">
                                <?php if(!empty($product['seller_image'])): 
                                    $seller_img = str_starts_with($product['seller_image'], 'http') ? $product['seller_image'] : '/' . $product['seller_image'];
                                ?>
                                    <img src="<?php echo htmlspecialchars($seller_img); ?>" class="seller-avatar" style="width: 50px; height: 50px;">
                                <?php else: ?>
                                    <div class="seller-avatar" style="width: 50px; height: 50px; background: #0066ff; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px;">
                                        <?php echo strtoupper(substr($product['seller_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="seller-info">
                                <h4 style="font-size: 16px;"><?php echo htmlspecialchars($product['seller_name'] ?: 'Verified Seller'); ?></h4>
                                <p>Member since <?php echo date('Y', strtotime($product['seller_created_at'] ?: $product['created_at'])); ?></p>
                            </div>
                        </div>

                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $product['seller_phone'] ?: $product['phone'] ?: ''); ?>" target="_blank" class="btn-contact" style="background: #22c55e; height: 48px; border-radius: 8px;">
                            <i class="fab fa-whatsapp"></i> WhatsApp Seller
                        </a>
                        <button class="btn-contact" id="callBtn" onclick="showPhone('<?php echo htmlspecialchars($product['seller_phone'] ?: $product['phone'] ?: 'N/A'); ?>')" style="background: transparent; border: 1px solid var(--white); color: var(--white); height: 48px; border-radius: 8px;">
                            <i class="fas fa-phone-alt"></i> <span id="callBtnText">Show Phone Number</span>
                        </button>
                    </div>

                    <!-- Safety Card -->
                    <div class="safety-card" style="background: transparent; border: 1px solid var(--glass-border);">
                        <h5 style="color: var(--white); font-weight: 800;"><i class="fas fa-shield-alt"></i> Safety Tips</h5>
                        <ul style="color: var(--text-muted); font-size: 12px;">
                            <li><span>•</span> Meet in public</li>
                            <li><span>•</span> Check item first</li>
                            <li><span>•</span> Pay after collect</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <?php if(!empty($related_products)): ?>
        <section style="margin-top: 100px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
                <h2 class="section-title" style="text-align: left; margin-bottom: 0;">Recommended for You</h2>
                <a href="/products.php" style="color: var(--accent-gold); text-decoration: none; font-weight: 700; font-size: 12px; text-transform: uppercase;">See All</a>
            </div>
            <div class="recommend-scroll">
                <?php foreach($related_products as $rp): 
                    $rp_img = !empty($rp['image']) ? (str_starts_with($rp['image'], 'http') ? $rp['image'] : '/' . $rp['image']) : '/images/placeholder.png';
                ?>
                    <a href="<?php echo getProductUrl($rp['id'], $rp['name']); ?>" class="product-card" style="margin-bottom: 0; display: block;">
                        <div class="product-image" style="height: 180px;">
                            <img src="<?php echo htmlspecialchars($rp_img); ?>" alt="<?php echo htmlspecialchars($rp['name'] ?? ''); ?>" width="250" height="180" loading="lazy">
                            <?php if(!empty($rp['is_featured'])): ?>
                                <div class="featured-badge" style="top: 10px; left: 10px;">★ FEATURED</div>
                            <?php endif; ?>
                            <div class="city-badge" style="bottom: 10px; left: 10px; background: rgba(0,0,0,0.8);"><?php echo strtoupper(htmlspecialchars($rp['city'] ?? 'Lahore')); ?></div>
                        </div>
                        <div class="product-info" style="padding: 15px;">
                            <div class="category-path" style="font-size: 10px;"><?php echo strtoupper(htmlspecialchars($rp['category_name'])); ?></div>
                            <h3 class="product-title" style="font-size: 14px; height: 38px;"><?php echo htmlspecialchars($rp['name'] ?? ''); ?></h3>
                            <div class="info-bottom" style="border-top: none; padding-top: 0;">
                                <div class="product-price" style="font-size: 18px;">PKR <?php echo number_format($rp['price']); ?></div>
                                <div class="action-btn btn-whatsapp" style="width: 32px; height: 32px; border-radius: 8px;"><i class="fab fa-whatsapp"></i></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <script>
        // Gallery Function
        function changeImage(src, thumb) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumb-box').forEach(b => b.classList.remove('active'));
            thumb.classList.add('active');
        }

        // Show Phone Function
        function showPhone(phone) {
            document.getElementById('callBtnText').innerText = phone;
            document.getElementById('callBtn').onclick = () => window.location.href = 'tel:' + phone;
        }
    </script>

<?php
renderFooter();
?>