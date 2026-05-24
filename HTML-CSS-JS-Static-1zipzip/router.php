<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Serve video files with Range request support (needed for video playback)
$video_exts = ['mp4','mov','webm','ogg'];
if ($uri !== '/' && file_exists(__DIR__ . $uri) && in_array(strtolower(pathinfo($uri, PATHINFO_EXTENSION)), $video_exts)) {
    $file = __DIR__ . $uri;
    $size = filesize($file);
    $mime_map = ['mp4'=>'video/mp4','mov'=>'video/mp4','webm'=>'video/webm','ogg'=>'video/ogg'];
    $ext  = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    $mime = $mime_map[$ext] ?? 'video/mp4';

    $start = 0;
    $end   = $size - 1;

    if (isset($_SERVER['HTTP_RANGE'])) {
        preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m);
        $start = $m[1] !== '' ? (int)$m[1] : 0;
        $end   = $m[2] !== '' ? (int)$m[2] : $size - 1;
        $end   = min($end, $size - 1);
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    } else {
        http_response_code(200);
    }

    $length = $end - $start + 1;
    header("Content-Type: $mime");
    header("Content-Length: $length");
    header("Accept-Ranges: bytes");
    header("Cache-Control: public, max-age=3600");

    $fp = fopen($file, 'rb');
    fseek($fp, $start);
    $chunk = 8192;
    $sent  = 0;
    while ($sent < $length && !feof($fp)) {
        $read = min($chunk, $length - $sent);
        echo fread($fp, $read);
        $sent += $read;
        flush();
    }
    fclose($fp);
    exit;
}

// Serve non-PHP static files directly (fonts, images, svg, css, js, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri) && pathinfo($uri, PATHINFO_EXTENSION) !== 'php') {
    return false;
}

$routes = [
    '/'               => 'index.php',
    '/login'          => 'login.php',
    '/register'       => 'register.php',
    '/dashboard'      => 'dashboard.php',
    '/logout'         => 'logout.php',
    '/subscribe'      => 'subscribe.php',
    '/menu'           => 'menu.php',
    '/admin'          => 'admin.php',
    '/add-product'    => 'add_product.php',
    '/edit-product'   => 'edit_product.php',
    '/delete-product' => 'delete_product.php',
    '/add-category'   => 'add_category.php',
    '/delete-category'=> 'delete_category.php',
    '/update-theme'   => 'update_theme.php',
    '/update-profile'        => 'update_profile.php',
    '/admin-action'          => 'admin_action.php',
    '/request-subscription'  => 'request_subscription.php',
];

if (isset($routes[$uri])) {
    require __DIR__ . '/' . $routes[$uri];
} elseif (file_exists(__DIR__ . $uri) && pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
    require __DIR__ . $uri;
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>404</title><style>body{background:#0a0a0a;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center}h1{font-size:5rem;font-weight:900;margin:0;opacity:.15}p{color:#888;margin:10px 0 24px}a{color:#fff;text-decoration:none;border:1px solid #333;padding:10px 24px;border-radius:50px;font-size:14px}</style></head><body><div><h1>404</h1><p>الصفحة غير موجودة</p><a href="/">الرئيسية</a></div></body></html>';
}
