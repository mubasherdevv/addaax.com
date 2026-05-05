<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['state_id'])) {
    $state_id = intval($_GET['state_id']);
    $stmt = $conn->prepare("SELECT id, name FROM cities WHERE state_id = ? AND status = 1 ORDER BY name ASC");
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cities = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($cities);
} else {
    echo json_encode([]);
}
?>
