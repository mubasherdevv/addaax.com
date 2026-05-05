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
$product_name = '';
$product_description = '';
$price = '';
$price = '';
$category_id = 24;
$success_message = '';
$error_message = '';

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
        // Insert into database
        $sql = "INSERT INTO products (name, description, price, category_id, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", 
            $product_name, 
            $product_description, 
            $price,
            $category_id
        );
        
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            

            
            // Handle product images
            $upload_dir = '../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Check if files were uploaded
            if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
                $file_count = count($_FILES['product_images']['name']);
                
                for ($i = 0; $i < $file_count; $i++) {
                    $filename = $_FILES['product_images']['name'][$i];
                    $temp_file = $_FILES['product_images']['tmp_name'][$i];
                    
                    // Generate unique filename
                    $unique_filename = uniqid() . '_' . $filename;
                    $upload_path = $upload_dir . $unique_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($temp_file, $upload_path)) {
                        // Insert image record into database
                        $image_sql = "INSERT INTO product_images (product_id, image_path, is_primary, created_at) 
                                    VALUES (?, ?, ?, NOW())";
                        $is_primary = ($i === 0) ? 1 : 0; // First image is primary
                        $image_path = 'uploads/products/' . $unique_filename;
                        
                        $image_stmt = $conn->prepare($image_sql);
                        $image_stmt->bind_param("isi", $product_id, $image_path, $is_primary);
                        $image_stmt->execute();
                    }
                }
            }
            
            $success_message = "Product added successfully!";
            
            // Reset form fields
            $product_name = '';
            $product_description = '';
            $price = '';
        } else {
            $error_message = "Error adding product: " . $conn->error;
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<?php
require_once 'includes/admin_layout.php';
renderAdminHeader('Add Product');
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
        }
    </style>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Add New Product</h1>
                <div class="admin-actions">
                    <a href="product_management.php" class="btn btn-outline btn-icon"><i class="fas fa-arrow-left"></i> <span>Back to Products</span></a>
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
                
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product_name">Product Name*</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_description">Product Description</label>
                        <textarea id="product_description" name="product_description"><?php echo htmlspecialchars($product_description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($)*</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
                    </div>
                    
                    <h2 class="form-title">Product Images</h2>
                    
                    <div class="form-group">
                        <label for="product_images">Product Images (First image will be the main image)</label>
                        <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple>
                        <p style="font-size: 14px; color: #666; margin-top: 5px;">You can select multiple images. Maximum size per image: 2MB</p>
                        
                        <div class="image-preview-container" id="image-preview-container"></div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="product_management.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit">Add Product</button>
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
        
        // Image preview functionality with cropping
        $('#product_images').on('change', function(e) {
            const files = e.target.files;
            const previewContainer = $('#image-preview-container');
            
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
                            <div class="image-preview ${i === 0 ? 'primary' : ''}">
                                <img src="${canvas.toDataURL('image/jpeg', 0.9)}" alt="Preview">
                                <div class="remove-image" data-index="${i}">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="set-primary" data-index="${i}">
                                    ${i === 0 ? 'Primary Image' : 'Set as Primary'}
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
            const index = $(this).data('index');
            
            // Remove primary class from all images
            $('.image-preview').removeClass('primary');
            $('.set-primary').text('Set as Primary');
            
            // Add primary class to selected image
            $(this).closest('.image-preview').addClass('primary');
            $(this).text('Primary Image');
        });
    });
    </script>
<?php renderAdminFooter(); ?>
 