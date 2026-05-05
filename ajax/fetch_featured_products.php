<?php
require_once '../includes/config.php';
require_once '../auth/db_connect.php';

// Get category ID from request
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build the SQL query
$sql = "SELECT p.*, c.name as category_name,
        (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 1";

// Add category filter if specified
if ($category_id > 0) {
    $sql .= " AND p.category_id = " . $category_id;
}

$sql .= " ORDER BY p.created_at DESC LIMIT 8";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($product = $result->fetch_assoc()) {
        ?>
        <div class="product-card">
            <div class="product-image">
                <?php if (!empty($product['primary_image'])): ?>
                    <img src="<?php echo htmlspecialchars($product['primary_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="image-placeholder">
                        <i class="fas fa-box"></i>
                        <span>No Image Available</span>
                    </div>
                <?php endif; ?>
                <?php if ($product['is_hot_deal']): ?>
                    <span class="badge hot-deal">Hot Deal</span>
                <?php endif; ?>
            </div>
            <div class="product-content">
                <div class="product-category">
                    <i class="fas fa-tag"></i>
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </div>
                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="product-meta">
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(4.5)</span>
                    </div>
                    <div class="product-stock">
                        <i class="fas fa-check-circle"></i>
                        <span>In Stock</span>
                    </div>
                </div>
                <div class="product-price">
                    <?php if ($product['sale_price'] > 0): ?>
                        <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <span class="sale-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                        <span class="discount-badge">-<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%</span>
                    <?php else: ?>
                        <span class="regular-price">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-actions">
                    <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <button class="btn btn-outline add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="no-products">No products found in this category.</div>';
}
?> 