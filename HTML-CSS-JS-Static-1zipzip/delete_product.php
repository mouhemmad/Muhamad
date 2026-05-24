<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    if (deleteProduct($product_id, $user_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>