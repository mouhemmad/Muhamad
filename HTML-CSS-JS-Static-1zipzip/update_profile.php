<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success'=>false,'msg'=>'غير مخول']);
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

$social_instagram = trim($_POST['social_instagram'] ?? '');
$social_tiktok    = trim($_POST['social_tiktok']    ?? '');
$social_snapchat  = trim($_POST['social_snapchat']  ?? '');
$social_whatsapp  = trim($_POST['social_whatsapp']  ?? '');
$social_facebook  = trim($_POST['social_facebook']  ?? '');
$social_location  = trim($_POST['social_location']  ?? '');

$video_url = null;

if (!empty($_FILES['video']['tmp_name'])) {
    $file     = $_FILES['video'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['mp4','mov','webm'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success'=>false,'msg'=>'صيغة الفيديو غير مدعومة. استخدم MP4 أو MOV أو WebM']);
        exit();
    }

    if ($file['size'] > 100 * 1024 * 1024) {
        echo json_encode(['success'=>false,'msg'=>'حجم الفيديو كبير جداً (الحد الأقصى 100MB)']);
        exit();
    }

    $filename  = 'v_' . $user_id . '_' . uniqid() . '.' . $ext;
    $dest      = __DIR__ . '/uploads/' . $filename;

    $existing = $db->prepare("SELECT video_url FROM users WHERE id=?");
    $existing->execute([$user_id]);
    $old = $existing->fetchColumn();
    if ($old && file_exists(__DIR__ . '/' . $old)) {
        @unlink(__DIR__ . '/' . $old);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success'=>false,'msg'=>'فشل رفع الفيديو']);
        exit();
    }

    $video_url = 'uploads/' . $filename;
}

// Handle hero image upload
$hero_image = null;
if (!empty($_FILES['hero_image']['tmp_name'])) {
    $hfile    = $_FILES['hero_image'];
    $hext     = strtolower(pathinfo($hfile['name'], PATHINFO_EXTENSION));
    $hallowed = ['jpg','jpeg','png','webp'];
    if (!in_array($hext, $hallowed)) {
        echo json_encode(['success'=>false,'msg'=>'صيغة الصورة غير مدعومة']);
        exit();
    }
    if ($hfile['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success'=>false,'msg'=>'حجم الصورة كبير جداً (الحد 10MB)']);
        exit();
    }
    $hname = 'hero_' . $user_id . '_' . uniqid() . '.' . $hext;
    $hdest = __DIR__ . '/uploads/' . $hname;
    $old_hero = $db->prepare("SELECT hero_image FROM users WHERE id=?");
    $old_hero->execute([$user_id]);
    $old_h = $old_hero->fetchColumn();
    if ($old_h && file_exists(__DIR__ . '/' . $old_h)) @unlink(__DIR__ . '/' . $old_h);
    if (move_uploaded_file($hfile['tmp_name'], $hdest)) {
        $hero_image = 'uploads/' . $hname;
    }
}

$extra = '';
if ($video_url !== null) $extra .= ', video_url=?';
if ($hero_image !== null) $extra .= ', hero_image=?';

$stmt = $db->prepare("UPDATE users SET
    social_instagram=?,
    social_tiktok=?,
    social_snapchat=?,
    social_whatsapp=?,
    social_facebook=?,
    social_location=?
    $extra
    WHERE id=?");

$params = [$social_instagram,$social_tiktok,$social_snapchat,$social_whatsapp,$social_facebook,$social_location];
if ($video_url !== null) $params[] = $video_url;
if ($hero_image !== null) $params[] = $hero_image;
$params[] = $user_id;
$stmt->execute($params);

echo json_encode(['success'=>true, 'video_url'=>$video_url, 'hero_image'=>$hero_image]);
