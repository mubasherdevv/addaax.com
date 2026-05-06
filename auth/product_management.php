<?php
require_once '../config/database.php';  // Database constants
require_once '../config/constants.php'; // Application constants
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php'; // Include website settings
require_once '../includes/functions.php'; // Include helper functions

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    // Redirect to login page if not admin
    header("Location: login.php");
    exit;
}

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Helper function to format currency values
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '$' . number_format($amount, 2, '.', ',');
    }
}

// Process form submissions
$success_message = '';
$error_message = '';

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Check if product exists
    $check_sql = "SELECT id FROM products WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // First delete any product images to avoid orphaned files
        $image_sql = "SELECT image_path FROM product_images WHERE product_id = ?";
        $image_stmt = $conn->prepare($image_sql);
        $image_stmt->bind_param('i', $product_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result();
        
        while ($image = $image_result->fetch_assoc()) {
            $image_path = '../' . $image['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete product images from database
        $delete_images_sql = "DELETE FROM product_images WHERE product_id = ?";
        $delete_images_stmt = $conn->prepare($delete_images_sql);
        $delete_images_stmt->bind_param('i', $product_id);
        $delete_images_stmt->execute();
        
        // Delete product
        $delete_sql = "DELETE FROM products WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $product_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Product deleted successfully!";
        } else {
            $error_message = "Error deleting product: " . $conn->error;
        }
    } else {
        $error_message = "Product not found!";
    }
}

// Get all products
$products = [];
    $category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $category_name = '';

    try {
        // Pagination settings
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM products p";
    if ($category_filter > 0) {
        $count_sql .= " WHERE p.category_id = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param('i', $category_filter);
        $count_stmt->execute();
        $total_products = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total_products = $conn->query($count_sql)->fetch_assoc()['total'];
    }
    $total_pages = ceil($total_products / $limit);

    // Check if category filter is applied
    if ($category_filter > 0) {
        // Get category name for display
        $category_sql = "SELECT name FROM categories WHERE id = ?";
        $category_stmt = $conn->prepare($category_sql);
        $category_stmt->bind_param('i', $category_filter);
        $category_stmt->execute();
        $category_result = $category_stmt->get_result();
        
        if ($category_result && $category_row = $category_result->fetch_assoc()) {
            $category_name = $category_row['name'];
        }
        
        // Filter products by category
        $products_sql = "SELECT p.*, c.name as category_name, u.first_name, u.last_name, u.email as user_email
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN users u ON p.seller_id = u.id
                        WHERE p.category_id = ?
                        ORDER BY p.id DESC
                        LIMIT ? OFFSET ?";
        $products_stmt = $conn->prepare($products_sql);
        $products_stmt->bind_param('iii', $category_filter, $limit, $offset);
        $products_stmt->execute();
        $products_result = $products_stmt->get_result();
    } else {
        // Get all products
        $products_sql = "SELECT p.*, c.name as category_name, u.first_name, u.last_name, u.email as user_email
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN users u ON p.seller_id = u.id
                        ORDER BY p.id DESC
                        LIMIT ? OFFSET ?";
        $products_stmt = $conn->prepare($products_sql);
        $products_stmt->bind_param('ii', $limit, $offset);
        $products_stmt->execute();
        $products_result = $products_stmt->get_result();
    }
    
    if ($products_result) {
        while ($row = $products_result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $error_message = "Error loading products: " . $conn->error;
    }
} catch (Exception $e) {
    $error_message = "Error loading products: " . $e->getMessage();
}

// Get all categories for product form
$categories = [];
$categories_sql = "SELECT id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);

if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<?php
require_once 'includes/admin_layout.php';
renderAdminHeader('Product Management');
renderAdminSidebar('products');
?>
    
    <!-- Fancy Popup Notification Styles -->
    <style>
        .fancy-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(120%);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 9999;
        }

        .fancy-popup.show {
            transform: translateX(0);
        }

        .fancy-popup i {
            font-size: 20px;
        }

        .fancy-popup.success i {
            color: #4CAF50;
        }

        .fancy-popup.error i {
            color: #f44336;
        }

        .fancy-popup .message {
            font-size: 14px;
            color: #333;
        }

        .fancy-popup .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: #4CAF50;
            width: 100%;
            transform-origin: left;
            transform: scaleX(0);
            transition: transform 3s linear;
        }

        .fancy-popup.show .progress-bar {
            transform: scaleX(1);
        }
    </style>
    <style>
        /* Product Management Specific Styles */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .category-filter-indicator {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .clear-filter-btn {
            background-color: #f5f5f5;
            color: #333;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .clear-filter-btn:hover {
            background-color: #e0e0e0;
        }
        
        .product-card {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            border-color: #e2e8f0;
        }
        
        .product-image {
            height: 200px;
            width: 100%;
            overflow: hidden;
            position: relative;
            background-color: #f9f9f9;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-image img:hover {
            transform: scale(1.05);
        }
        
        .product-content {
            padding: 15px;
        }
        
        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .product-category {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-weight: 600;
            color: #3f51b5;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .product-stock {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .product-actions a {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-edit {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }
        .btn-edit:hover { background-color: #6366f1; color: white; }
        
        .btn-delete {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .btn-delete:hover { background-color: #ef4444; color: white; }
        
        .btn-view {
            background-color: rgba(15, 23, 42, 0.05);
            color: #0f172a;
        }
        .btn-view:hover { background-color: #0f172a; color: white; }
        
        .product-search {
            display: flex;
            margin-bottom: 25px;
            gap: 12px;
            background: #fff;
            padding: 8px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }
        
        .product-search input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            background: transparent;
            font-size: 0.95rem;
            color: #1e293b;
        }
        .product-search input:focus { outline: none; }
        
        .product-search button {
            padding: 10px 24px;
            background-color: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .product-search button:hover { background-color: #4f46e5; }
        
        .product-filters {
            display: flex;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .product-filters select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            min-width: 150px;
        }
        
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
            min-height: 120px;
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
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
        
        @media (max-width: 992px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .product-filters {
                flex-wrap: wrap;
            }
            
            .product-filters select {
                flex: 1;
                min-width: 120px;
            }
            
            /* Table responsiveness */
            .product-table th:nth-child(4),
            .product-table td:nth-child(4) {
                display: none; /* Hide price column on medium screens */
            }
        }
        
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
            
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .admin-header h1 {
                margin-bottom: 15px;
            }
            
            .admin-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .product-filters {
                flex-direction: column;
                gap: 10px;
            }
            
            .product-filters select {
                width: 100%;
            }
            
            /* Table responsiveness */
            .product-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .product-table th:nth-child(5), 
            .product-table td:nth-child(5) {
                display: none; /* Hide category column on small screens */
            }
            
            .product-actions a span {
                display: none; /* Hide text in buttons, keep only icons */
            }
            
            .product-actions a i {
                margin: 0;
            }
            
            .table-actions a {
                padding: 8px;
                min-width: 32px;
                text-align: center;
            }
            
            .table-actions a i {
                margin: 0;
            }
            
            .table-scroll-container::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                height: 100%;
                width: 30px;
                background: linear-gradient(to right, rgba(255,255,255,0), rgba(63, 81, 181, 0.1));
                z-index: 1;
                pointer-events: none;
            }
            
            .table-scroll-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 15px;
                background: linear-gradient(to left, rgba(255,255,255,0), rgba(63, 81, 181, 0.1));
                z-index: 1;
                pointer-events: none;
            }
        }
        
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr; /* Single column on smallest screens */
                gap: 15px;
            }
            
            .product-card {
                display: flex;
                flex-direction: row;
            }
            
            .product-image {
                width: 120px;
                height: 120px;
                flex-shrink: 0;
            }
            
            .product-content {
                flex: 1;
                display: flex;
                flex-direction: column;
            }
            
            .product-actions {
                margin-top: auto;
            }
            
            /* More table adjustments for very small screens */
            .product-table th:nth-child(7),
            .product-table td:nth-child(7) {
                display: none; /* Hide stock column on smallest screens */
            }
            
            /* Adding scroll indicator for table */
            .table-scroll-container {
                position: relative;
                margin-bottom: 15px;
                overflow: hidden;
            }
            
            .table-scroll-container.scrollable::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                height: 100%;
                width: 30px;
                background: linear-gradient(to right, rgba(255,255,255,0), rgba(63, 81, 181, 0.1));
                z-index: 1;
                pointer-events: none;
            }
            
            .table-scroll-container.scrolled-right::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 15px;
                background: linear-gradient(to left, rgba(255,255,255,0), rgba(63, 81, 181, 0.1));
                z-index: 1;
                pointer-events: none;
            }
            
            .table-scroll-container.scrolled-end::after {
                display: none;
            }
            
            .table-scroll-indicator {
                display: none;
                text-align: center;
                font-size: 14px;
                padding: 10px;
                color: #3f51b5;
                background-color: rgba(63, 81, 181, 0.05);
                border-radius: 5px;
                margin-bottom: 10px;
                border: 1px dashed #3f51b5;
                -webkit-tap-highlight-color: transparent;
            }
            
            .table-scroll-indicator i {
                animation: scroll-hint 1.5s infinite;
                display: inline-block;
                margin: 0 5px;
            }
            
            .product-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: #3f51b5 #f5f5f5;
            }
            
            .product-table-wrapper::-webkit-scrollbar {
                height: 8px;
            }
            
            .product-table-wrapper::-webkit-scrollbar-track {
                background: #f5f5f5;
                border-radius: 10px;
            }
            
            .product-table-wrapper::-webkit-scrollbar-thumb {
                background-color: #3f51b5;
                border-radius: 10px;
                border: 2px solid #f5f5f5;
            }
            
            @keyframes scroll-hint {
                0% { transform: translateX(-5px); }
                50% { transform: translateX(5px); }
                100% { transform: translateX(-5px); }
            }
            
            .bulk-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .bulk-actions select {
                width: 100%;
            }
            
            .bulk-actions button {
                width: 100%;
            }
            
            #selected-count {
                margin: 5px 0 0 0;
                display: block;
            }
        }
        
        /* Dropdown menu on cards */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #555;
            padding: 5px;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px 0;
            min-width: 150px;
            z-index: 10;
            display: none;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 8px 15px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f5f5f5;
        }
        
        .dropdown-item i {
            margin-right: 8px;
            width: 16px;
        }
        
        /* Table/List View Styles */
        .product-table-wrapper {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            overflow: hidden;
            display: none;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .product-table th {
            background-color: #f8fafc;
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .product-table th:hover {
            background-color: #f1f5f9;
        }
        
        .product-table th i {
            margin-left: 5px;
            font-size: 12px;
            opacity: 0.5;
        }
        
        .product-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
            font-size: 0.95rem;
        }
        
        .product-table tr:last-child td { border-bottom: none; }
        
        .product-table tr:hover {
            background-color: #f8fafc;
        }
        
        .product-table .product-thumb {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .product-table .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .table-actions {
            display: flex;
            gap: 8px;
        }
        
        .table-actions a {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        
        .product-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .bulk-actions {
            display: none;
            margin-top: 20px;
            padding: 15px 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }
        
        .bulk-actions select {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-right: 12px;
            font-size: 0.95rem;
            color: #1e293b;
        }
        
        .bulk-actions button {
            padding: 10px 20px;
            background-color: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .bulk-actions button:hover { background-color: #4f46e5; }
        
        .view-toggle {
            display: flex;
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 3px;
            margin-right: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .view-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border: none;
            background: none;
            color: #666;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin: 0 2px;
            min-width: 40px;
        }

        .view-btn:hover {
            background-color: #e0e0e0;
            color: #333;
        }

        .view-btn.active {
            background-color: #6366f1;
            color: white;
            box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
        }

        .view-btn i {
            font-size: 16px;
        }

        /* Separator between view buttons */
        .view-btn-separator {
            width: 1px;
            background-color: #ddd;
            margin: 6px 3px;
        }

        /* Tooltip styles */
        .view-btn[title] {
            position: relative;
        }

        .view-btn[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
        }

        /* Active state animation */
        .view-btn.active i {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        /* QR code specific styles */
        #qr-toggle.active {
            background-color: #4caf50;
        }

        #qr-toggle.active:hover {
            background-color: #43a047;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .view-toggle {
                margin-right: 10px;
            }

            .view-btn {
                padding: 6px 10px;
                min-width: 36px;
            }

            .view-btn i {
                font-size: 14px;
            }
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

        /* Update modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            width: 90%;
            max-width: 800px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #95a5a6;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #e74c3c;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        /* Update form styles */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 1rem;
        }

        .form-group p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin: 5px 0 10px;
            line-height: 1.4;
        }

        #bulk-products-input {
            width: 100%;
            min-height: 200px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            line-height: 1.5;
            resize: vertical;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        #bulk-products-input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        #bulk-products-input::placeholder {
            color: #bdc3c7;
        }

        #default-category,
        #default-stock {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        #default-category:focus,
        #default-stock:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        /* Update button styles */
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid #e0e0e0;
            color: #2c3e50;
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
            border-color: #bdc3c7;
            transform: translateY(-2px);
        }

        /* Loading state */
        .btn.loading {
            position: relative;
            color: transparent !important;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Success/Error messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.95rem;
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


        /* Add these styles to your existing CSS */
        #qr-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 15px;
            transition: background-color 0.3s ease;
        }

        #qr-toggle.active {
            background-color: #3f51b5;
            color: white;
        }

        .product-image {
            position: relative;
            height: 200px;
            width: 100%;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: opacity 0.3s ease;
        }

        .qr-code-img {
            position: absolute;
            top: 0;
            left: 0;
            padding: 10px;
            background: white;
        }

        /* For list view */
        .product-table .product-thumb {
            position: relative;
        }

        .product-table .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* View Toggle Styles */
        .product-table-wrapper {
            display: none;
        }
        .view-mode-list .product-grid {
            display: none !important;
        }
        .view-mode-list .product-table-wrapper {
            display: block !important;
        }
        .view-btn.active {
            background-color: #6366f1 !important;
            color: white !important;
        }
    </style>
    
    <!-- Main Content -->
    <main class="admin-content">
            <div class="admin-header">
                <h1>Product Management</h1>
                <?php if ($category_filter > 0 && !empty($category_name)): ?>
                <div class="category-filter-indicator">
                    <span>Showing products in category: <strong><?php echo htmlspecialchars($category_name ?? ''); ?></strong></span>
                    <a href="product_management.php" class="clear-filter-btn">Clear Filter</a>
                </div>
                <?php endif; ?>
                <div class="admin-actions">
                    <div class="view-toggle">
                        <button type="button" id="grid-view" class="view-btn active" title="Grid View">
                            <i class="fas fa-th"></i>
                        </button>
                        <div class="view-btn-separator"></div>
                        <button type="button" id="list-view" class="view-btn" title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Product Filters -->
            <div class="product-filters">
                <select id="sort-filter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="name_asc">Name: A to Z</option>
                    <option value="name_desc">Name: Z to A</option>
                </select>
            </div>
            
            <!-- Product Search -->
            <div class="product-search">
                <input type="text" id="product-search" placeholder="Search products...">
                <button type="button" id="search-btn"><i class="fas fa-search"></i> Search</button>
            </div>
            
            <!-- Product Grid -->
            <div class="product-grid">
                <?php if (!empty($error_message)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 30px; background-color: #ffebee; color: #c62828; border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 36px; margin-bottom: 15px;"></i>
                    <h2>Error Loading Products</h2>
                    <p><?php echo $error_message; ?></p>
                    <p>Please try refreshing the page or contact your system administrator if the problem persists.</p>
                </div>
                <?php elseif (empty($products)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px 0;">
                    <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h2>No Products Found</h2>
                    <p>Start by adding your first product.</p>
                    <a href="add_product.php" class="btn btn-primary" style="margin-top: 20px;">Add New Product</a>
                </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php
                            // Get the first product image and QR code
                            try {
                                $image_sql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC LIMIT 1";
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

                                // Get QR code path
                                $qr_code_path = isset($product['qr_code']) ? $product['qr_code'] : '';
                                if (empty($qr_code_path) || !file_exists('../' . $qr_code_path)) {
                                    $qr_code_path = 'images/no-qr-code.png';
                                }
                            } catch (Exception $e) {
                                $image_path = 'images/placeholder.jpg';
                                $qr_code_path = 'images/no-qr-code.png';
                            }
                            ?>
                            <img src="../<?php echo htmlspecialchars($image_path ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" class="product-img">
                            <?php if(isset($product['is_featured']) && $product['is_featured']): ?>
                                <div style="position: absolute; top: 10px; left: 10px; background: #ff9800; color: white; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; z-index: 5; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                    <i class="fas fa-star"></i> FEATURED
                                </div>
                            <?php endif; ?>
                            <img src="../<?php echo htmlspecialchars($qr_code_path ?? ''); ?>" alt="QR Code for <?php echo htmlspecialchars($product['name'] ?? ''); ?>" class="qr-code-img" style="display: none;">
                        </div>
                        <div class="product-content">
                             <h3 class="product-title"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h3>
                             
                             <!-- Grid Product Badges -->
                             <?php 
                             $badges_data = trim($product['badges'] ?? '');
                             if(!empty($badges_data)): 
                                 $badges_list = explode(',', $badges_data);
                                 ?>
                                 <div style="display: flex; gap: 5px; flex-wrap: wrap; margin: 5px 0 10px 0;">
                                     <?php foreach($badges_list as $b): 
                                         $b_clean = strtolower(trim($b));
                                         if(empty($b_clean)) continue;
                                         
                                         $svg_file = '';
                                         if($b_clean == 'hot premium') $svg_file = 'hot.svg';
                                         elseif($b_clean == 'high demand') $svg_file = 'high_demand.svg';
                                         elseif($b_clean == 'recommended') $svg_file = 'recommended.svg';
                                         elseif($b_clean == 'popular') $svg_file = 'popular.svg';
                                         
                                         if(!empty($svg_file)):
                                         ?>
                                         <img src="../svg-icon/<?php echo $svg_file; ?>" alt="<?php echo htmlspecialchars($b); ?>" style="height: 18px; width: auto; display: block;">
                                         <?php endif; ?>
                                     <?php endforeach; ?>
                                 </div>
                             <?php endif; ?>
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
                                <a href="#" class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)"><i class="fas fa-trash"></i> <span>Delete</span></a>
                                <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn-view"><i class="fas fa-eye"></i> <span>View</span></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions">
                <select id="bulk-action">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete Selected</option>
                    <option value="in-stock">Mark as In Stock</option>
                    <option value="out-of-stock">Mark as Out of Stock</option>
                    <option value="update-quantity">Update Quantity</option>
                </select>
                <button id="apply-bulk">Apply</button>
                <span id="selected-count" style="margin-left: 15px; color: #555;"></span>
            </div>

            <!-- Bulk Quantity Update Modal -->
            <div id="bulk-quantity-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Update Product Quantities</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="new-quantity">New Quantity</label>
                            <input type="number" id="new-quantity" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label for="quantity-action">Action</label>
                            <select id="quantity-action">
                                <option value="set">Set to this quantity</option>
                                <option value="add">Add to current quantity</option>
                                <option value="subtract">Subtract from current quantity</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="cancel-quantity" class="btn btn-outline">Cancel</button>
                        <button id="confirm-quantity" class="btn btn-primary">Update Quantities</button>
                    </div>
                </div>
            </div>

            <!-- Product Table with scroll indicator -->
            <div class="table-scroll-container">
                <div class="table-scroll-indicator">
                    <i class="fas fa-hand-pointer"></i> Swipe <i class="fas fa-arrows-left-right"></i> to view more
                </div>
                <div class="product-table-wrapper">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th width="40px"><input type="checkbox" id="select-all" class="product-checkbox"></th>
                                <th width="80px">Image</th>
                                <th>Product Name <i class="fas fa-sort"></i></th>
                                <th width="120px">Status</th>
                                <th width="100px">Featured</th>
                                <th width="200px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                <tr data-id="<?php echo $product['id']; ?>">
                                    <td><input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>"></td>
                                    <td>
                                        <div class="product-thumb">
                                            <?php
                                            // Get the first product image (reusing the same code from above)
                                            try {
                                                $image_sql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC LIMIT 1";
                                                $image_stmt = $conn->prepare($image_sql);
                                                if ($image_stmt) {
                                                    $image_stmt->bind_param('i', $product['id']);
                                                    $image_stmt->execute();
                                                    $image_result = $image_stmt->get_result();
                                                    if ($image_result && $image_result->num_rows > 0) {
                                                        $image_path = $image_result->fetch_assoc()['image_path'];
                                                    } elseif (!empty($product['image'])) {
                                                        $image_path = $product['image'];
                                                    } else {
                                                        $image_path = 'images/placeholder.jpg';
                                                    }
                                                } else {
                                                    $image_path = !empty($product['image']) ? $product['image'] : 'images/placeholder.jpg';
                                                }
                                            } catch (Exception $e) {
                                                $image_path = !empty($product['image']) ? $product['image'] : 'images/placeholder.jpg';
                                            }
                                            ?>
                                            <img src="../<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                                            <?php if(isset($product['is_featured']) && $product['is_featured']): ?>
                                                <i class="fas fa-star" style="color: #ff9800; margin-right: 5px;" title="Featured Ad"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($product['name'] ?? ''); ?>
                                        </div>
                                        
                                        <!-- Table Product Badges -->
                                        <?php 
                                        $badges_data = trim($product['badges'] ?? '');
                                        if(!empty($badges_data)): 
                                            $badges_list = explode(',', $badges_data);
                                            ?>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px;">
                                                <?php foreach($badges_list as $b): 
                                                    $b_clean = strtolower(trim($b));
                                                    if(empty($b_clean)) continue;
                                                    
                                                    $svg_file = '';
                                                    if($b_clean == 'hot premium') $svg_file = 'hot.svg';
                                                    elseif($b_clean == 'high demand') $svg_file = 'high_demand.svg';
                                                    elseif($b_clean == 'recommended') $svg_file = 'recommended.svg';
                                                    elseif($b_clean == 'popular') $svg_file = 'popular.svg';
                                                    
                                                    if(!empty($svg_file)):
                                                    ?>
                                                    <img src="../svg-icon/<?php echo $svg_file; ?>" alt="<?php echo htmlspecialchars($b); ?>" style="height: 18px; width: auto; display: block;">
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $product['status'] == 1 ? 'active' : 'hidden'; ?>" 
                                              style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; 
                                                     background: <?php echo $product['status'] == 1 ? '#dcfce7' : '#fee2e2'; ?>; 
                                                     color: <?php echo $product['status'] == 1 ? '#166534' : '#991b1b'; ?>;">
                                            <?php echo $product['status'] == 1 ? 'ACTIVE' : 'HIDDEN'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <i class="fas fa-star <?php echo $product['is_featured'] ? 'featured-active' : 'featured-inactive'; ?>" 
                                           style="font-size: 18px; cursor: pointer; color: <?php echo $product['is_featured'] ? '#fbbf24' : '#e2e8f0'; ?>"
                                           onclick="toggleFeatured(<?php echo $product['id']; ?>, this)"
                                           title="<?php echo $product['is_featured'] ? 'Remove from Featured' : 'Mark as Featured'; ?>"></i>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="btn-status-toggle" 
                                               style="color: <?php echo $product['status'] == 1 ? '#ef4444' : '#22c55e'; ?>"
                                               onclick="toggleStatus(<?php echo $product['id']; ?>, this)"
                                               title="<?php echo $product['status'] == 1 ? 'Hide Ad' : 'Show Ad'; ?>">
                                                <i class="fas <?php echo $product['status'] == 1 ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                            </a>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></a>
                                            <a href="<?php echo getProductUrl($product['id'], $product['name']); ?>" class="btn-view" title="View"><i class="fas fa-eye"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="margin: 20px 0; display: flex; justify-content: center; gap: 8px;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="product_management.php?page=<?php echo $i; ?><?php echo $category_filter > 0 ? '&category='.$category_filter : ''; ?>" 
                               style="padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; text-decoration: none; font-weight: 600;
                                      background: <?php echo $page == $i ? '#6366f1' : '#fff'; ?>; color: <?php echo $page == $i ? '#fff' : '#64748b'; ?>;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

            
        </main>
        
    <script>
    $(document).ready(function() {
        // Fancy Popup Notification Function
        function showFancyPopup(message, type = 'success') {
            const popup = document.createElement('div');
            popup.className = `fancy-popup ${type}`;
            
            const icon = document.createElement('i');
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            const messageSpan = document.createElement('span');
            messageSpan.className = 'message';
            messageSpan.textContent = message;
            
            const progressBar = document.createElement('div');
            progressBar.className = 'progress-bar';
            
            popup.appendChild(icon);
            popup.appendChild(messageSpan);
            popup.appendChild(progressBar);
            
            document.body.appendChild(popup);
            
            // Trigger animation
            setTimeout(() => popup.classList.add('show'), 10);
            
            // Remove popup after 3 seconds
            setTimeout(() => {
                popup.classList.remove('show');
                setTimeout(() => popup.remove(), 300);
            }, 3000);
        }

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
        
        // View state is now handled by the new logic at the bottom of the script
        // Handle table scroll indicator visibility
        function updateScrollIndicator() {
            if (window.innerWidth < 992 && $('.product-table').is(':visible')) {
                $('.table-scroll-indicator').show();
                
                // Check if table is wider than container
                var tableWidth = $('.product-table').width();
                var containerWidth = $('.product-table-wrapper').width();
                
                if (tableWidth > containerWidth) {
                    $('.table-scroll-indicator').show();
                    // Add shadow to indicate scrollable area
                    $('.table-scroll-container').addClass('scrollable');
                } else {
                    $('.table-scroll-indicator').hide();
                    $('.table-scroll-container').removeClass('scrollable');
                }
            } else {
                $('.table-scroll-indicator').hide();
                $('.table-scroll-container').removeClass('scrollable');
            }
        }
        
        // Update scroll indicator on load and resize
        updateScrollIndicator();
        $(window).on('resize', function() {
            updateScrollIndicator();
        });
        
        // Add touch swipe hint for mobile users
        $('.product-table-wrapper').on('touchstart', function() {
            $('.table-scroll-indicator').fadeOut();
        });
        
        // Show scroll position indicator
        $('.product-table-wrapper').on('scroll', function() {
            var scrollLeft = $(this).scrollLeft();
            var maxScrollLeft = $(this)[0].scrollWidth - $(this).width();
            
            if (scrollLeft > 0) {
                $('.table-scroll-container').addClass('scrolled-right');
            } else {
                $('.table-scroll-container').removeClass('scrolled-right');
            }
            
            if (scrollLeft >= maxScrollLeft - 5) {
                $('.table-scroll-container').removeClass('scrolled-right');
                $('.table-scroll-container').addClass('scrolled-end');
            } else {
                $('.table-scroll-container').removeClass('scrolled-end');
            }
        });
        
        // View toggle click handlers are now managed below via grid-view and list-view
        // Select all checkboxes
        $('#select-all').on('change', function() {
            $('.product-checkbox').prop('checked', $(this).prop('checked'));
            updateBulkActions();
        });
        
        // Individual checkbox change
        $(document).on('change', '.product-checkbox', function() {
            updateBulkActions();
            // If any checkbox is unchecked, uncheck the "select all" checkbox
            if (!$(this).prop('checked')) {
                $('#select-all').prop('checked', false);
            }
            // If all checkboxes are checked, check the "select all" checkbox
            else if ($('.product-checkbox:checked').length === $('.product-checkbox').length) {
                $('#select-all').prop('checked', true);
            }
        });
        
        // Update bulk actions visibility
        function updateBulkActions() {
            var selectedCount = $('.product-checkbox:checked').length;
            if (selectedCount > 0) {
                $('.bulk-actions').show();
                $('#selected-count').text(selectedCount + ' item(s) selected');
            } else {
                $('.bulk-actions').hide();
            }
        }
        
        // Apply bulk actions
        $('#apply-bulk').on('click', function() {
            var action = $('#bulk-action').val();
            var selectedIds = [];
            
            $('.product-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (action && selectedIds.length > 0) {
                if (action === 'delete') {
                    if (confirm('Are you sure you want to delete ' + selectedIds.length + ' selected product(s)?')) {
                        // Implement AJAX to handle bulk delete
                        alert('Bulk delete feature will be implemented here');
                        // After successful delete, refresh the page or update the table
                    }
                } else if (action === 'in-stock' || action === 'out-of-stock') {
                    // Implement AJAX to update stock status
                    alert('Bulk stock update feature will be implemented here');
                    // After successful update, refresh the page or update the table
                } else if (action === 'update-quantity') {
                    $('#bulk-quantity-modal').show();
                }
            } else {
                alert('Please select both an action and at least one product');
            }
        });
        
        // Handle bulk quantity update modal
        $('.close-modal, #cancel-quantity').on('click', function() {
            $('#bulk-quantity-modal').hide();
        });

        $('#confirm-quantity').on('click', function() {
            var selectedIds = [];
            $('.product-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            var newQuantity = parseInt($('#new-quantity').val());
            var action = $('#quantity-action').val();
            
            if (isNaN(newQuantity) || newQuantity < 0) {
                alert('Please enter a valid quantity');
                return;
            }
            
            // Show loading state
            $('#confirm-quantity').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            
            // Send AJAX request to update quantities
            $.ajax({
                url: 'update_bulk_quantity.php',
                type: 'POST',
                data: {
                    product_ids: selectedIds,
                    new_quantity: newQuantity,
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        // Refresh the page to show updated quantities
                        location.reload();
                    } else {
                        alert('Error updating quantities: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error updating quantities. Please try again.');
                    console.error("AJAX Error:", status, error);
                },
                complete: function() {
                    $('#confirm-quantity').prop('disabled', false).text('Update Quantities');
                    $('#bulk-quantity-modal').hide();
                }
            });
        });
        
        // View toggling
        $('#grid-view').on('click', function() {
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            $('.admin-content').removeClass('view-mode-list');
            localStorage.setItem('admin_product_view', 'grid');
            filterProducts(); // Sync view with current filters
        });

        $('#list-view').on('click', function() {
            $('.view-btn').removeClass('active');
            $(this).addClass('active');
            $('.admin-content').addClass('view-mode-list');
            localStorage.setItem('admin_product_view', 'list');
            filterProducts(); // Sync view with current filters
        });

        // Load saved view preference
        const savedView = localStorage.getItem('admin_product_view');
        if (savedView === 'list') {
            $('#list-view').trigger('click');
        }

        // Product filtering functionality 
        $('#sort-filter').on('change', function() {
            filterProducts();
        });
        
        $('#search-btn').on('click', function(e) {
            e.preventDefault();
            filterProducts();
        });
        
        $('#product-search').on('keypress', function(e) {
            if (e.which === 13) {
                filterProducts();
            }
        });
        
        function filterProducts() {
            const categoryId = 0; // Removed from UI
            const status = ''; // Removed from UI
            const sort = $('#sort-filter').val();
            const searchQuery = $('#product-search').val().trim();
            const viewType = $('#list-view').hasClass('active') ? 'list' : 'grid';
            
            // Show loading state
            if (viewType === 'grid') {
                $('.product-grid').html('<div style="grid-column: 1 / -1; text-align: center; padding: 50px 0;"><i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #3f51b5;"></i><p style="margin-top: 20px;">Loading products...</p></div>');
            } else {
                $('.product-table tbody').html('<tr><td colspan="7" style="text-align: center; padding: 50px 0;"><i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #3f51b5;"></i><p style="margin-top: 20px;">Loading products...</p></td></tr>');
            }
            
            // Send AJAX request to filter products
            $.ajax({
                url: 'filter_products.php',
                type: 'GET',
                data: {
                    category: categoryId,
                    status: status,
                    sort: sort,
                    search: searchQuery,
                    view: viewType
                },
                success: function(response) {
                    if (viewType === 'grid') {
                        $('.product-grid').html(response);
                    } else {
                        // For list view, we need to handle the response differently
                        // This assumes filter_products.php will return the table rows when view=list
                        $('.product-table tbody').html(response);
                        updateBulkActions();
                    }
                    updateScrollIndicator();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    if (viewType === 'grid') {
                        $('.product-grid').html('<div style="grid-column: 1 / -1; text-align: center; padding: 50px 0;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #f44336;"></i><p style="margin-top: 20px;">Error loading products. Please try again.</p></div>');
                    } else {
                        $('.product-table tbody').html('<tr><td colspan="7" style="text-align: center; padding: 30px; color: #f44336;"><i class="fas fa-exclamation-triangle" style="font-size: 36px; margin-bottom: 15px;"></i><p>Error loading products. Please try again.</p></td></tr>');
                    }
                    updateScrollIndicator();
                }
            });
        }
        
        // Sort table columns
        $('.product-table th').on('click', function() {
            if ($(this).find('i.fa-sort').length > 0) {
                // Get column name
                var columnText = $(this).text().trim().toLowerCase();
                var columnName = '';
                
                // Map column text to sort parameter
                if (columnText.includes('product name')) {
                    columnName = 'name';
                } else if (columnText.includes('price')) {
                    columnName = 'price';
                } else if (columnText.includes('stock')) {
                    columnName = 'stock';
                }
                
                if (columnName) {
                    // Check current sort direction
                    var currentDir = 'asc';
                    if ($(this).hasClass('sort-asc')) {
                        currentDir = 'desc';
                        $(this).removeClass('sort-asc').addClass('sort-desc');
                        $(this).find('i').removeClass('fa-sort fa-sort-up').addClass('fa-sort-down');
                    } else {
                        $(this).removeClass('sort-desc').addClass('sort-asc');
                        $(this).find('i').removeClass('fa-sort fa-sort-down').addClass('fa-sort-up');
                    }
                    
                    // Update sort filter select
                    if (columnName === 'name' && currentDir === 'asc') {
                        $('#sort-filter').val('name_asc');
                    } else if (columnName === 'name' && currentDir === 'desc') {
                        $('#sort-filter').val('name_desc');
                    } else if (columnName === 'price' && currentDir === 'asc') {
                        $('#sort-filter').val('price_low');
                    } else if (columnName === 'price' && currentDir === 'desc') {
                        $('#sort-filter').val('price_high');
                    } else if (columnName === 'stock') {
                        // Custom stock sorting
                    }
                    
                    // Apply filter
                    filterProducts();
                }
            }
        });
        
        // Function to reset all filters
        window.resetFilters = function() {
            $('#sort-filter').val('newest');
            $('#product-search').val('');
            filterProducts();
        };
        
        // Dropdown functionality
        $(document).on('click', '.dropdown-toggle', function(e) {
            e.preventDefault();
            $(this).siblings('.dropdown-menu').toggleClass('show');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });

        // Fancy Popup Fallback
        window.showFancyPopup = function(message, type = 'success') {
            if (typeof showNotification === 'function') {
                showNotification(message, type);
            } else {
                alert(message);
            }
        };

        // Update delete product function
        window.deleteProduct = function(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                $.ajax({
                    url: 'delete_product.php',
                    method: 'POST',
                    data: { id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showFancyPopup('Product deleted successfully');
                            // If it's a grid card, remove it. If it's a table row, remove it.
                            $(`[data-id="${productId}"], .product-card:has(a[onclick*="${productId}"])`).fadeOut(300, function() {
                                $(this).remove();
                            });
                            // If table was empty, reload or show message
                            if ($('.product-card').length === 0 && $('.product-table tbody tr').length === 0) {
                                location.reload();
                            }
                        } else {
                            showFancyPopup('Failed to delete product: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showFancyPopup('An error occurred while deleting the product', 'error');
                    }
                });
            }
        };

        // Update edit product form
        $('#edit-product-form').on('submit', function(e) {
            e.preventDefault();
            // ... existing form submission code ...
            if (response.success) {
                showFancyPopup('Product updated successfully');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showFancyPopup('Failed to update product: ' + response.message, 'error');
            }
        });
        

        // Touch swipe functionality for medium devices
        if (window.innerWidth <= 992) {
            // Add swipe panels to each row
            $('.product-table tbody tr').each(function() {
                var row = $(this);
                var productId = row.data('product-id');
                
                // Create details panel
                var detailsPanel = $('<div class="product-details-panel"></div>');
                
                // Add action buttons
                detailsPanel.append('<div class="action-btn view" title="View Details"><i class="fas fa-eye"></i></div>');
                detailsPanel.append('<div class="action-btn edit" title="Edit Product"><i class="fas fa-edit"></i></div>');
                detailsPanel.append('<div class="action-btn delete" title="Delete Product"><i class="fas fa-trash"></i></div>');
                
                // Append panel to row
                row.append(detailsPanel);
                
                // Add product ID to row for reference
                row.attr('data-product-id', productId);
                
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
                    window.location.href = 'product_details.php?id=' + productId;
                });
                
                detailsPanel.find('.action-btn.edit').on('click', function() {
                    window.location.href = 'edit_product.php?id=' + productId;
                });
                
                detailsPanel.find('.action-btn.delete').on('click', function() {
                    if (confirm('Are you sure you want to delete this product?')) {
                        window.location.href = 'product_management.php?delete=' + productId;
                    }
                });
            });
        }

        // Initialize QR code view state from localStorage
        let showQRCode = localStorage.getItem('showQRCode') === 'true';
        
        // Set initial state
        if (showQRCode) {
            $('#qr-toggle').addClass('active');
            $('.product-img').hide();
            $('.qr-code-img').show();
        }
        
        // QR code toggle functionality
        $('#qr-toggle').on('click', function() {
            showQRCode = !showQRCode;
            $(this).toggleClass('active');
            
            // Save state to localStorage
            localStorage.setItem('showQRCode', showQRCode);
            
            if (showQRCode) {
                $('.product-img').hide();
                $('.qr-code-img').show();
            } else {
                $('.product-img').show();
                $('.qr-code-img').hide();
            }
        });

        // Update the view toggle to maintain QR code state
        $('#grid-view, #list-view').on('click', function() {
            if (showQRCode) {
                setTimeout(() => {
                    $('.product-img').hide();
                    $('.qr-code-img').show();
                }, 100);
            }
        });

        // Handle filter changes to maintain QR code state
        $('#category-filter, #status-filter, #sort-filter').on('change', function() {
            if (showQRCode) {
                setTimeout(() => {
                    $('.product-img').hide();
                    $('.qr-code-img').show();
                }, 100);
            }
        });

        // Handle search to maintain QR code state
        $('#search-btn').on('click', function() {
            if (showQRCode) {
                setTimeout(() => {
                    $('.product-img').hide();
                    $('.qr-code-img').show();
                }, 100);
            }
        });

        // Handle bulk actions to maintain QR code state
        $('#apply-bulk').on('click', function() {
            if (showQRCode) {
                setTimeout(() => {
                    $('.product-img').hide();
                    $('.qr-code-img').show();
                }, 100);
            }
        });
        // Toggle Featured Ad
        window.toggleFeatured = function(productId, element) {
            $.ajax({
                url: 'toggle_featured.php',
                type: 'POST',
                data: { id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const isFeatured = response.is_featured;
                        $(element).css('color', isFeatured ? '#fbbf24' : '#e2e8f0');
                        $(element).attr('title', isFeatured ? 'Remove from Featured' : 'Mark as Featured');
                        showFancyPopup(isFeatured ? 'Ad marked as Featured' : 'Ad removed from Featured');
                    } else {
                        showFancyPopup('Error: ' + response.message, 'error');
                    }
                },
                error: function() {
                    showFancyPopup('An error occurred. Please try again.', 'error');
                }
            });
        };

        // Toggle Ad Status (Active/Hide)
        window.toggleStatus = function(productId, element) {
            $.ajax({
                url: 'toggle_status.php',
                type: 'POST',
                data: { id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const isActive = response.status == 1;
                        const row = $(element).closest('tr');
                        const badge = row.find('.status-badge');
                        
                        // Update badge
                        badge.text(isActive ? 'ACTIVE' : 'HIDDEN');
                        badge.css({
                            'background': isActive ? '#dcfce7' : '#fee2e2',
                            'color': isActive ? '#166534' : '#991b1b'
                        });
                        
                        // Update action button
                        $(element).css('color', isActive ? '#ef4444' : '#22c55e');
                        $(element).attr('title', isActive ? 'Hide Ad' : 'Show Ad');
                        $(element).find('i').attr('class', isActive ? 'fas fa-eye-slash' : 'fas fa-eye');
                        
                        showFancyPopup(isActive ? 'Ad is now Active' : 'Ad is now Hidden');
                    } else {
                        showFancyPopup('Error: ' + response.message, 'error');
                    }
                },
                error: function() {
                    showFancyPopup('An error occurred. Please try again.', 'error');
                }
            });
        };
    });

    // Add this after your existing JavaScript code
    function updateProductQuantity(productId, quantity) {
        fetch('update_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the quantity display in the product list
                const quantityElement = document.querySelector(`[data-product-id="${productId}"] .product-quantity`);
                if (quantityElement) {
                    quantityElement.textContent = data.new_quantity;
                }
                showNotification('Quantity updated successfully', 'success');
            } else {
                showNotification(data.message || 'Error updating quantity', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating quantity', 'error');
        });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    </script>
<?php renderAdminFooter(); ?>
 