<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    echo '<div style="grid-column: 1 / -1; text-align: center; padding: 30px; background-color: #ffebee; color: #c62828; border-radius: 8px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 36px; margin-bottom: 15px;"></i>
            <h2>Access Denied</h2>
            <p>You need to be logged in as an admin to view this content.</p>
          </div>';
    exit;
}

// Helper function to format currency values
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '$' . number_format($amount, 2, '.', ',');
    }
}

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$view = isset($_GET['view']) ? $_GET['view'] : 'grid';

// Build SQL query
$sql = "SELECT p.*, c.name as category_name, u.first_name, u.last_name, u.email as user_email
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE 1=1";

$params = [];
$types = '';

// Add category filter
if (!empty($category)) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

// Add search filter
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

// Add sorting
switch ($sort) {
    case 'newest':
        $sql .= " ORDER BY p.id DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.id ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    default:
        $sql .= " ORDER BY p.id DESC";
}

try {
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    // If no products found
    if (empty($products)) {
        if ($view === 'list') {
            echo '<tr><td colspan="7" style="text-align: center; padding: 50px 0;">
                    <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h2>No Products Found</h2>
                    <p>No products match your current filters.</p>
                    <button onclick="resetFilters()" class="btn btn-primary" style="margin-top: 20px;">Reset Filters</button>
                  </td></tr>';
        } else {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 50px 0;">
                    <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h2>No Products Found</h2>
                    <p>No products match your current filters.</p>
                    <button onclick="resetFilters()" class="btn btn-primary" style="margin-top: 20px;">Reset Filters</button>
                  </div>';
        }
        exit;
    }
    
    // Output the filtered products
    if ($view === 'list') {
        // List view output (table rows)
        foreach ($products as $product) {
            ?>
            <tr data-id="<?php echo $product['id']; ?>">
                <td><input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>"></td>
                <td>
                    <div class="product-thumb">
                        <?php
                        // Get the first product image
                        try {
                            $image_sql = "SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1";
                            $image_stmt = $conn->prepare($image_sql);
                            if ($image_stmt) {
                                $image_stmt->bind_param('i', $product['id']);
                                $image_stmt->execute();
                                $image_result = $image_stmt->get_result();
                                if ($image_result && $image_result->num_rows > 0) {
                                    $image_path = $image_result->fetch_assoc()['image_path'];
                                } else {
                                    $image_path = 'images/placeholder.jpg';
                                }
                            } else {
                                $image_path = 'images/placeholder.jpg';
                            }
                        } catch (Exception $e) {
                            $image_path = 'images/placeholder.jpg';
                        }
                        ?>
                        <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                </td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                <td>
                    <?php echo formatCurrency($product['price']); ?>
                </td>
                <td>
                    <div style="font-size: 13.5px; line-height: 1.4;">
                        <strong style="color: #1e293b; display: block; margin-bottom: 2px;">
                            <?php echo htmlspecialchars(($product['first_name'] ?? '') . ' ' . ($product['last_name'] ?? 'Admin')); ?>
                        </strong>
                        <div style="color: #64748b; font-size: 12px; margin-bottom: 3px;">
                            <i class="fas fa-envelope" style="width: 14px; opacity: 0.7;"></i> <?php echo htmlspecialchars($product['user_email'] ?? 'System'); ?>
                        </div>
                        <?php if (!empty($product['city']) || !empty($product['province'])): ?>
                        <div style="color: #6366f1; font-size: 12px; font-weight: 500;">
                            <i class="fas fa-map-marker-alt" style="width: 14px; opacity: 0.8;"></i> 
                            <?php 
                                $location = [];
                                if (!empty($product['city'])) $location[] = $product['city'];
                                if (!empty($product['province'])) $location[] = $product['province'];
                                echo htmlspecialchars(implode(', ', $location));
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="table-actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="#" class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>); return false;"><i class="fas fa-trash"></i></a>
                        <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn-view"><i class="fas fa-eye"></i></a>
                    </div>
                </td>
            </tr>
            <?php
        }
    } else {
        // Grid view output (cards)
        foreach ($products as $product) {
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php
                    // Get the first product image
                    try {
                        $image_sql = "SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1";
                        $image_stmt = $conn->prepare($image_sql);
                        if ($image_stmt) {
                            $image_stmt->bind_param('i', $product['id']);
                            $image_stmt->execute();
                            $image_result = $image_stmt->get_result();
                            if ($image_result && $image_result->num_rows > 0) {
                                $image_path = $image_result->fetch_assoc()['image_path'];
                            } else {
                                $image_path = 'images/placeholder.jpg';
                            }
                        } else {
                            $image_path = 'images/placeholder.jpg';
                        }
                    } catch (Exception $e) {
                        $image_path = 'images/placeholder.jpg';
                    }
                    ?>
                    <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-content">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-category">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                    </div>
                    <div class="product-price">
                        <?php echo formatCurrency($product['price']); ?>
                    </div>
                    <div class="product-user-info" style="margin-bottom: 15px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 13px; line-height: 1.4;">
                            <strong style="color: #1e293b; display: block; margin-bottom: 2px;">
                                <i class="fas fa-user-circle" style="color: #64748b; margin-right: 4px;"></i>
                                <?php echo htmlspecialchars(($product['first_name'] ?? '') . ' ' . ($product['last_name'] ?? 'Admin')); ?>
                            </strong>
                            <div style="color: #64748b; font-size: 12px; margin-bottom: 3px;">
                                <i class="fas fa-envelope" style="width: 14px; opacity: 0.7;"></i> <?php echo htmlspecialchars($product['user_email'] ?? $_SESSION['user_email'] ?? 'System'); ?>
                            </div>
                            <?php if (!empty($product['city']) || !empty($product['province'])): ?>
                            <div style="color: #6366f1; font-size: 12px; font-weight: 500; margin-top: 4px;">
                                <i class="fas fa-map-marker-alt" style="width: 14px; opacity: 0.8;"></i> 
                                <?php 
                                    $location = [];
                                    if (!empty($product['city'])) $location[] = $product['city'];
                                    if (!empty($product['province'])) $location[] = $product['province'];
                                    echo htmlspecialchars(implode(', ', $location));
                                ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> <span>Edit</span></a>
                        <a href="#" class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>); return false;"><i class="fas fa-trash"></i> <span>Delete</span></a>
                        <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn-view"><i class="fas fa-eye"></i> <span>View</span></a>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
} catch (Exception $e) {
    if ($view === 'list') {
        echo '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #f44336;">
                <i class="fas fa-exclamation-triangle" style="font-size: 36px; margin-bottom: 15px;"></i>
                <h2>Error Loading Products</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Please try refreshing the page or contact your system administrator if the problem persists.</p>
              </td></tr>';
    } else {
        echo '<div style="grid-column: 1 / -1; text-align: center; padding: 30px; background-color: #ffebee; color: #c62828; border-radius: 8px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 36px; margin-bottom: 15px;"></i>
                <h2>Error Loading Products</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Please try refreshing the page or contact your system administrator if the problem persists.</p>
              </div>';
    }
}

$conn->close();
?> 