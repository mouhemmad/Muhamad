<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';

define('SITE_NAME', 'Kozhen Studio');
$_host = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
define('SITE_URL', 'https://' . $_host . '/');

define('TRIAL_DAYS', 30);

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Admin2025');

// إعدادات الباقات
$PLANS = [
    'trial' => ['price' => 0, 'days' => 30, 'max_products' => 10],
    'basic' => ['price' => 19.99, 'days' => 30, 'max_products' => 50],
    'pro' => ['price' => 39.99, 'days' => 30, 'max_products' => 999999]
];

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function getUser($user_id = null) {
    $db = Database::getConnection();
    $id = $user_id ?? $_SESSION['user_id'] ?? null;

    if (!$id) return null;

    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function checkSubscription($user_id) {
    $user = getUser($user_id);
    if (!$user) return false;

    $today = date('Y-m-d');

    if ($user['subscription_status'] == 'trial') {
        if ($today > $user['trial_end']) {
            return ['status' => 'expired', 'message' => 'انتهت الفترة التجريبية'];
        }
        $days_left = (strtotime($user['trial_end']) - strtotime($today)) / 86400;
        return ['status' => 'active', 'plan' => 'trial', 'days_left' => ceil($days_left)];
    }

    if ($user['subscription_status'] == 'basic' || $user['subscription_status'] == 'pro') {
        if ($today > $user['subscription_end']) {
            return ['status' => 'expired', 'message' => 'انتهى الاشتراك'];
        }
        $days_left = (strtotime($user['subscription_end']) - strtotime($today)) / 86400;
        return ['status' => 'active', 'plan' => $user['subscription_status'], 'days_left' => ceil($days_left)];
    }

    return ['status' => 'inactive'];
}
?>