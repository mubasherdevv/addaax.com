<?php
// Enable error handling as exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Prevent PHP from outputting errors directly
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Buffer output to prevent any unwanted output before headers
ob_start();

try {
    // Include database connection and session
    require_once 'session.php';
    require_once 'db_connect.php';

    // Initialize response array
    $response = ['success' => false, 'message' => '', 'categories' => []];

    // Check if user is logged in and is an admin
    if (!function_exists('isLoggedIn') || !function_exists('isAdmin')) {
        throw new Exception('Authentication functions not available');
    }

    if (!isLoggedIn()) {
        throw new Exception('User not logged in');
    }

    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('User is not an admin');
    }

    // Check if categories table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($tableCheck->num_rows == 0) {
        throw new Exception('Categories table does not exist. Please run create_categories_table.php first.');
    }
    
    // Handle single category request for edit
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $category_id = intval($_GET['id']);
        
        $sql = "SELECT c.*, p.id as parent_id
                FROM categories c 
                LEFT JOIN categories p ON c.parent_id = p.id
                WHERE c.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['category'] = $result->fetch_assoc();
        } else {
            $response['message'] = 'Category not found';
        }
        
        $stmt->close();
    } 
    // Handle parent dropdown request (exclude subcategories, show only top-level)
    else if (isset($_GET['parent_dropdown'])) {
        $sql = "SELECT id, name FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY name";
        $result = $conn->query($sql);
        
        if ($result) {
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            
            $response['success'] = true;
            $response['categories'] = $categories;
        } else {
            throw new Exception('Error fetching categories: ' . $conn->error);
        }
    } 
    // Default: get all categories
    else {
        try {
            // First check if products table exists to avoid errors
            $productsTableCheck = $conn->query("SHOW TABLES LIKE 'products'");
            $productsTableExists = ($productsTableCheck && $productsTableCheck->num_rows > 0);
            
            if ($productsTableExists) {
                $sql = "SELECT c.*, 
                        p.name as parent_name,
                        (SELECT COUNT(*) FROM products prod WHERE prod.category_id = c.id) as product_count
                        FROM categories c
                        LEFT JOIN categories p ON c.parent_id = p.id
                        ORDER BY c.parent_id IS NULL DESC, c.parent_id ASC, c.name ASC";
            } else {
                // Simpler query that doesn't reference the products table
                $sql = "SELECT c.*, 
                        p.name as parent_name,
                        0 as product_count
                        FROM categories c
                        LEFT JOIN categories p ON c.parent_id = p.id
                        ORDER BY c.parent_id IS NULL DESC, c.parent_id ASC, c.name ASC";
            }
            
            $result = $conn->query($sql);
            
            if ($result) {
                $categories = [];
                while ($row = $result->fetch_assoc()) {
                    $categories[] = $row;
                }
                
                $response['success'] = true;
                $response['categories'] = $categories;
            } else {
                throw new Exception('Error fetching categories: ' . $conn->error);
            }
        } catch (Exception $e) {
            // If there's an error with the products table, try again with a simpler query
            try {
                $sql = "SELECT c.*, 
                        p.name as parent_name,
                        0 as product_count
                        FROM categories c
                        LEFT JOIN categories p ON c.parent_id = p.id
                        ORDER BY c.parent_id IS NULL DESC, c.parent_id ASC, c.name ASC";
                
                $result = $conn->query($sql);
                
                if ($result) {
                    $categories = [];
                    while ($row = $result->fetch_assoc()) {
                        $categories[] = $row;
                    }
                    
                    $response['success'] = true;
                    $response['categories'] = $categories;
                    $response['message'] = 'Products table not available, showing categories only';
                } else {
                    throw new Exception('Error fetching categories: ' . $conn->error);
                }
            } catch (Exception $innerException) {
                throw new Exception('Failed to fetch categories: ' . $innerException->getMessage());
            }
        }
    }

    // Clear any buffered output
    ob_end_clean();

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    // Clear any buffered output
    ob_end_clean();
    
    // Return error as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'categories' => []
    ]);
}
?> 