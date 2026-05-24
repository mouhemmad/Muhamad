<?php
require_once 'config/config.php';
if (!isLoggedIn()) { header("Location: /login"); exit(); }
header('Content-Type: application/json');

$uid   = $_SESSION['user_id'];
$plan  = trim($_POST['plan']  ?? 'basic');
$months= (int)($_POST['duration_months'] ?? 1);
$phone = trim($_POST['phone'] ?? '');
$notes = trim($_POST['notes'] ?? '');

$allowed_plans = ['basic','pro'];
$allowed_months = [3,6,12];
if (!in_array($plan,$allowed_plans)) $plan = 'basic';
if (!in_array($months,$allowed_months)) $months = 3;

$db = Database::getConnection();
$existing = $db->prepare("SELECT id FROM subscription_requests WHERE user_id=? AND status='pending'");
$existing->execute([$uid]);
if ($existing->fetch()) {
    echo json_encode(['success'=>true,'already'=>true]);
    exit();
}

$stmt = $db->prepare("INSERT INTO subscription_requests (user_id,plan,duration_months,phone,notes) VALUES (?,?,?,?,?)");
$stmt->execute([$uid,$plan,$months,$phone,$notes]);
echo json_encode(['success'=>true,'already'=>false]);
