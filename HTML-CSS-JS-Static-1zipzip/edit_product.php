<?php
require_once 'config/config.php';
if (!isLoggedIn()) { echo json_encode(['success'=>false,'msg'=>'غير مصرح']); exit(); }

header('Content-Type: application/json');

$id       = (int)($_POST['id'] ?? 0);
$name     = trim($_POST['name'] ?? '');
$desc     = trim($_POST['description'] ?? '');
$price    = (float)($_POST['price'] ?? 0);
$category = trim($_POST['category'] ?? '');
$uid      = $_SESSION['user_id'];

if (!$id || !$name || $price <= 0) {
    echo json_encode(['success'=>false,'msg'=>'بيانات ناقصة']); exit();
}

$image = $_FILES['image'] ?? null;
$ok = updateProduct($id, $uid, [
    'name'        => $name,
    'description' => $desc,
    'price'       => $price,
    'category'    => $category,
], $image);

if ($ok) {
    // Return updated image_url if changed
    $prod = getProduct($id, $uid);
    echo json_encode(['success'=>true,'image_url'=>$prod['image_url']??'']);
} else {
    echo json_encode(['success'=>false,'msg'=>'فشل التحديث']);
}
?>
