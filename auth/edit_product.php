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

// Initialize variables
$product_id = 0;
$product_name = '';
$product_description = '';
$price = '';
$price = '';
$category_id = 24;
$success_message = '';
$error_message = '';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: product_management.php");
    exit;
}

$product_id = intval($_GET['id']);

// Get product details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: product_management.php");
    exit;
}

$product = $result->fetch_assoc();

// Set form values from product data
$product_name = $product['name'];
$product_description = $product['description'];
$price = $product['price'];
$price = $product['price'];

// Get product images
$images = [];
$images_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

while ($image = $images_result->fetch_assoc()) {
    $images[] = $image;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
$price = floatval($_POST['price']);
$price = floatval($_POST['price']);
    
    // Validate form data
    $errors = [];
    
    if (empty($product_name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($price) || $price <= 0) {
        $errors[] = "Price must be greater than zero";
    }
    
    if (empty($errors)) {
        // Update database
        $sql = "UPDATE products SET 
                name = ?,
                description = ?,
                price = ?,
                badges = ?,
                is_featured = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $badges = isset($_POST['badges']) ? implode(',', $_POST['badges']) : null;
        $is_featured = isset($_POST['is_featured']) ? intval($_POST['is_featured']) : 0;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsii", 
            $product_name, 
            $product_description, 
            $price,
            $badges,
            $is_featured,
            $product_id
        );
        
        if ($stmt->execute()) {
            // Handle product images
            $upload_dir = '../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Handle deleted images
            if (isset($_POST['deleted_images']) && !empty($_POST['deleted_images'])) {
                $deleted_images = explode(',', $_POST['deleted_images']);
                
                foreach ($deleted_images as $image_id) {
                    if (is_numeric($image_id)) {
                        // Get image path
                        $image_sql = "SELECT image_path FROM product_images WHERE id = ? AND product_id = ?";
                        $image_stmt = $conn->prepare($image_sql);
                        $image_stmt->bind_param("ii", $image_id, $product_id);
                        $image_stmt->execute();
                        $image_result = $image_stmt->get_result();
                        
                        if ($image_result->num_rows > 0) {
                            $image_path = $image_result->fetch_assoc()['image_path'];
                            
                            // Delete file if it exists
                            $full_path = '../' . $image_path;
                            if (file_exists($full_path)) {
                                unlink($full_path);
                            }
                            
                            // Delete record from database
                            $delete_sql = "DELETE FROM product_images WHERE id = ? AND product_id = ?";
                            $delete_stmt = $conn->prepare($delete_sql);
                            $delete_stmt->bind_param("ii", $image_id, $product_id);
                            $delete_stmt->execute();
                        }
                    }
                }
            }
            
            // Handle new images
            if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
                $file_count = count($_FILES['product_images']['name']);
                
                // Check if we have any existing images
                $has_existing_images = count($images) > 0;
                
                for ($i = 0; $i < $file_count; $i++) {
                    $filename = $_FILES['product_images']['name'][$i];
                    $temp_file = $_FILES['product_images']['tmp_name'][$i];
                    
                    // Generate unique filename
                    $unique_filename = uniqid() . '_' . $filename;
                    $upload_path = $upload_dir . $unique_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($temp_file, $upload_path)) {
                        // Insert image record into database
                        $image_sql = "INSERT INTO product_images (product_id, image_path, is_primary, filename, created_at) 
                                    VALUES (?, ?, ?, ?, NOW())";
                        
                        // First image is primary only if no existing images
                        $is_primary = (!$has_existing_images && $i === 0) ? 1 : 0;
                        $image_path = 'uploads/products/' . $unique_filename;
                        
                        $image_stmt = $conn->prepare($image_sql);
                        $image_stmt->bind_param("isis", $product_id, $image_path, $is_primary, $filename);
                        $image_stmt->execute();
                    }
                }
            }
            
            // Handle primary image setting
            if (isset($_POST['primary_image']) && is_numeric($_POST['primary_image'])) {
                $primary_image_id = intval($_POST['primary_image']);
                
                // Reset all images to non-primary
                $reset_sql = "UPDATE product_images SET is_primary = 0 WHERE product_id = ?";
                $reset_stmt = $conn->prepare($reset_sql);
                $reset_stmt->bind_param("i", $product_id);
                $reset_stmt->execute();
                
                // Set selected image as primary
                $primary_sql = "UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?";
                $primary_stmt = $conn->prepare($primary_sql);
                $primary_stmt->bind_param("ii", $primary_image_id, $product_id);
                $primary_stmt->execute();
            }
            
            $success_message = "Product updated successfully!";
            
            // Refresh product data after update
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            // Refresh images
            $images = [];
            $images_stmt->execute();
            $images_result = $images_stmt->get_result();
            while ($image = $images_result->fetch_assoc()) {
                $images[] = $image;
            }
        } else {
            $error_message = "Error updating product: " . $conn->error;
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<?php
require_once 'includes/admin_layout.php';
renderAdminHeader('Edit Product');
renderAdminSidebar('products');
?>
    <style>
        /* Form Styles */
        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="email"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3f51b5;
            outline: none;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group input[type="file"] {
            padding: 10px 0;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .form-actions button,
        .form-actions a {
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-submit {
            background-color: #3f51b5;
            color: white;
            border: none;
        }
        
        .btn-cancel {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        
        /* Alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Image preview styles */
        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .image-preview {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .image-preview:hover {
            border-color: #3f51b5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        
        .image-preview .remove-image {
            position: absolute;
            top: 8px;
            right: 8px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            opacity: 0;
        }
        
        .image-preview:hover .remove-image {
            opacity: 1;
        }
        
        .image-preview .remove-image:hover {
            background-color: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }
        
        .image-preview .set-primary {
            position: absolute;
            bottom: 8px;
            left: 8px;
            right: 8px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            opacity: 0;
        }
        
        .image-preview:hover .set-primary {
            opacity: 1;
        }
        
        .image-preview.primary {
            border-color: #3f51b5;
        }
        
        .image-preview.primary .set-primary {
            background-color: #3f51b5;
            opacity: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
        }
        
        .image-preview-container {
            justify-content: center;
        }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Edit Product: <?php echo htmlspecialchars($product_name); ?></h1>
                <div class="admin-actions">
                    <a href="product_management.php" class="btn btn-outline btn-icon"><i class="fas fa-arrow-left"></i> <span>Back to Products</span></a>
                    <a href="product_details.php?id=<?php echo $product_id; ?>" class="btn btn-primary btn-icon"><i class="fas fa-eye"></i> <span>View Product</span></a>
                </div>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <h2 class="form-title">Product Information</h2>
                
                <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $product_id; ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product_name">Product Name*</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_description">Product Description</label>
                        <textarea id="product_description" name="product_description"><?php echo htmlspecialchars($product_description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (PKR)*</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Ad Type</label>
                        <div class="ad-type-selection" style="display: flex; gap: 20px; margin-top: 10px; background: #f0f4ff; padding: 15px; border-radius: 8px; border: 1px solid #d0d7ff;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; font-size: 14px; color: #1e293b;">
                                <input type="radio" name="is_featured" value="0" <?php echo (!isset($product['is_featured']) || $product['is_featured'] == 0) ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #3f51b5;">
                                Simple Ad
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; font-size: 14px; color: #3f51b5;">
                                <input type="radio" name="is_featured" value="1" <?php echo (isset($product['is_featured']) && $product['is_featured'] == 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #3f51b5;">
                                <i class="fas fa-star" style="color: #ff9800;"></i> Featured Ad
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Special Badges</label>
                        <div class="badges-selection" style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <?php 
                            $current_badges = !empty($product['badges']) ? explode(',', $product['badges']) : [];
                            $available_badges = ['Hot Premium', 'High Demand', 'Recommended', 'Popular'];
                            foreach($available_badges as $badge): 
                            ?>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; font-size: 14px; color: #1e293b;">
                                <input type="checkbox" name="badges[]" value="<?php echo $badge; ?>" <?php echo in_array($badge, $current_badges) ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #3f51b5;">
                                <?php echo $badge; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <h2 class="form-title">Product Images</h2>
                    
                    <div class="form-group">
                        <?php if (!empty($images)): ?>
                        <div class="current-images">
                            <label>Current Images</label>
                            <div class="image-preview-container">
                                <?php foreach($images as $image): ?>
                                <div class="image-preview <?php echo $image['is_primary'] ? 'primary' : ''; ?>" data-id="<?php echo $image['id']; ?>">
                                    <img src="../<?php echo htmlspecialchars($image['image_path'] ?? ''); ?>" alt="Product Image">
                                    <div class="remove-image" data-id="<?php echo $image['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="set-primary" data-id="<?php echo $image['id']; ?>">
                                        <?php echo $image['is_primary'] ? 'Primary Image' : 'Set as Primary'; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <label for="product_images" style="margin-top: 20px;">Add New Images</label>
                        <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple>
                        <p style="font-size: 14px; color: #666; margin-top: 5px;">You can select multiple images. Maximum size per image: 2MB</p>
                        
                        <div class="image-preview-container" id="new-image-preview-container"></div>
                        
                        <!-- Hidden fields for tracking deleted images and primary image -->
                        <input type="hidden" name="deleted_images" id="deleted_images" value="">
                        <input type="hidden" name="primary_image" id="primary_image" value="<?php 
                            foreach($images as $image) {
                                if ($image['is_primary']) {
                                    echo $image['id'];
                                    break;
                                }
                            }
                        ?>">
                    </div>
                    
                    <div class="form-actions">
                        <a href="product_management.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit">Update Product</button>
                    </div>
                </form>
            </div>
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
        
        // Track deleted images
        let deletedImages = [];
        
        // Handle image removal
        $('.remove-image').on('click', function() {
            const imageId = $(this).data('id');
            deletedImages.push(imageId);
            $('#deleted_images').val(deletedImages.join(','));
            $(this).closest('.image-preview').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Handle setting primary image
        $('.set-primary').on('click', function() {
            const imageId = $(this).data('id');
            
            // Remove primary class from all images
            $('.image-preview').removeClass('primary');
            $('.set-primary').text('Set as Primary');
            
            // Add primary class to selected image
            $(this).closest('.image-preview').addClass('primary');
            $(this).text('Primary Image');
            
            // Update hidden field
            $('#primary_image').val(imageId);
        });
        
        // Image preview functionality with cropping
        $('#product_images').on('change', function(e) {
            const files = e.target.files;
            const previewContainer = $('#new-image-preview-container');
            
            // Clear preview container
            previewContainer.empty();
            
            // Preview each selected file
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = new Image();
                    img.src = e.target.result;
                    
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        
                        // Set canvas size to square
                        const size = Math.max(img.width, img.height);
                        canvas.width = size;
                        canvas.height = size;
                        
                        // Calculate position to center the image
                        const x = (size - img.width) / 2;
                        const y = (size - img.height) / 2;
                        
                        // Draw image centered on canvas
                        ctx.drawImage(img, x, y);
                        
                        const preview = $(`
                            <div class="image-preview new-image">
                                <img src="${canvas.toDataURL('image/jpeg', 0.9)}" alt="Preview">
                                <div class="remove-image" data-index="${i}">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        `);
                        
                        previewContainer.append(preview);
                    };
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Handle image removal
        $(document).on('click', '.remove-image', function() {
            $(this).closest('.image-preview').remove();
        });
        
        // Handle setting primary image
        $(document).on('click', '.set-primary', function() {
            const imageId = $(this).data('id');
            
            // Remove primary class from all images
            $('.image-preview').removeClass('primary');
            $('.set-primary').text('Set as Primary');
            
            // Add primary class to selected image
            $(this).closest('.image-preview').addClass('primary');
            $(this).text('Primary Image');
            
            // Update hidden field
            $('#primary_image').val(imageId);
        });
    });
    </script>
<?php renderAdminFooter(); ?>
 