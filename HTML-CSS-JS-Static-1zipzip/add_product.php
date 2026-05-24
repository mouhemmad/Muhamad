<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    http_response_code(401); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id     = $_SESSION['user_id'];
    $name        = $_POST['name'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $category    = $_POST['category'];
    $image       = $_FILES['image'] ?? null;

    if (addProduct($user_id, $name, $description, $price, $category, $image)) {
        header("Location: /dashboard"); exit();
    } else {
        echo "حدث خطأ";
    }
}
