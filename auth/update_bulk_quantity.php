<?php
require_once '../config/database.php';
require_once 'session.php';
require_once 'db_connect.php';

if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];

if (empty($product_ids)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No products selected']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Stock management has been disabled']);
?>
