<?php
require_once 'config/config.php';
if (!isLoggedIn()) { header("Location: /login"); exit(); }

$name  = trim($_POST['name']  ?? '');
$icon  = trim($_POST['icon']  ?? 'utensils');
$color = trim($_POST['color'] ?? '#8b5cf6');
$uid   = $_SESSION['user_id'];

if ($name) {
    $slug = preg_replace('/\s+/','_', trim(preg_replace('/[^\p{L}\p{N}_]/u','', $name)));
    $slug = $slug ?: 'cat_'.time();
    addCategory($uid, $slug, $name, $icon, $color);
}
header("Location: /dashboard#categories");
exit();
