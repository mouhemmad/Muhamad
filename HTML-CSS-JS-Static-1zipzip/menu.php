<?php
require_once 'config/config.php';
require_once 'includes/food_icons.php';

$username = $_GET['u'] ?? $_GET['username'] ?? null;
if (!$username) die("رابط غير صحيح");

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("المطعم غير موجود");

$subscription = checkSubscription($user['id']);
if ($subscription['status'] != 'active') { ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>غير متاح</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#111;color:#aaa;text-align:center}h2{color:#fff;margin-bottom:10px}</style></head>
<body><div><h2>هذا المطعم غير متاح حالياً</h2><p>الاشتراك منتهٍ أو لم يتم التجديد</p></div></body></html>
<?php exit(); }

$products  = getProducts($user['id']);
$theme     = $user['selected_theme'] ?? 'dark';

// Load user categories (with icons) keyed by slug
$cats_raw  = getCategories($user['id']);
$cats_map  = []; // slug => category row
foreach ($cats_raw as $c) $cats_map[$c['slug']] = $c;

// Fallback labels for old slugs
$fallback_labels = [
  'main'=>'الأطباق الرئيسية','appetizer'=>'المقبلات',
  'drinks'=>'المشروبات','desserts'=>'الحلويات',
];
$fallback_icons  = [
  'main'=>'utensils','appetizer'=>'leaf',
  'drinks'=>'coffee','desserts'=>'cookie',
];

// Group products by category
$grouped  = [];
foreach ($products as $p) {
  $grouped[$p['category']][] = $p;
}
$all_cats = array_keys($grouped);

// Helper to get category info
function getCatInfo($slug, $cats_map, $fallback_labels, $fallback_icons) {
  if (isset($cats_map[$slug])) {
    return ['name'=>$cats_map[$slug]['name'], 'icon'=>$cats_map[$slug]['icon'], 'color'=>$cats_map[$slug]['color']];
  }
  return ['name'=>$fallback_labels[$slug]??$slug, 'icon'=>$fallback_icons[$slug]??'utensils', 'color'=>'#8b5cf6'];
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?php echo htmlspecialchars($user['restaurant_name']); ?> — المنيو</title>
<style>
@font-face{font-family:'Graphik Arabic';src:url('/Graphik_Arabic_Light.woff2') format('woff2');font-weight:300;font-style:normal;font-display:swap}
@font-face{font-family:'Graphik Arabic';src:url('/Graphik_Arabic_Medium.woff2') format('woff2');font-weight:400 500;font-style:normal;font-display:swap}
@font-face{font-family:'Graphik Arabic';src:url('/Graphik_Arabic_SemiBold.woff2') format('woff2');font-weight:600;font-style:normal;font-display:swap}
@font-face{font-family:'Graphik Arabic';src:url('/Graphik_Arabic_Bold.woff2') format('woff2');font-weight:700 900;font-style:normal;font-display:swap}

/* ═══ RESET ═══ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
img{max-width:100%;display:block}
button{cursor:pointer;font-family:inherit;border:none;outline:none}
a{text-decoration:none}

/* ═══ BASE ═══ */
body{font-family:'Graphik Arabic',sans-serif;min-height:100vh;overflow-x:hidden}

/* ═══ NAVBAR ═══ */
.m-nav{display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:64px;position:sticky;top:0;z-index:100}
.m-nav-logo{font-size:1.3rem;font-weight:900;letter-spacing:-0.5px}
.m-nav-right{display:flex;align-items:center;gap:10px}
.m-nav-badge{padding:7px 16px;border-radius:50px;font-size:13px;font-weight:700}

/* ═══ HERO ═══ */
.m-hero{min-height:340px;display:flex;align-items:center;padding:48px 28px;position:relative;overflow:hidden}
@media(min-width:768px){.m-hero{min-height:420px;padding:80px 60px}}
.m-hero-content{position:relative;z-index:2;max-width:600px}
.m-hero-tag{display:inline-flex;align-items:center;gap:7px;padding:6px 16px;border-radius:50px;font-size:12px;font-weight:700;margin-bottom:20px;text-transform:uppercase;letter-spacing:1px}
.m-hero-name{font-size:clamp(2.5rem,8vw,5rem);font-weight:900;line-height:1;margin-bottom:16px}
.m-hero-sub{font-size:1rem;opacity:.75;line-height:1.6;max-width:420px;margin-bottom:30px}
.m-hero-cta{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:50px;font-size:15px;font-weight:800}
.m-hero-deco{position:absolute;inset:0;z-index:1}

/* ═══ CATEGORIES ═══ */
.m-cats{padding:24px 20px 0}
.m-cats-scroll{display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none}
.m-cats-scroll::-webkit-scrollbar{display:none}
.cat-btn{padding:10px 22px;border-radius:50px;font-size:14px;font-weight:700;white-space:nowrap;transition:all .25s;font-family:'Graphik Arabic',sans-serif}

/* ═══ SECTION TITLE ═══ */
.m-section{padding:32px 20px}
@media(min-width:768px){.m-section{padding:40px 40px}}
.m-section-head{margin-bottom:26px}
.m-section-title{font-size:1.5rem;font-weight:800;margin-bottom:6px}
.m-section-line{height:3px;width:50px;border-radius:2px;margin-bottom:6px}
.m-section-sub{font-size:.875rem;opacity:.55}

/* ═══ PRODUCT GRID ═══ */
.m-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
@media(min-width:580px){.m-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:900px){.m-grid{grid-template-columns:repeat(4,1fr);gap:20px}}

/* ═══ PRODUCT CARD ═══ */
.m-card{border-radius:18px;overflow:hidden;transition:transform .3s,box-shadow .3s;position:relative}
.m-card:hover{transform:translateY(-5px)}
.m-card-img{width:100%;aspect-ratio:1;object-fit:cover;display:block}
.m-card-img-placeholder{width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:3rem}
.m-card-body{padding:14px}
.m-card-name{font-size:.95rem;font-weight:700;line-height:1.3;margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.m-card-desc{font-size:.8rem;opacity:.6;margin-bottom:10px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.m-card-footer{display:flex;align-items:center;justify-content:space-between}
.m-card-price{font-size:1.05rem;font-weight:900}
.m-card-btn{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:.2s}

/* ═══ EMPTY ═══ */
.m-empty{text-align:center;padding:60px 20px;opacity:.5}
.m-empty svg{margin:0 auto 14px}

/* ═══ FOOTER ═══ */
.m-footer{padding:32px 20px;text-align:center;margin-top:20px}
.m-footer-name{font-size:1.1rem;font-weight:800;margin-bottom:6px}
.m-footer-sub{font-size:.8rem;opacity:.5}


/* ════════════════════════════════════
   HERO IMAGE SUPPORT
════════════════════════════════════ */
.m-hero-img-bg{position:absolute;inset:0;z-index:0;background-size:cover;background-position:center;background-repeat:no-repeat}
.m-hero-img-overlay{position:absolute;inset:0;z-index:1}
.m-hero.has-image .m-hero-deco{display:none}
.m-hero.has-image .m-hero-content{z-index:2}
/* per-theme overlays on hero image */
body.theme-dark .m-hero-img-overlay{background:linear-gradient(135deg,rgba(5,5,5,.82) 0%,rgba(5,5,5,.55) 60%,rgba(30,20,0,.4) 100%)}
body.theme-light .m-hero-img-overlay{background:linear-gradient(135deg,rgba(160,30,20,.78) 0%,rgba(200,50,40,.5) 60%,rgba(255,80,60,.3) 100%)}
body.theme-street .m-hero-img-overlay{background:linear-gradient(135deg,rgba(5,5,5,.85) 0%,rgba(10,8,0,.6) 60%,rgba(80,50,0,.35) 100%)}
body.theme-fine .m-hero-img-overlay{background:linear-gradient(135deg,rgba(5,0,0,.88) 0%,rgba(15,5,5,.65) 60%,rgba(80,10,10,.35) 100%)}
body.theme-cafe .m-hero-img-overlay{background:linear-gradient(135deg,rgba(30,12,6,.8) 0%,rgba(60,30,15,.55) 60%,rgba(120,80,50,.3) 100%)}

/* ════════════════════════════════════
   THEMES
════════════════════════════════════ */

/* ── DARK (Luxury Gold / Cinematic) ── */
body.theme-dark{background:#07070a;color:#ede8df}
body.theme-dark .m-nav{background:rgba(5,5,8,.92);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-bottom:1px solid rgba(212,175,55,.1);box-shadow:0 1px 0 rgba(212,175,55,.06)}
body.theme-dark .m-nav-logo{color:#d4af37;letter-spacing:.5px;font-size:1.2rem}
body.theme-dark .m-nav-badge{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.22);color:#c9a227;font-size:12px;font-weight:700}
body.theme-dark .m-hero{background:#07070a;border-bottom:1px solid rgba(212,175,55,.07);min-height:460px}
body.theme-dark .m-hero-deco{background:radial-gradient(ellipse 100% 90% at 95% 30%,rgba(212,175,55,.18) 0%,rgba(180,100,0,.06) 45%,transparent 70%)}
body.theme-dark .m-hero-deco::after{content:'';position:absolute;bottom:-60px;left:5%;width:350px;height:350px;background:radial-gradient(circle,rgba(212,175,55,.06) 0%,transparent 70%)}
body.theme-dark .m-hero-content{color:#fff}
body.theme-dark .m-hero-tag{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.28);color:#d4af37;font-weight:700;letter-spacing:1.5px}
body.theme-dark .m-hero-name{background:linear-gradient(135deg,#e8c96d 0%,#f5e17a 38%,#c9960d 72%,#d4af37 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:clamp(3rem,10vw,6.5rem);text-shadow:none}
body.theme-dark .m-hero-name span{background:inherit;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
body.theme-dark .m-hero-sub{color:rgba(237,232,223,.5);font-size:.97rem}
body.theme-dark .m-hero-cta{background:linear-gradient(135deg,#d4af37,#c9960d);color:#050505;font-weight:900;box-shadow:0 8px 36px rgba(212,175,55,.35),0 2px 8px rgba(212,175,55,.2);border-radius:50px}
body.theme-dark .m-hero-cta:hover{box-shadow:0 12px 48px rgba(212,175,55,.45);opacity:.92}
body.theme-dark .m-cats{background:#07070a;padding:28px 20px 0}
body.theme-dark .cat-btn{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);color:#6a6055;transition:all .25s cubic-bezier(.25,.46,.45,.94)}
body.theme-dark .cat-btn.active,body.theme-dark .cat-btn:hover{background:rgba(212,175,55,.1);border-color:rgba(212,175,55,.3);color:#d4af37}
body.theme-dark .m-section{background:#07070a}
body.theme-dark .m-section-title{color:#ede8df;font-size:1.8rem;font-weight:900;letter-spacing:-.5px}
body.theme-dark .m-section-line{background:linear-gradient(90deg,#d4af37 0%,rgba(212,175,55,.3) 50%,transparent 100%);height:2px}
body.theme-dark .m-section-sub{color:#3e3830}
body.theme-dark .m-card{background:#0e0e11;border:1px solid rgba(255,255,255,.05);border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.4)}
body.theme-dark .m-card:hover{border-color:rgba(212,175,55,.28);box-shadow:0 24px 64px rgba(0,0,0,.6),0 0 0 1px rgba(212,175,55,.1);transform:translateY(-7px)}
body.theme-dark .m-card-img-placeholder{background:#141417;color:#d4af37}
body.theme-dark .m-card-name{color:#ede8df;font-weight:700}
body.theme-dark .m-card-desc{color:#4a4540}
body.theme-dark .m-card-price{color:#d4af37;font-weight:900;font-size:1.1rem}
body.theme-dark .m-card-btn{background:rgba(212,175,55,.1);color:#d4af37;border:1px solid rgba(212,175,55,.22);border-radius:50%;transition:.2s}
body.theme-dark .m-card-btn:hover{background:#d4af37;color:#050505;transform:scale(1.1)}
body.theme-dark .m-footer{background:#050508;border-top:1px solid rgba(212,175,55,.08);color:#3e3830}
body.theme-dark .m-footer-name{color:#d4af37;font-weight:800;letter-spacing:.5px}

/* ── LIGHT (Fresh Minimalist / Clean) ── */
body.theme-light{background:#f8f9fa;color:#1a1a2e}
body.theme-light .m-nav{background:rgba(255,255,255,.96);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 1px 0 #eed8d8,0 4px 24px rgba(192,57,43,.06)}
body.theme-light .m-nav-logo{color:#c0392b;font-weight:900;font-size:1.25rem}
body.theme-light .m-nav-badge{background:#fdf2f2;border:1px solid #f5c6c6;color:#c0392b;font-weight:700}
body.theme-light .m-hero{background:linear-gradient(130deg,#be1e2d 0%,#d63031 35%,#e74c3c 65%,#ff6b6b 100%);min-height:460px}
body.theme-light .m-hero-deco{background:radial-gradient(ellipse 70% 90% at 100% 20%,rgba(255,255,255,.18) 0%,transparent 55%)}
body.theme-light .m-hero-deco::after{content:'';position:absolute;bottom:0;left:0;right:0;height:120px;background:linear-gradient(to top,rgba(0,0,0,.12),transparent)}
body.theme-light .m-hero-content{color:#fff}
body.theme-light .m-hero-tag{background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);font-weight:700;backdrop-filter:blur(10px)}
body.theme-light .m-hero-name{color:#fff;font-size:clamp(3rem,9vw,6rem);font-weight:900;line-height:.95;text-shadow:0 4px 30px rgba(0,0,0,.2)}
body.theme-light .m-hero-name span{color:rgba(255,255,255,.82)}
body.theme-light .m-hero-sub{color:rgba(255,255,255,.8);font-size:.97rem}
body.theme-light .m-hero-cta{background:#fff;color:#c0392b;font-weight:900;box-shadow:0 8px 32px rgba(0,0,0,.18),0 2px 8px rgba(0,0,0,.1);border-radius:50px}
body.theme-light .m-hero-cta:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(0,0,0,.22)}
body.theme-light .m-cats{background:#f8f9fa;border-bottom:1px solid #f0eded;padding:28px 20px 0}
body.theme-light .cat-btn{background:#fff;color:#888;border:1.5px solid #eedede;box-shadow:0 2px 10px rgba(0,0,0,.05);transition:all .22s cubic-bezier(.25,.46,.45,.94)}
body.theme-light .cat-btn.active,body.theme-light .cat-btn:hover{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border-color:transparent;box-shadow:0 6px 20px rgba(192,57,43,.3)}
body.theme-light .m-section{background:#f8f9fa}
body.theme-light .m-section-title{color:#1a1a2e;font-size:1.6rem;font-weight:900;letter-spacing:-.5px}
body.theme-light .m-section-line{background:linear-gradient(90deg,#c0392b,#e74c3c,rgba(231,76,60,.2),transparent);height:3px;border-radius:2px}
body.theme-light .m-section-sub{color:#bbb}
body.theme-light .m-card{background:#fff;box-shadow:0 2px 20px rgba(0,0,0,.06),0 0 0 1px #f5eaea;border-radius:22px;border:none}
body.theme-light .m-card:hover{box-shadow:0 16px 48px rgba(192,57,43,.14),0 0 0 2px #c0392b;transform:translateY(-5px)}
body.theme-light .m-card-img-placeholder{background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#c0392b}
body.theme-light .m-card-name{color:#1a1a2e;font-weight:700}
body.theme-light .m-card-desc{color:#ccc}
body.theme-light .m-card-price{color:#c0392b;font-weight:900;font-size:1.1rem}
body.theme-light .m-card-btn{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border-radius:50%;box-shadow:0 4px 14px rgba(192,57,43,.35);transition:.2s}
body.theme-light .m-card-btn:hover{transform:scale(1.12);box-shadow:0 6px 20px rgba(192,57,43,.45)}
body.theme-light .m-footer{background:linear-gradient(135deg,#c0392b,#e74c3c);color:rgba(255,255,255,.75)}
body.theme-light .m-footer-name{color:#fff;font-weight:900}

/* ── STREET (Urban Bold / Gritty Yellow) ── */
body.theme-street{background:#0a0a0a;color:#f0f0f0}
body.theme-street .m-nav{background:#080808;border-bottom:3px solid #f39c12;padding:0 24px}
body.theme-street .m-nav-logo{color:#f39c12;font-size:1.35rem;font-weight:900;text-transform:uppercase;letter-spacing:2px}
body.theme-street .m-nav-badge{background:#f39c12;color:#0a0a0a;font-weight:900;border-radius:6px;padding:6px 14px}
body.theme-street .m-hero{background:#0a0a0a;min-height:500px;position:relative;overflow:hidden}
body.theme-street .m-hero-deco{background:radial-gradient(ellipse 80% 100% at 20% 80%,rgba(243,156,18,.22) 0%,rgba(230,126,34,.06) 45%,transparent 70%)}
body.theme-street .m-hero-deco::after{content:'MENU';position:absolute;bottom:-30px;right:-20px;font-size:clamp(8rem,25vw,18rem);font-weight:900;color:rgba(255,255,255,.018);letter-spacing:-10px;line-height:1;pointer-events:none;font-family:'Graphik Arabic',sans-serif}
body.theme-street .m-hero-content{color:#fff}
body.theme-street .m-hero-tag{background:#f39c12;color:#0a0a0a;font-weight:900;border-radius:6px;letter-spacing:2px;text-transform:uppercase;font-size:11px}
body.theme-street .m-hero-name{font-size:clamp(3.5rem,12vw,7.5rem);font-weight:900;line-height:.88;letter-spacing:-3px;color:#fff;text-transform:uppercase}
body.theme-street .m-hero-name span{color:#f39c12;display:block;font-style:italic}
body.theme-street .m-hero-sub{color:#555;font-size:.92rem}
body.theme-street .m-hero-cta{background:#f39c12;color:#0a0a0a;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;border-radius:8px;box-shadow:0 8px 36px rgba(243,156,18,.4)}
body.theme-street .m-hero-cta:hover{background:#e67e22;box-shadow:0 12px 48px rgba(243,156,18,.5)}
body.theme-street .m-cats{background:#0a0a0a;padding:28px 20px 0;border-bottom:none}
body.theme-street .cat-btn{background:#111;border:2px solid #1e1e1e;color:#555;border-radius:8px;text-transform:uppercase;letter-spacing:.8px;font-size:12px;font-weight:900;transition:all .2s cubic-bezier(.25,.46,.45,.94)}
body.theme-street .cat-btn.active,body.theme-street .cat-btn:hover{background:#f39c12;border-color:#f39c12;color:#0a0a0a;box-shadow:0 6px 20px rgba(243,156,18,.35)}
body.theme-street .m-section{background:#0a0a0a}
body.theme-street .m-section-title{font-size:2.2rem;font-weight:900;text-transform:uppercase;letter-spacing:-1.5px;color:#fff}
body.theme-street .m-section-line{background:#f39c12;height:4px;width:60px;border-radius:2px}
body.theme-street .m-section-sub{color:#333}
body.theme-street .m-card{background:#111;border:2px solid #191919;border-radius:14px;transition:transform .3s,box-shadow .3s,border-color .3s}
body.theme-street .m-card:hover{border-color:#f39c12;box-shadow:0 0 0 1px rgba(243,156,18,.3),0 12px 48px rgba(243,156,18,.18);transform:translateY(-5px)}
body.theme-street .m-card-img-placeholder{background:#181818;color:#f39c12}
body.theme-street .m-card-name{color:#fff;font-weight:900;text-transform:uppercase;font-size:.9rem;letter-spacing:.5px}
body.theme-street .m-card-desc{color:#404040}
body.theme-street .m-card-price{color:#f39c12;font-weight:900;font-size:1.15rem}
body.theme-street .m-card-btn{background:#f39c12;color:#0a0a0a;border-radius:8px;font-weight:900;transition:.2s}
body.theme-street .m-card-btn:hover{background:#e67e22;transform:scale(1.08)}
body.theme-street .m-footer{background:#060606;border-top:3px solid #f39c12;color:#444}
body.theme-street .m-footer-name{color:#f39c12;font-weight:900;text-transform:uppercase;letter-spacing:2px}

/* ── FINE (Premium Fine Dining / Crimson Noir) ── */
body.theme-fine{background:#080508;color:#f0ece8}
body.theme-fine .m-nav{background:rgba(6,3,6,.96);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-bottom:1px solid rgba(178,20,30,.18);box-shadow:0 1px 0 rgba(178,20,30,.1)}
body.theme-fine .m-nav-logo{color:#fff;font-weight:900;font-size:1.15rem;letter-spacing:2.5px;text-transform:uppercase}
body.theme-fine .m-nav-badge{background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.28);color:#e74c3c;font-size:12px}
body.theme-fine .m-hero{background:linear-gradient(160deg,#0e0203 0%,#130505 40%,#080508 100%);min-height:480px;position:relative}
body.theme-fine .m-hero-deco{background:radial-gradient(ellipse 90% 70% at 70% 55%,rgba(200,20,30,.28) 0%,rgba(120,10,15,.1) 40%,transparent 68%)}
body.theme-fine .m-hero-deco::after{content:'';position:absolute;top:0;left:0;right:0;height:50%;background:radial-gradient(ellipse 50% 60% at 30% 0%,rgba(178,20,30,.1) 0%,transparent 60%)}
body.theme-fine .m-hero-content{color:#fff}
body.theme-fine .m-hero-tag{background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.35);color:#e74c3c;font-weight:700;text-transform:uppercase;letter-spacing:2.5px;font-size:10.5px}
body.theme-fine .m-hero-name{color:#fff;font-weight:900;font-size:clamp(3rem,10vw,6.5rem);line-height:.9;letter-spacing:-2px;text-shadow:0 8px 60px rgba(178,20,30,.4)}
body.theme-fine .m-hero-name span{color:#c0392b;display:block}
body.theme-fine .m-hero-sub{color:rgba(240,236,232,.4);font-size:.95rem}
body.theme-fine .m-hero-cta{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;font-weight:900;text-transform:uppercase;letter-spacing:1.5px;border-radius:10px;box-shadow:0 8px 36px rgba(192,57,43,.4)}
body.theme-fine .m-hero-cta:hover{box-shadow:0 12px 48px rgba(192,57,43,.5);transform:translateY(-2px)}
body.theme-fine .m-cats{background:#080508;padding:28px 20px 0}
body.theme-fine .cat-btn{background:#100a0a;border:1px solid #1e1414;color:#4a3535;border-radius:10px;transition:all .22s}
body.theme-fine .cat-btn.active,body.theme-fine .cat-btn:hover{background:rgba(192,57,43,.14);border-color:rgba(192,57,43,.35);color:#e74c3c}
body.theme-fine .m-section{background:#080508}
body.theme-fine .m-section-title{color:#f0ece8;font-size:2rem;font-weight:900;letter-spacing:-1px}
body.theme-fine .m-section-line{background:linear-gradient(90deg,#c0392b,rgba(192,57,43,.35),transparent);height:2px;border-radius:1px}
body.theme-fine .m-section-sub{color:#2a1a1a}
body.theme-fine .m-card{background:#0e080a;border:1px solid #1a1010;border-radius:18px;box-shadow:0 4px 30px rgba(0,0,0,.5)}
body.theme-fine .m-card:hover{border-color:rgba(192,57,43,.35);box-shadow:0 20px 70px rgba(0,0,0,.7),0 0 0 1px rgba(192,57,43,.12);transform:translateY(-7px)}
body.theme-fine .m-card-img-placeholder{background:linear-gradient(135deg,#140a0a,#1e1010);color:#c0392b}
body.theme-fine .m-card-name{color:#f0ece8;font-weight:700;font-size:.95rem}
body.theme-fine .m-card-desc{color:#3a2020}
body.theme-fine .m-card-price{color:#e74c3c;font-weight:900;font-size:1.1rem}
body.theme-fine .m-card-btn{background:rgba(192,57,43,.12);color:#e74c3c;border:1px solid rgba(192,57,43,.25);border-radius:50%;transition:.2s}
body.theme-fine .m-card-btn:hover{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;transform:scale(1.1)}
body.theme-fine .m-footer{background:#050305;border-top:1px solid rgba(178,20,30,.12);color:#2a1515}
body.theme-fine .m-footer-name{color:#c0392b;font-weight:900;text-transform:uppercase;letter-spacing:2px}

/* ── CAFE (Warm Cozy / Coffee House) ── */
body.theme-cafe{background:#faf6ef;color:#2b1a0e}
body.theme-cafe .m-nav{background:rgba(252,249,243,.97);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 1px 0 #e8ddd0,0 4px 20px rgba(93,64,55,.07)}
body.theme-cafe .m-nav-logo{color:#5d4037;font-weight:900;font-style:italic;font-size:1.25rem}
body.theme-cafe .m-nav-badge{background:#fff8f0;border:1px solid #e8d5c4;color:#795548;font-weight:700}
body.theme-cafe .m-hero{background:linear-gradient(130deg,#2e1503 0%,#4e2d0e 25%,#6d4018 50%,#9c6a35 80%,#b58750 100%);min-height:460px;position:relative}
body.theme-cafe .m-hero-deco{background:radial-gradient(ellipse 60% 80% at 90% 30%,rgba(255,200,100,.18) 0%,rgba(255,180,60,.05) 50%,transparent 70%)}
body.theme-cafe .m-hero-deco::after{content:'';position:absolute;bottom:0;left:0;right:0;height:180px;background:linear-gradient(to top,rgba(0,0,0,.15) 0%,transparent 100%)}
body.theme-cafe .m-hero-content{color:#fff}
body.theme-cafe .m-hero-tag{background:rgba(255,255,255,.16);color:#ffcc80;border:1px solid rgba(255,255,255,.22);font-weight:700;backdrop-filter:blur(10px)}
body.theme-cafe .m-hero-name{color:#fff;font-weight:900;font-style:italic;font-size:clamp(3rem,9vw,6rem);text-shadow:0 6px 40px rgba(0,0,0,.3)}
body.theme-cafe .m-hero-name span{color:#ffcc80}
body.theme-cafe .m-hero-sub{color:rgba(255,255,255,.65);font-size:.97rem}
body.theme-cafe .m-hero-cta{background:#fff;color:#5d4037;font-weight:900;font-style:italic;border-radius:50px;box-shadow:0 8px 32px rgba(0,0,0,.2)}
body.theme-cafe .m-hero-cta:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(0,0,0,.25)}
body.theme-cafe .m-cats{background:#faf6ef;padding:28px 20px 0;border-bottom:1px solid #ede3d4}
body.theme-cafe .cat-btn{background:#fff;color:#a8856f;border:1.5px solid #e8d4c4;border-radius:50px;box-shadow:0 2px 10px rgba(93,64,55,.08);transition:all .22s}
body.theme-cafe .cat-btn.active,body.theme-cafe .cat-btn:hover{background:linear-gradient(135deg,#795548,#9c6a35);color:#fff;border-color:transparent;box-shadow:0 6px 20px rgba(121,85,72,.3)}
body.theme-cafe .m-section{background:#faf6ef}
body.theme-cafe .m-section-title{color:#2b1a0e;font-size:1.7rem;font-weight:900;font-style:italic;letter-spacing:-.3px}
body.theme-cafe .m-section-line{background:linear-gradient(90deg,#795548,rgba(121,85,72,.35),transparent);height:3px;border-radius:2px}
body.theme-cafe .m-section-sub{color:#d4b9a0}
body.theme-cafe .m-card{background:#fff;border:1px solid #ede0cf;border-radius:22px;box-shadow:0 4px 24px rgba(93,64,55,.1)}
body.theme-cafe .m-card:hover{box-shadow:0 20px 60px rgba(93,64,55,.2),0 0 0 2px #9c6a35;transform:translateY(-5px)}
body.theme-cafe .m-card-img-placeholder{background:linear-gradient(135deg,#fdf4e8,#f5e8d4);color:#a8856f}
body.theme-cafe .m-card-name{color:#2b1a0e;font-weight:700}
body.theme-cafe .m-card-desc{color:#d4b9a0}
body.theme-cafe .m-card-price{color:#795548;font-weight:900;font-size:1.1rem}
body.theme-cafe .m-card-btn{background:linear-gradient(135deg,#795548,#9c6a35);color:#fff;border-radius:50%;box-shadow:0 4px 14px rgba(121,85,72,.35);transition:.2s}
body.theme-cafe .m-card-btn:hover{transform:scale(1.12);box-shadow:0 8px 24px rgba(121,85,72,.45)}
body.theme-cafe .m-footer{background:linear-gradient(135deg,#2e1503,#4e2d0e);color:rgba(255,255,255,.45)}
body.theme-cafe .m-footer-name{color:#ffcc80;font-weight:900;font-style:italic}

/* ═══ SPLASH PAGE ═══ */
#splash{position:fixed;inset:0;z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;background:#000;overflow:hidden}
#splash-video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.75}
#splash-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85) 0%,rgba(0,0,0,.2) 50%,rgba(0,0,0,.4) 100%)}
#splash-content{position:relative;z-index:2;width:100%;max-width:420px;padding:0 24px 48px;display:flex;flex-direction:column;align-items:center;gap:20px}
#splash-name{font-family:'Graphik Arabic',sans-serif;font-size:clamp(1.8rem,8vw,2.8rem);font-weight:900;color:#fff;text-align:center;line-height:1.2;text-shadow:0 2px 20px rgba(0,0,0,.5)}
.splash-langs{display:flex;gap:12px;width:100%}
.splash-lang-btn{flex:1;padding:14px;border-radius:50px;font-size:16px;font-weight:800;font-family:'Graphik Arabic',sans-serif;border:none;cursor:pointer;transition:.2s;letter-spacing:.3px}
.splash-lang-btn:hover{opacity:.88;transform:scale(1.02)}
.splash-socials{display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap}
.splash-social-a{width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.25);transition:.2s;text-decoration:none}
.splash-social-a:hover{background:rgba(255,255,255,.28);transform:scale(1.1)}
.splash-social-a svg{width:20px;height:20px}
.splash-brand{display:flex;align-items:center;gap:10px;text-decoration:none;padding:8px 16px;border-radius:16px;background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);transition:.2s;margin-top:4px}
.splash-brand:hover{background:rgba(255,255,255,.14);transform:scale(1.03)}
.splash-brand-icon{width:36px;height:36px;border-radius:10px;overflow:hidden;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.4)}
.splash-brand-icon img{width:100%;height:100%;object-fit:cover;display:block}
.splash-brand-text{display:flex;flex-direction:column;align-items:flex-start;gap:1px}
.splash-brand-powered{font-family:'Graphik Arabic',sans-serif;font-size:10px;color:rgba(255,255,255,.45);font-weight:500;line-height:1}
.splash-brand-name{font-family:'Graphik Arabic',sans-serif;font-size:14px;color:rgba(255,255,255,.92);font-weight:700;line-height:1.2;letter-spacing:.2px}
#splash.hidden{opacity:0;pointer-events:none;transition:opacity .5s ease}
</style>
</head>
<body class="theme-<?php echo htmlspecialchars($theme); ?>">

<?php
$has_splash = !empty($user['video_url']) || !empty($user['social_instagram']) || !empty($user['social_tiktok']) || !empty($user['social_snapchat']) || !empty($user['social_whatsapp']) || !empty($user['social_facebook']) || !empty($user['social_location']);
$accent = ['dark'=>'#d4af37','light'=>'#c0392b','street'=>'#f39c12','fine'=>'#e74c3c','cafe'=>'#795548'];
$ac = $accent[$theme] ?? '#ED1C24';
?>

<?php if ($has_splash): ?>
<!-- SPLASH PAGE -->
<div id="splash">
  <?php if (!empty($user['video_url'])): ?>
  <video id="splash-video" autoplay muted loop playsinline preload="auto">
    <source src="/<?php echo htmlspecialchars($user['video_url']); ?>" type="video/mp4">
  </video>
  <?php else: ?>
  <div id="splash-video" style="background:linear-gradient(135deg,#0a0a0a 0%,#1a1a1a 100%)"></div>
  <?php endif; ?>
  <div id="splash-overlay"></div>
  <div id="splash-content">
    <div id="splash-name"><?php echo htmlspecialchars($user['restaurant_name']); ?></div>

    <?php
    $socials = [
      'instagram' => ['url'=>$user['social_instagram']??'', 'color'=>'#E1306C', 'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>'],
      'tiktok'    => ['url'=>$user['social_tiktok']??'',    'color'=>'#010101', 'svg'=>'<svg viewBox="0 0 24 24" fill="#fff"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.28 6.28 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.74a4.85 4.85 0 0 1-1.01-.05z"/></svg>'],
      'snapchat'  => ['url'=>$user['social_snapchat']??'',  'color'=>'#FFFC00', 'svg'=>'<svg viewBox="0 0 24 24" fill="#fff"><path d="M12.002 2C8.267 2 6 4.238 6 7.7v.74l-1.653.874a.355.355 0 0 0-.196.32c0 .253.179.466.427.512l1.422.27c-.042.142-.1.285-.177.412-.37.612-1.003.882-1.796.882a2.1 2.1 0 0 1-.428-.044.35.35 0 0 0-.083-.009.37.37 0 0 0-.37.37c0 .22.147.413.36.458C4.52 12.73 5.14 13.5 6.37 13.5c.04 0 .08 0 .12-.002.29.448.7.8 1.201 1.03C8.48 15 9.43 15.12 10.4 15.2c-.4.6-.87.94-1.63.94-.26 0-.56-.05-.86-.16a2.4 2.4 0 0 0-.82-.15c-.8 0-1.52.44-1.52 1.07 0 .84 1.23 1.32 3.43 1.32.18 0 .36-.01.54-.02a6.26 6.26 0 0 0 1.46.44v.38c0 .46.37.84.83.84.46 0 .84-.38.84-.84v-.38a6.26 6.26 0 0 0 1.46-.44c.18.01.36.02.54.02 2.2 0 3.43-.48 3.43-1.32 0-.63-.72-1.07-1.52-1.07-.3 0-.58.06-.82.15-.3.11-.6.16-.86.16-.76 0-1.23-.34-1.63-.94.97-.08 1.92-.2 2.71-.63.5-.23.91-.58 1.2-1.03.04.002.08.002.12.002 1.23 0 1.85-.77 2.34-1.965.213-.045.36-.238.36-.458a.37.37 0 0 0-.37-.37.35.35 0 0 0-.083.009 2.1 2.1 0 0 1-.428.044c-.793 0-1.426-.27-1.796-.882a2.22 2.22 0 0 1-.177-.412l1.422-.27a.518.518 0 0 0 .427-.512.355.355 0 0 0-.196-.32L18 8.44V7.7C18 4.238 15.733 2 12.002 2z"/></svg>'],
      'whatsapp'  => ['url'=>$user['social_whatsapp']??'',  'color'=>'#25D366', 'svg'=>'<svg viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>'],
      'facebook'  => ['url'=>$user['social_facebook']??'',  'color'=>'#1877F2', 'svg'=>'<svg viewBox="0 0 24 24" fill="#fff"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'],
      'location'  => ['url'=>$user['social_location']??'',  'color'=>'#EA4335', 'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>'],
    ];
    $has_socials = array_filter($socials, fn($s) => !empty($s['url']));
    ?>

    <div class="splash-langs">
      <button class="splash-lang-btn" style="background:<?php echo $ac; ?>;color:#fff" onclick="enterMenu('ar')">العربية</button>
      <button class="splash-lang-btn" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.35)" onclick="enterMenu('en')">English</button>
    </div>

    <?php if (!empty($has_socials)): ?>
    <div class="splash-socials">
      <?php foreach($socials as $key => $s): if(empty($s['url'])) continue; ?>
      <a href="<?php echo htmlspecialchars($s['url']); ?>" target="_blank" class="splash-social-a" style="background:<?php echo $s['color']; ?>cc">
        <?php echo $s['svg']; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <a href="https://kozhenstudio.com" target="_blank" class="splash-brand">
      <div class="splash-brand-icon">
        <img src="/kozhen-icon.jpeg" alt="Kozhen Studio">
      </div>
      <div class="splash-brand-text">
        <span class="splash-brand-powered">مُشغَّل بواسطة</span>
        <span class="splash-brand-name">Kozhen Studio</span>
      </div>
    </a>
  </div>
</div>
<script>
function enterMenu(lang) {
  document.getElementById('splash').classList.add('hidden');
  setTimeout(()=>{ document.getElementById('splash').style.display='none'; }, 500);
  sessionStorage.setItem('splash_seen_<?php echo (int)$user['id']; ?>', '1');
}
(function(){
  if (sessionStorage.getItem('splash_seen_<?php echo (int)$user['id']; ?>')) {
    var s = document.getElementById('splash');
    if (s) s.style.display = 'none';
  }
})();
</script>
<?php endif; ?>

<!-- NAVBAR -->
<nav class="m-nav">
  <div class="m-nav-logo"><?php echo htmlspecialchars($user['restaurant_name']); ?></div>
  <div class="m-nav-right">
    <div class="m-nav-badge">
      <?php echo count($products); ?> صنف
    </div>
  </div>
</nav>

<!-- HERO -->
<?php $has_hero = !empty($user['hero_image']); ?>
<div class="m-hero<?php echo $has_hero ? ' has-image' : ''; ?>">
  <?php if ($has_hero): ?>
  <div class="m-hero-img-bg" style="background-image:url('/<?php echo htmlspecialchars($user['hero_image']); ?>')"></div>
  <div class="m-hero-img-overlay"></div>
  <?php else: ?>
  <div class="m-hero-deco"></div>
  <?php endif; ?>
  <div class="m-hero-content">
    <div class="m-hero-tag">
      <span style="display:inline-flex;vertical-align:middle"><?php echo getFoodIconSvg('utensils', 13); ?></span>
      قائمة الطعام
    </div>
    <div class="m-hero-name">
      <?php
        $words = explode(' ', $user['restaurant_name']);
        if (count($words) >= 2) {
          echo htmlspecialchars($words[0]) . '<br><span>' . htmlspecialchars(implode(' ', array_slice($words, 1))) . '</span>';
        } else {
          echo htmlspecialchars($user['restaurant_name']);
        }
      ?>
    </div>
    <p class="m-hero-sub">اكتشف أشهى الأطباق والمشروبات — جودة لا تُضاهى في كل وجبة</p>
    <?php if (!empty($products)): ?>
    <a href="#menu" class="m-hero-cta">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      تصفح المنيو
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- CATEGORY TABS -->
<?php if (!empty($grouped)): ?>
<div class="m-cats">
  <div class="m-cats-scroll" id="catTabs">
    <button class="cat-btn active" onclick="filterCat('all', this)">
      <span style="display:inline-flex;vertical-align:middle;margin-left:5px"><?php echo getFoodIconSvg('utensils', 13); ?></span>الكل
    </button>
    <?php foreach ($all_cats as $cat):
      $ci = getCatInfo($cat, $cats_map, $fallback_labels, $fallback_icons);
    ?>
    <button class="cat-btn" onclick="filterCat('<?php echo htmlspecialchars($cat); ?>', this)">
      <span style="display:inline-flex;vertical-align:middle;margin-left:5px"><?php echo getFoodIconSvg($ci['icon'], 13); ?></span><?php echo htmlspecialchars($ci['name']); ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- MENU -->
<div id="menu">
<?php if (empty($products)): ?>
  <div class="m-empty">
    <span style="opacity:.4"><?php echo getFoodIconSvg('utensils', 48); ?></span>
    <p>لم يتم إضافة منتجات بعد</p>
  </div>
<?php else: ?>
  <?php foreach ($grouped as $cat => $items):
    $ci = getCatInfo($cat, $cats_map, $fallback_labels, $fallback_icons);
  ?>
  <div class="m-section cat-section" data-cat="<?php echo htmlspecialchars($cat); ?>">
    <div class="m-section-head">
      <div class="m-section-line"></div>
      <div class="m-section-title" style="display:flex;align-items:center;gap:10px">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;background:<?php echo $ci['color']; ?>22;color:<?php echo $ci['color']; ?>;flex-shrink:0">
          <?php echo getFoodIconSvg($ci['icon'], 20); ?>
        </span>
        <?php echo htmlspecialchars($ci['name']); ?>
      </div>
      <div class="m-section-sub"><?php echo count($items); ?> صنف متاح</div>
    </div>
    <div class="m-grid">
      <?php foreach ($items as $p): ?>
      <div class="m-card">
        <?php if ($p['image_url'] && file_exists($p['image_url'])): ?>
          <img class="m-card-img" src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
        <?php else: ?>
          <div class="m-card-img-placeholder">
            <span style="opacity:.35;color:var(--fg3)"><?php echo getFoodIconSvg($ci['icon'], 36); ?></span>
          </div>
        <?php endif; ?>
        <div class="m-card-body">
          <div class="m-card-name"><?php echo htmlspecialchars($p['name']); ?></div>
          <?php if ($p['description']): ?>
          <div class="m-card-desc"><?php echo htmlspecialchars($p['description']); ?></div>
          <?php endif; ?>
          <div class="m-card-footer">
            <span class="m-card-price"><?php echo number_format($p['price'], 2); ?> د</span>
            <button class="m-card-btn" title="إضافة">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<!-- FOOTER -->
<footer class="m-footer">
  <div class="m-footer-name"><?php echo htmlspecialchars($user['restaurant_name']); ?></div>
  <a href="https://kozhenstudio.com" target="_blank" style="display:inline-flex;align-items:center;gap:7px;margin-top:10px;text-decoration:none;opacity:.55;transition:.2s" onmouseenter="this.style.opacity='0.85'" onmouseleave="this.style.opacity='0.55'">
    <img src="/kozhen-icon.jpeg" alt="Kozhen Studio" style="width:22px;height:22px;border-radius:6px;object-fit:cover">
    <span style="font-size:.78rem;font-weight:600;color:inherit">مُشغَّل بواسطة Kozhen Studio</span>
  </a>
</footer>

<script>
function filterCat(cat, btn) {
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.cat-section').forEach(s => {
    s.style.display = (cat === 'all' || s.dataset.cat === cat) ? 'block' : 'none';
  });
}

// Smooth scroll to menu
document.querySelector('.m-hero-cta')?.addEventListener('click', e => {
  e.preventDefault();
  document.getElementById('menu').scrollIntoView({behavior:'smooth'});
});
</script>
</body>
</html>
