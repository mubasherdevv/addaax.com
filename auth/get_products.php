<?php
// Enable error reporting for debugging but don't display errors to users
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set content type to JSON
header('Content-Type: application/json');

try {
    require_once 'session.php';
    require_once 'db_connect.php';

    // Check if user is logged in and is an admin
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        $response = [
            'success' => false,
            'message' => 'Unauthorized access'
        ];
        echo json_encode($response);
        exit;
    }

    // Get specific product by ID
    if (isset($_GET['id'])) {
        $product_id = intval($_GET['id']);
        
        $sql = 'SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?';
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response = [
                'success' => false,
                'message' => 'Product not found'
            ];
        } else {
            $product = $result->fetch_assoc();
            $response = [
                'success' => true,
                'product' => $product
            ];
        }
        
        echo json_encode($response);
        exit;
    }

    // Get all products with pagination and filtering
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;

    // Base query
    $query = 'FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1';
    $count_query = 'SELECT COUNT(*) as total FROM products p WHERE 1=1';
    $params = [];
    $types = '';

    // Apply category filter
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category_id = intval($_GET['category']);
        $query .= ' AND p.category_id = ?';
        $count_query .= ' AND p.category_id = ?';
        $params[] = $category_id;
        $types .= 'i';
    }

    // Apply search filter
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $query .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
        $count_query .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }

    // Complete the query
    $query = 'SELECT p.*, c.name as category_name ' . $query . ' ORDER BY p.id DESC LIMIT ? OFFSET ?';
    $params[] = $items_per_page;
    $params[] = $offset;
    $types .= 'ii';

    // Get total count for pagination
    $count_stmt = $conn->prepare($count_query);
    if (!$count_stmt) {
        throw new Exception("Count prepare statement failed: " . $conn->error);
    }
    
    if (!empty($types) && !empty($params)) {
        // Create a copy of params array without the limit and offset parameters
        $count_params = array_slice($params, 0, -2);
        $count_types = substr($types, 0, -2); // Remove 'ii' for the LIMIT parameters
        
        if (!empty($count_params)) {
            if (!$count_stmt->bind_param($count_types, ...$count_params)) {
                throw new Exception("Count bind_param failed: " . $count_stmt->error);
            }
        }
    }
    
    if (!$count_stmt->execute()) {
        throw new Exception("Count execute failed: " . $count_stmt->error);
    }
    
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_items = $count_row['total'];
    $total_pages = ceil($total_items / $items_per_page);

    // Execute the main query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Main query prepare statement failed: " . $conn->error);
    }
    
    if (!empty($types) && !empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception("Main query bind_param failed: " . $stmt->error);
        }
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Main query execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Prepare pagination data
    $pagination = [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'items_per_page' => $items_per_page,
        'total_items' => $total_items
    ];

    if ($page > 1) {
        $pagination['prev_page'] = $page - 1;
    }

    if ($page < $total_pages) {
        $pagination['next_page'] = $page + 1;
    }

    // Return response
    $response = [
        'success' => true,
        'products' => $products,
        'pagination' => $pagination
    ];

    echo json_encode($response);

    // Close connection
    $stmt->close();
    $count_stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log error
    error_log("Product load error: " . $e->getMessage());
    
    // Return error response
    $response = [
        'success' => false,
        'message' => 'Error loading products: ' . $e->getMessage()
    ];
    
    echo json_encode($response);
    
    // Close connection if exists
    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($count_stmt) && $count_stmt) $count_stmt->close();
    if (isset($conn) && $conn) $conn->close();
}
?> 