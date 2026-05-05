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
$category_id = 0;
$category_name = '';
$category_description = '';
$parent_id = 0;
$success_message = '';
$error_message = '';

// Process category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Check if category has products
    $check_products = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_products);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $product_count = $check_stmt->get_result()->fetch_assoc()['count'];
    
    if ($product_count > 0) {
        $error_message = "Cannot delete category: {$product_count} product(s) are assigned to this category.";
    } else {
        // Check if category has subcategories
        $check_subcategories = "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?";
        $check_sub_stmt = $conn->prepare($check_subcategories);
        $check_sub_stmt->bind_param("i", $delete_id);
        $check_sub_stmt->execute();
        $subcategory_count = $check_sub_stmt->get_result()->fetch_assoc()['count'];
        
        if ($subcategory_count > 0) {
            $error_message = "Cannot delete category: {$subcategory_count} subcategories are assigned to this category.";
        } else {
            // Safe to delete
            $delete_sql = "DELETE FROM categories WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $delete_id);
            
            if ($delete_stmt->execute()) {
                $success_message = "Category deleted successfully!";
            } else {
                $error_message = "Error deleting category: " . $conn->error;
            }
        }
    }
}

// Process form submission for add/edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description']);
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['category_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.";
        } else {
            $upload_dir = '../uploads/categories/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('category_') . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/categories/' . $file_name;
            } else {
                $error_message = "Failed to upload image. Please try again.";
            }
        }
    }
    
    // Validate form data
    if (empty($category_name)) {
        $error_message = "Category name is required";
    } else {
        if (isset($_POST['category_id']) && is_numeric($_POST['category_id'])) {
            // Updating existing category
            $category_id = intval($_POST['category_id']);
            
            // Check if the parent is not the category itself
            if ($parent_id === $category_id) {
                $error_message = "A category cannot be its own parent";
            } else {
                // Get current image path if no new image is uploaded
                if (empty($image_path)) {
                    $current_image_sql = "SELECT image FROM categories WHERE id = ?";
                    $current_image_stmt = $conn->prepare($current_image_sql);
                    $current_image_stmt->bind_param("i", $category_id);
                    $current_image_stmt->execute();
                    $current_image_result = $current_image_stmt->get_result();
                    $current_image = $current_image_result->fetch_assoc();
                    $image_path = $current_image['image'] ?? '';
                }
                
                $sql = "UPDATE categories SET name = ?, description = ?, parent_id = ?, image = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $category_name, $category_description, $parent_id, $image_path, $category_id);
                
                if ($stmt->execute()) {
                    $success_message = "Category updated successfully!";
                    $category_name = '';
                    $category_description = '';
                    $parent_id = 0;
                    $category_id = 0;
                } else {
                    $error_message = "Error updating category: " . $conn->error;
                }
            }
        } else {
            // Add new category
            $sql = "INSERT INTO categories (name, description, parent_id, image, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $category_name, $category_description, $parent_id, $image_path);
            
            if ($stmt->execute()) {
                $success_message = "Category added successfully!";
                $category_name = '';
                $category_description = '';
                $parent_id = 0;
            } else {
                $error_message = "Error adding category: " . $conn->error;
            }
        }
    }
}

// Handle edit requests
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $category_id = intval($_GET['edit']);
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $category_name = $category['name'];
        $category_description = $category['description'];
        $parent_id = $category['parent_id'];
        $current_image = $category['image'] ?? '';
    } else {
        $error_message = "Category not found";
        $category_id = 0;
    }
}

// Get all categories
$categories = [];
$categories_sql = "SELECT c.*, IFNULL(p.name, 'None') as parent_name, 
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
                   FROM categories c
                   LEFT JOIN categories p ON c.parent_id = p.id
                   ORDER BY c.name";
$categories_result = $conn->query($categories_sql);

if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get parent categories for dropdown (exclude current category if editing)
$parent_categories = [];
$parent_sql = "SELECT id, name FROM categories WHERE id != ? ORDER BY name";
$parent_stmt = $conn->prepare($parent_sql);
$parent_stmt->bind_param("i", $category_id);
$parent_stmt->execute();
$parent_result = $parent_stmt->get_result();

if ($parent_result) {
    while ($row = $parent_result->fetch_assoc()) {
        $parent_categories[] = $row;
    }
}
?>

<?php
require_once 'includes/admin_layout.php';
renderAdminHeader('Category Management');
renderAdminSidebar('categories');
?>
    <style>
        /* Category Management Specific Styles */
        .admin-panel {
            display: flex;
            gap: 30px;
            margin: 20px 0;
        }
        
        .category-form {
            flex: 1;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        .categories-list {
            flex: 2;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        .category-form h2,
        .categories-list h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #3f51b5;
            outline: none;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }
        
        .btn-primary {
            background-color: #3f51b5;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .categories-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            color: #333;
        }
        
        .categories-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #555;
        }
        
        .categories-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .category-actions {
            display: flex;
            gap: 10px;
        }
        
        .category-actions a {
            color: #3f51b5;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .category-actions a.delete {
            color: #f44336;
        }
        
        .category-actions a:hover {
            text-decoration: underline;
        }
        
        .category-search {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .category-search input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .category-search button {
            padding: 10px 15px;
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
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
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #777;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #555;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-panel {
                flex-direction: column;
            }
            
            .category-form, 
            .categories-list {
                width: 100%;
            }
            
            .categories-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                position: relative;
                scroll-behavior: smooth;
            }
            
            .categories-table tbody tr {
                position: relative;
                transition: transform 0.3s ease;
            }
            
            .categories-table tbody tr.swiped {
                transform: translateX(-100px);
            }
            
            .category-details-panel {
                position: absolute;
                right: -100px;
                top: 0;
                width: 100px;
                height: 100%;
                background-color: #f5f5f5;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
                z-index: 10;
            }
            
            .category-details-panel .action-btn {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background-color: #3f51b5;
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 5px 0;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            
            .category-details-panel .action-btn:hover {
                background-color: #303f9f;
            }
            
            .category-details-panel .action-btn.edit {
                background-color: #4caf50;
            }
            
            .category-details-panel .action-btn.delete {
                background-color: #f44336;
            }
            
            .category-details-panel .action-btn.view {
                background-color: #2196f3;
            }
            
            /* Scroll indicators */
            .scroll-indicator {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 40px;
                height: 40px;
                background-color: rgba(0, 0, 0, 0.5);
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 50%;
                cursor: pointer;
                z-index: 20;
            }
            
            .scroll-indicator.left {
                left: 10px;
            }
            
            .scroll-indicator.right {
                right: 10px;
            }
            
            .scroll-indicator:hover {
                background-color: rgba(0, 0, 0, 0.7);
            }
        }
        
        .image-upload-container {
            margin-top: 10px;
        }
        
        .current-image {
            margin-bottom: 15px;
            max-width: 200px;
        }
        
        .current-image img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .image-input {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .image-preview {
            margin-top: 10px;
            max-width: 200px;
            display: none;
        }
        
        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .image-help {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        /* Admin Profile Styles */
        .admin-profile {
            padding: 20px;
            text-align: center;
            background-color: #1a1a2e;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto 10px;
            background-color: #fff;
            min-width: 80px;
            min-height: 80px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .admin-profile img:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .admin-profile h3 {
            margin: 0 0 5px;
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .admin-profile p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Category Management</h1>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="admin-panel">
                <div class="category-form">
                    <h2><?php echo $category_id ? 'Edit Category' : 'Add New Category'; ?></h2>
                    
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                        <?php if ($category_id): ?>
                        <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="category_name">Category Name*</label>
                            <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_description">Description</label>
                            <textarea id="category_description" name="category_description"><?php echo htmlspecialchars($category_description); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_image">Category Image</label>
                            <div class="image-upload-container">
                                <?php if (!empty($current_image)): ?>
                                <div class="current-image">
                                    <img src="../<?php echo htmlspecialchars($current_image); ?>" alt="Current category image">
                                </div>
                                <?php endif; ?>
                                <input type="file" id="category_image" name="category_image" accept="image/jpeg,image/png,image/gif,image/webp" class="image-input">
                                <div class="image-preview"></div>
                                <p class="image-help">Recommended size: 800x600px. Max file size: 2MB</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_id">Parent Category</label>
                            <select id="parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                <?php foreach($parent_categories as $parent): ?>
                                <option value="<?php echo $parent['id']; ?>" <?php echo ($parent_id == $parent['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($parent['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <?php if ($category_id): ?>
                            <a href="category_management.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Update Category</button>
                            <?php else: ?>
                            <button type="reset" class="btn-secondary">Reset</button>
                            <button type="submit" class="btn-primary">Add Category</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <div class="categories-list">
                    <h2>Categories</h2>
                    
                    <div class="category-search">
                        <input type="text" id="category-search" placeholder="Search categories...">
                        <button id="search-btn"><i class="fas fa-search"></i> Search</button>
                    </div>
                    
                    <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h3>No Categories Found</h3>
                        <p>Add your first category to get started</p>
                    </div>
                    <?php else: ?>
                    <div class="categories-table-wrapper">
                        <table class="categories-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Parent</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['parent_name']); ?></td>
                                    <td><?php echo $category['product_count']; ?></td>
                                    <td class="category-actions">
                                        <a href="category_management.php?edit=<?php echo $category['id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="category_management.php?delete=<?php echo $category['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this category?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <a href="product_management.php?category=<?php echo $category['id']; ?>">
                                            <i class="fas fa-box"></i> View Products
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
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
        
        // Category search functionality
        $('#search-btn').on('click', function() {
            searchCategories();
        });
        
        $('#category-search').on('keypress', function(e) {
            if (e.which === 13) {
                searchCategories();
            }
        });
        
        function searchCategories() {
            const searchQuery = $('#category-search').val().trim().toLowerCase();
            
            if (searchQuery === '') {
                // Show all categories
                $('.categories-table tbody tr').show();
                return;
            }
            
            // Filter categories
            $('.categories-table tbody tr').each(function() {
                const categoryName = $(this).find('td:first-child').text().toLowerCase();
                
                if (categoryName.includes(searchQuery)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Show message if no categories match
            if ($('.categories-table tbody tr:visible').length === 0) {
                if ($('.no-matches').length === 0) {
                    $('.categories-table').after('<div class="empty-state no-matches"><i class="fas fa-search"></i><h3>No Matching Categories</h3><p>Try a different search term</p></div>');
                }
            } else {
                $('.no-matches').remove();
            }
        }
        
        // Image preview functionality
        document.getElementById('category_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.querySelector('.image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Touch swipe functionality for medium devices
        if (window.innerWidth <= 992) {
            // Add scroll indicators
            $('.categories-table-wrapper').append('<div class="scroll-indicator left"><i class="fas fa-chevron-left"></i></div>');
            $('.categories-table-wrapper').append('<div class="scroll-indicator right"><i class="fas fa-chevron-right"></i></div>');
            
            // Add swipe panels to each row
            $('.categories-table tbody tr').each(function() {
                var row = $(this);
                var categoryId = row.data('category-id');
                
                // Create details panel
                var detailsPanel = $('<div class="category-details-panel"></div>');
                
                // Add action buttons
                detailsPanel.append('<div class="action-btn view" title="View Products"><i class="fas fa-eye"></i></div>');
                detailsPanel.append('<div class="action-btn edit" title="Edit Category"><i class="fas fa-edit"></i></div>');
                detailsPanel.append('<div class="action-btn delete" title="Delete Category"><i class="fas fa-trash"></i></div>');
                
                // Append panel to row
                row.append(detailsPanel);
                
                // Add category ID to row for reference
                row.attr('data-category-id', categoryId);
                
                // Handle swipe events
                var startX, moveX, isSwiped = false;
                
                row.on('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                    isSwiped = row.hasClass('swiped');
                });
                
                row.on('touchmove', function(e) {
                    moveX = e.touches[0].clientX;
                    var diff = startX - moveX;
                    
                    // Only allow swiping left (not right)
                    if (diff > 0) {
                        e.preventDefault();
                    }
                });
                
                row.on('touchend', function(e) {
                    var diff = startX - moveX;
                    
                    // If swiped left more than 50px, show details
                    if (diff > 50 && !isSwiped) {
                        row.addClass('swiped');
                    } 
                    // If swiped right more than 50px or swiped less than 50px, hide details
                    else if ((diff < -50 && isSwiped) || (Math.abs(diff) < 50 && isSwiped)) {
                        row.removeClass('swiped');
                    }
                });
                
                // Handle action button clicks
                detailsPanel.find('.action-btn.view').on('click', function() {
                    window.location.href = 'product_management.php?category=' + categoryId;
                });
                
                detailsPanel.find('.action-btn.edit').on('click', function() {
                    window.location.href = 'edit_category.php?id=' + categoryId;
                });
                
                detailsPanel.find('.action-btn.delete').on('click', function() {
                    if (confirm('Are you sure you want to delete this category?')) {
                        window.location.href = 'category_management.php?delete=' + categoryId;
                    }
                });
            });
            
            // Handle scroll indicators
            $('.scroll-indicator.left').on('click', function() {
                $('.categories-table-wrapper').animate({
                    scrollLeft: '-=200'
                }, 300);
            });
            
            $('.scroll-indicator.right').on('click', function() {
                $('.categories-table-wrapper').animate({
                    scrollLeft: '+=200'
                }, 300);
            });
            
            // Show/hide scroll indicators based on scroll position
            $('.categories-table-wrapper').on('scroll', function() {
                var wrapper = $(this);
                var scrollLeft = wrapper.scrollLeft();
                var maxScroll = wrapper[0].scrollWidth - wrapper[0].clientWidth;
                
                if (scrollLeft <= 0) {
                    $('.scroll-indicator.left').hide();
                } else {
                    $('.scroll-indicator.left').show();
                }
                
                if (scrollLeft >= maxScroll) {
                    $('.scroll-indicator.right').hide();
                } else {
                    $('.scroll-indicator.right').show();
                }
            });
            
            // Initial check for scroll indicators
            $('.categories-table-wrapper').trigger('scroll');
        }
    });
    </script>
<?php renderAdminFooter(); ?>
 