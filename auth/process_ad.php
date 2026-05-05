<?php
ob_start();
session_start();

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

require_once 'db_connect.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?return_to=../post-ad.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $city_id = intval($_POST['city_id'] ?? 0);
    $province_id = intval($_POST['province_id'] ?? 0);
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');

    // Get city and province names
    $city_name = '';
    $province_name = '';
    
    if ($city_id > 0) {
        $city_res = $conn->query("SELECT name FROM cities WHERE id = $city_id");
        if ($city_res && $row = $city_res->fetch_assoc()) {
            $city_name = $row['name'];
        }
    }

    if ($province_id > 0) {
        $state_res = $conn->query("SELECT name FROM states WHERE id = $province_id");
        if ($state_res && $row = $state_res->fetch_assoc()) {
            $province_name = $row['name'];
        }
    }

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    // Insert into products
    $sql = "INSERT INTO products (name, slug, description, price, seller_id, city, province, phone, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database Error");
    }

    $stmt->bind_param("sssdisss", $title, $slug, $description, $price, $user_id, $city_name, $province_name, $phone);
    
    if ($stmt->execute()) {
        $product_id = $conn->insert_id;
        
        // Handle images
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            require_once '../includes/image_utils.php';
            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $first_image = '';
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (empty($tmp_name)) continue;

                $filename = $_FILES['images']['name'][$key];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($ext, $allowed)) {
                    $unique_name = uniqid() . '_' . time() . '.' . $ext;
                    $target_path = $upload_dir . $unique_name;

                    // Use compression instead of move_uploaded_file
                    $saved_filename = compressImage($tmp_name, $target_path, 75);
                    
                    if ($saved_filename) {
                        $db_path = 'uploads/products/' . $saved_filename;
                        if (empty($first_image)) $first_image = $db_path;

                        $is_primary = ($key === 0) ? 1 : 0;
                        $img_sql = "INSERT INTO product_images (product_id, image_path, is_primary, filename, created_at) 
                                    VALUES (?, ?, ?, ?, NOW())";
                        $img_stmt = $conn->prepare($img_sql);
                        $img_stmt->bind_param("isis", $product_id, $db_path, $is_primary, $filename);
                        $img_stmt->execute();
                    }
                }
            }

            // Update main product image
            if (!empty($first_image)) {
                $conn->query("UPDATE products SET image = '$first_image' WHERE id = $product_id");
            }
        }

        ob_end_clean();
        header("Location: dashboard.php?tab=ads&msg=Ad posted successfully! Admin will review it.");
        exit;
    } else {
        die("Submission failed");
    }
}
?>
