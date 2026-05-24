<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $theme = $_POST['theme'];
    updateUserTheme($_SESSION['user_id'], $theme);
    echo json_encode(['success' => true]);
}
?>