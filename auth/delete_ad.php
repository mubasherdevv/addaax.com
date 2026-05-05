<?php
require_once 'session.php';
require_once 'db_connect.php';

// Require login
requireLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $is_admin = isAdmin();

    // Check ownership if not admin
    $check_sql = $is_admin ? "SELECT id FROM products WHERE id = ?" : "SELECT id FROM products WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($check_sql);
    if ($is_admin) {
        $stmt->bind_param("i", $ad_id);
    } else {
        $stmt->bind_param("ii", $ad_id, $user_id);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // Delete images from folder
        $img_res = $conn->query("SELECT image_path FROM product_images WHERE product_id = $ad_id");
        while ($row = $img_res->fetch_assoc()) {
            $path = '../' . $row['image_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Delete from DB
        $conn->query("DELETE FROM product_images WHERE product_id = $ad_id");
        $conn->query("DELETE FROM products WHERE id = $ad_id");

        $redirect = $is_admin ? "product_management.php" : "dashboard.php?tab=ads";
        header("Location: $redirect&msg=Ad deleted successfully");
        exit;
    }
}

header("Location: dashboard.php");
exit;
?>
