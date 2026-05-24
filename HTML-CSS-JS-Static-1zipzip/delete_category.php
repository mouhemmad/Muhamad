<?php
require_once 'config/config.php';
if (!isLoggedIn()) { echo json_encode(['success'=>false]); exit(); }
header('Content-Type: application/json');
$id  = (int)($_POST['id'] ?? 0);
$uid = $_SESSION['user_id'];
if ($id) {
    $ok = deleteCategory($id, $uid);
    echo json_encode(['success'=>(bool)$ok]);
} else {
    echo json_encode(['success'=>false]);
}
?>
