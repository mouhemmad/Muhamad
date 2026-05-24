<?php
require_once 'config/config.php';
header('Content-Type: application/json');

if (!($_SESSION['admin_logged_in'] ?? false)) {
    echo json_encode(['error' => 'unauthorized']); exit();
}

$action = $_POST['action'] ?? '';
$uid    = (int)($_POST['user_id'] ?? 0);
$db     = Database::getConnection();

if ($action === 'set_plan') {
    $plan     = $_POST['plan'] ?? 'trial';
    $duration = (int)($_POST['duration'] ?? 30);
    if ($plan === 'disabled') {
        $db->prepare("UPDATE users SET subscription_status='disabled', subscription_end=NULL WHERE id=?")->execute([$uid]);
        echo json_encode(['success'=>true, 'plan'=>'disabled', 'end'=>'—']);
    } elseif ($plan === 'trial') {
        $end = date('Y-m-d', strtotime("+$duration days"));
        $db->prepare("UPDATE users SET subscription_status='trial', trial_end=?, subscription_end=NULL WHERE id=?")->execute([$end,$uid]);
        echo json_encode(['success'=>true, 'plan'=>'trial', 'end'=>$end]);
    } else {
        $end = date('Y-m-d', strtotime("+$duration days"));
        $db->prepare("UPDATE users SET subscription_status=?, subscription_end=? WHERE id=?")->execute([$plan,$end,$uid]);
        echo json_encode(['success'=>true, 'plan'=>$plan, 'end'=>$end]);
    }
    exit();
}

if ($action === 'delete_user') {
    $db->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
    echo json_encode(['success'=>true]);
    exit();
}

if ($action === 'approve_request') {
    $rid   = (int)($_POST['request_id'] ?? 0);
    $plan  = $_POST['plan'] ?? 'basic';
    $months= (int)($_POST['duration_months'] ?? 1);
    $days  = $months * 30;
    $end   = date('Y-m-d', strtotime("+$days days"));
    $db->prepare("UPDATE subscription_requests SET status='approved' WHERE id=?")->execute([$rid]);
    $db->prepare("UPDATE users SET subscription_status=?, subscription_end=? WHERE id=?")->execute([$plan, $end, $uid]);
    $db->prepare("INSERT INTO subscriptions (user_id,plan_name,start_date,end_date,amount) VALUES (?,?,?,?,0)")->execute([$uid,$plan,date('Y-m-d'),$end]);
    echo json_encode(['success'=>true,'end'=>$end,'plan'=>$plan]);
    exit();
}

if ($action === 'reject_request') {
    $rid = (int)($_POST['request_id'] ?? 0);
    $db->prepare("UPDATE subscription_requests SET status='rejected' WHERE id=?")->execute([$rid]);
    echo json_encode(['success'=>true]);
    exit();
}

echo json_encode(['error'=>'unknown action']);
