<?php
require_once 'config/config.php';
require_once 'includes/food_icons.php';
if (!isLoggedIn()) { header("Location: /login"); exit(); }
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
$user         = getUser();
$subscription = checkSubscription($_SESSION['user_id']);
$themes       = getThemes();
$products     = getProducts($_SESSION['user_id']);
$cats         = getCategories($_SESSION['user_id']);
if ($subscription['status'] == 'expired') { header("Location: /subscribe"); exit(); }
$menu_url = SITE_URL . 'menu?u=' . urlencode($user['username']);
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة التحكم — <?php echo htmlspecialchars($user['restaurant_name']); ?></title>
<style>
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Light.woff2') format('woff2');font-weight:300}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Medium.woff2') format('woff2');font-weight:500}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_SemiBold.woff2') format('woff2');font-weight:600}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Bold.woff2') format('woff2');font-weight:700}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0a0a;--bg2:#111;--bg3:#161616;
  --fg:#f5f5f5;--fg2:#aaa;--fg3:#555;
  --border:#222;--border2:#2a2a2a;
  --red:#ED1C24;--red-dim:#1a0001;--red-border:#3a0002;
  --blue:#3b82f6;--yellow:#f59e0b;--green:#22c55e;
}
body{font-family:'GraphikArabic','Tajawal',sans-serif;background:var(--bg);color:var(--fg);min-height:100vh}

/* ── TOPBAR ── */
.topbar{background:rgba(10,10,10,.92);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;justify-content:space-between;padding:0 20px;position:sticky;top:0;z-index:200}
.topbar-logo{display:flex;align-items:center;gap:9px;text-decoration:none}
.logo-mark{width:30px;height:30px;background:var(--red);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.logo-mark svg{width:14px;height:14px;fill:none;stroke:#fff;stroke-width:2.5}
.logo-text{font-size:1rem;font-weight:700;color:var(--fg);letter-spacing:-0.3px}
.topbar-right{display:flex;align-items:center;gap:7px}

/* ── BUTTONS ── */
.t-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:50px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid transparent;font-family:inherit;transition:opacity .2s,background .2s;white-space:nowrap}
.t-btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.t-btn-ghost{background:var(--bg2);border-color:var(--border);color:var(--fg)}
.t-btn-ghost:hover{background:var(--bg3)}
.t-btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
.t-btn-primary:hover{opacity:.85}
.t-btn-danger{background:var(--red-dim);border-color:var(--red-border);color:var(--red)}
.t-btn-danger:hover{background:#220002}

/* ── LAYOUT ── */
.layout{display:grid;grid-template-columns:230px 1fr;min-height:calc(100vh - 60px)}
@media(max-width:768px){.layout{grid-template-columns:1fr}.sidebar{display:none}}

/* ── SIDEBAR ── */
.sidebar{background:var(--bg);border-left:1px solid var(--border);padding:20px 12px;display:flex;flex-direction:column;gap:2px}
.user-card{padding:14px;background:var(--bg2);border:1px solid var(--border);border-radius:14px;margin-bottom:16px}
.user-card-name{font-size:13.5px;font-weight:700;color:var(--fg)}
.user-card-handle{font-size:12px;color:var(--fg3);margin-top:3px}
.sidebar-section{font-size:10.5px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.8px;padding:14px 12px 5px;margin-top:4px}
.nav-item{display:flex;align-items:center;gap:9px;padding:10px 14px;border-radius:11px;color:var(--fg2);cursor:pointer;text-decoration:none;font-size:13.5px;font-weight:500;transition:.15s;border:1px solid transparent;background:none;width:100%;text-align:right;font-family:inherit}
.nav-item svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.nav-item:hover{background:var(--bg2);color:var(--fg);border-color:var(--border)}
.nav-item.active{background:var(--red-dim);color:var(--red);border-color:var(--red-border)}
.nav-item.danger{color:var(--red)}
.nav-item.danger:hover{background:var(--red-dim);border-color:var(--red-border)}

/* ── MAIN ── */
.main{padding:24px 22px;overflow-y:auto;max-height:calc(100vh - 60px)}

/* ── ALERT ── */
.alert{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:13px;font-size:13.5px;font-weight:600;margin-bottom:20px}
.alert svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.alert a{color:inherit;font-weight:700;text-decoration:underline}
.alert-warning{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:var(--yellow)}
.alert-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:var(--green)}
.alert-red{background:var(--red-dim);border:1px solid var(--red-border);color:var(--red)}

/* ── LINK BOX ── */
.link-box{background:var(--bg2);border:1px solid var(--border);border-radius:15px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.link-box-url{font-size:13.5px;font-weight:500;color:var(--fg2);word-break:break-all}
.link-box-label{font-size:11px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:4px;display:flex;align-items:center;gap:5px}
.link-box-label svg{width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2}
.link-box-actions{display:flex;gap:8px;flex-shrink:0}

/* ── CARD ── */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:18px;padding:22px;margin-bottom:20px}
.card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px}
.card-head h2{font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--fg)}
.card-head h2 svg{width:16px;height:16px;stroke:var(--red);fill:none;stroke-width:2}

/* ── STATS ── */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:20px;transition:border-color .2s}
.stat:hover{border-color:var(--border2)}
.stat-label{font-size:11.5px;font-weight:600;color:var(--fg3);margin-bottom:10px;display:flex;align-items:center;gap:6px}
.stat-label svg{width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2}
.stat-value{font-size:2rem;font-weight:700;color:var(--fg);letter-spacing:-1px}
.stat-sub{font-size:11.5px;color:var(--fg3);margin-top:4px}

/* ── TABLE ── */
.prod-table{width:100%;border-collapse:collapse}
.prod-table th{text-align:right;font-size:11px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.6px;padding:8px 14px;border-bottom:1px solid var(--border)}
.prod-table td{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,0.04);vertical-align:middle;font-size:14px}
.prod-table tr:last-child td{border-bottom:none}
.prod-table tr:hover td{background:rgba(255,255,255,.02)}
.prod-img{width:44px;height:44px;border-radius:10px;object-fit:cover}
.prod-img-placeholder{width:44px;height:44px;border-radius:10px;background:var(--bg3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--fg3)}
.prod-img-placeholder svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:1.5}
.prod-name{font-size:13.5px;font-weight:600;color:var(--fg)}
.prod-desc{font-size:11.5px;color:var(--fg3);margin-top:2px}
.prod-price{font-size:14px;font-weight:700;color:var(--red)}
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:50px;font-size:11px;font-weight:600}
.badge svg{width:10px;height:10px;stroke:currentColor;fill:none;stroke-width:2}
.badge-cat{background:rgba(59,130,246,.1);color:#60a5fa;border:1px solid rgba(59,130,246,.2)}
.btn-icon{width:32px;height:32px;border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;border:1px solid transparent}
.btn-icon svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}
.btn-edit{background:rgba(59,130,246,.08);border-color:rgba(59,130,246,.2);color:#60a5fa}
.btn-edit:hover{background:rgba(59,130,246,.18)}
.btn-del{background:var(--red-dim);border-color:var(--red-border);color:var(--red)}
.btn-del:hover{background:#220002}
.btn-actions{display:flex;gap:6px;justify-content:flex-end}
.empty-state{text-align:center;padding:44px 20px;color:var(--fg3)}
.empty-state svg{width:40px;height:40px;stroke:currentColor;fill:none;stroke-width:1;opacity:.25;display:block;margin:0 auto 12px}

/* ── FORM ── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}}
.form-group{margin-bottom:0}
.form-full{grid-column:1/-1}
.form-group label{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--fg3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px}
.form-group label svg{width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2}
.form-group input,.form-group textarea,.form-group select{width:100%;background:#0d0d0d;border:1px solid var(--border);color:var(--fg);padding:11px 14px;border-radius:11px;font-size:14px;font-family:inherit;outline:none;transition:.2s;resize:vertical}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{border-color:var(--red);background:#120000}
.form-group select option{background:#111;color:var(--fg)}
.form-group input::placeholder,.form-group textarea::placeholder{color:var(--fg3)}
.btn-submit{background:var(--red);color:#fff;border:none;padding:11px 22px;border-radius:11px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px;transition:.2s;margin-top:6px}
.btn-submit:hover{opacity:.85}
.btn-submit svg{width:15px;height:15px;stroke:#fff;fill:none;stroke-width:2.5}

/* ── CATEGORIES ── */
.cats-list{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
.cat-row{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg3);border:1px solid var(--border);border-radius:13px;gap:10px}
.cat-row-info{display:flex;align-items:center;gap:12px}
.cat-icon-dot{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cat-icon-dot svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2}
.cat-row-name{font-size:13.5px;font-weight:600;color:var(--fg)}
.cat-row-slug{font-size:11px;color:var(--fg3);margin-top:2px}

/* ── ICON PICKER ── */
.icon-picker{display:grid;grid-template-columns:repeat(auto-fill,minmax(42px,1fr));gap:7px;padding:12px;background:#0d0d0d;border:1px solid var(--border);border-radius:13px;max-height:200px;overflow-y:auto}
.icon-opt{width:42px;height:42px;border-radius:9px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:1px solid transparent;transition:.15s;background:transparent;color:var(--fg3)}
.icon-opt svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:2}
.icon-opt:hover{border-color:var(--border2);color:var(--fg)}
.icon-opt.selected{border-color:var(--red-border);background:var(--red-dim);color:var(--red)}

/* ── THEMES ── */
.themes-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px}
.theme-card{border-radius:14px;overflow:hidden;border:2px solid var(--border);cursor:pointer;transition:.2s;position:relative}
.theme-card:hover{border-color:var(--border2);transform:translateY(-2px)}
.theme-card.selected{border-color:var(--red);box-shadow:0 0 0 3px rgba(237,28,36,.15)}
.theme-strip{height:7px}
.theme-body{padding:10px 12px}
.theme-name{font-size:12.5px;font-weight:700}
.theme-price{font-size:11px;color:var(--fg3);margin-top:2px}
.theme-check{position:absolute;top:7px;left:7px;width:20px;height:20px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;opacity:0;transition:.2s}
.theme-check svg{width:11px;height:11px;stroke:#fff;fill:none;stroke-width:3}
.theme-card.selected .theme-check{opacity:1}

/* ── MODAL ── */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:9000;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px}
.modal-bg.open{display:flex}
.modal{background:#111;border:1px solid var(--border);border-radius:22px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;padding:26px}
.modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.modal-head h3{font-size:1.05rem;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--fg)}
.modal-head h3 svg{width:17px;height:17px;stroke:var(--red);fill:none;stroke-width:2}
.modal-close{width:32px;height:32px;border-radius:50%;background:var(--bg3);border:1px solid var(--border);color:var(--fg3);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
.modal-close:hover{color:var(--red);border-color:var(--red-border)}
.modal-close svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5}

/* ── IMAGE UPLOAD ── */
.img-preview-wrap{position:relative;width:100%;height:130px;border-radius:12px;overflow:hidden;background:#0d0d0d;border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:.2s}
.img-preview-wrap:hover{border-color:var(--red)}
.img-preview-wrap img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.img-preview-label{display:flex;flex-direction:column;align-items:center;gap:6px;color:var(--fg3);font-size:12.5px;font-weight:600;z-index:1}
.img-preview-label svg{width:26px;height:26px;stroke:currentColor;fill:none;stroke-width:1.5}
.img-preview-wrap.has-img .img-preview-label{display:none}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(80px);background:#111;border:1px solid var(--border);color:var(--fg);padding:12px 22px;border-radius:50px;font-size:13.5px;font-weight:600;z-index:9999;transition:.3s cubic-bezier(.175,.885,.32,1.275);white-space:nowrap;backdrop-filter:blur(20px)}
.toast.show{transform:translateX(-50%) translateY(0)}
.toast.success{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:var(--green)}
.toast.error{background:var(--red-dim);border-color:var(--red-border);color:var(--red)}
</style>
</head>
<body>

<!-- ── TOPBAR ── -->
<div class="topbar">
  <a href="index.php" class="topbar-logo">
    <div class="logo-mark">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    <span class="logo-text">Kozhen Studio</span>
  </a>
  <div class="topbar-right">
    <a href="<?php echo $menu_url; ?>" target="_blank" class="t-btn t-btn-primary">
      <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      معاينة المنيو
    </a>
    <a href="/subscribe" class="t-btn t-btn-ghost">
      <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </a>
    <a href="/logout" class="t-btn t-btn-danger">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </div>
</div>

<div class="layout">
  <!-- ── SIDEBAR ── -->
  <aside class="sidebar">
    <div class="user-card">
      <div class="user-card-name"><?php echo htmlspecialchars($user['restaurant_name']); ?></div>
      <div class="user-card-handle">@<?php echo htmlspecialchars($user['username']); ?></div>
    </div>
    <div class="sidebar-section">القائمة</div>
    <button class="nav-item active" id="nav-overview" onclick="showSection('overview')">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      نظرة عامة
    </button>
    <button class="nav-item" id="nav-products" onclick="showSection('products')">
      <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      المنتجات
    </button>
    <button class="nav-item" id="nav-categories" onclick="showSection('categories')">
      <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      الأقسام
    </button>
    <button class="nav-item" id="nav-themes" onclick="showSection('themes')">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
      الثيمات
    </button>
    <button class="nav-item" id="nav-splash" onclick="showSection('splash')">
      <svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
      الصفحة الترحيبية
    </button>
    <div class="sidebar-section">الحساب</div>
    <a href="/subscribe" class="nav-item">
      <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      الاشتراك
    </a>
    <a href="<?php echo $menu_url; ?>" target="_blank" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      معاينة المنيو
    </a>
    <a href="/logout" class="nav-item danger">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      تسجيل الخروج
    </a>
  </aside>

  <!-- ── MAIN ── -->
  <main class="main">

    <?php if ($subscription['plan'] == 'trial'): ?>
    <div class="alert <?php echo $subscription['days_left'] <= 7 ? 'alert-warning' : 'alert-success'; ?>">
      <svg viewBox="0 0 24 24"><?php echo $subscription['days_left'] <= 7 ? '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>' : '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'; ?></svg>
      الفترة التجريبية — متبقي <strong style="margin:0 4px"><?php echo $subscription['days_left']; ?> يوم</strong>
      <?php if ($subscription['days_left'] <= 7): ?> — <a href="/subscribe">اشترك الآن</a><?php endif; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-success">
      <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      اشتراك <?php echo $subscription['plan'] == 'basic' ? 'أساسي' : 'احترافي'; ?> — متبقي <strong style="margin:0 4px"><?php echo $subscription['days_left']; ?> يوم</strong>
    </div>
    <?php endif; ?>

    <div class="link-box">
      <div>
        <div class="link-box-label">
          <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          رابط منيوك
        </div>
        <div class="link-box-url"><?php echo $menu_url; ?></div>
      </div>
      <div class="link-box-actions">
        <button class="t-btn t-btn-ghost" onclick="copyLink()">
          <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          نسخ
        </button>
        <a href="<?php echo $menu_url; ?>" target="_blank" class="t-btn t-btn-primary">
          <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          فتح
        </a>
      </div>
    </div>

    <!-- ══ OVERVIEW ══ -->
    <div id="section-overview">
      <div class="stats-row">
        <div class="stat">
          <div class="stat-label">
            <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
            المنتجات
          </div>
          <div class="stat-value"><?php echo count($products); ?></div>
          <div class="stat-sub">منتج في المنيو</div>
        </div>
        <div class="stat">
          <div class="stat-label">
            <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            الأقسام
          </div>
          <div class="stat-value"><?php echo count($cats); ?></div>
          <div class="stat-sub">قسم مُضاف</div>
        </div>
        <div class="stat">
          <div class="stat-label">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            الاشتراك
          </div>
          <div class="stat-value"><?php echo $subscription['days_left']; ?></div>
          <div class="stat-sub">يوم متبقي</div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/></svg>
            أحدث المنتجات
          </h2>
          <button class="t-btn t-btn-ghost" onclick="showSection('products')">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            إضافة
          </button>
        </div>
        <?php if (empty($products)): ?>
        <div class="empty-state">
          <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          لا يوجد منتجات بعد
        </div>
        <?php else: ?>
        <table class="prod-table">
          <thead><tr><th>المنتج</th><th>السعر</th><th>القسم</th><th></th></tr></thead>
          <tbody>
            <?php foreach(array_slice($products,0,5) as $p):
              $catObj = array_filter($cats, fn($c)=>$c['slug']==$p['category']);
              $catObj = reset($catObj);
              $catName = $catObj ? $catObj['name'] : $p['category'];
            ?>
            <tr id="pr-<?php echo $p['id']; ?>">
              <td><div style="display:flex;align-items:center;gap:12px">
                <?php if($p['image_url']): ?>
                <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="prod-img" id="pimg-<?php echo $p['id']; ?>">
                <?php else: ?>
                <div class="prod-img-placeholder" id="pimg-<?php echo $p['id']; ?>"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                <?php endif; ?>
                <div>
                  <div class="prod-name" id="pname-<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></div>
                  <div class="prod-desc" id="pdesc-<?php echo $p['id']; ?>"><?php echo htmlspecialchars(substr($p['description'],0,40)); ?><?php echo strlen($p['description'])>40?'...':''; ?></div>
                </div>
              </div></td>
              <td><span class="prod-price" id="pprice-<?php echo $p['id']; ?>"><?php echo number_format($p['price'],2); ?> ر.س</span></td>
              <td><span class="badge badge-cat"><?php echo htmlspecialchars($catName); ?></span></td>
              <td><div class="btn-actions">
                <button class="btn-icon btn-edit" onclick="openEdit(<?php echo $p['id']; ?>)"><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                <button class="btn-icon btn-del" onclick="delProduct(<?php echo $p['id']; ?>)"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
              </div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- ══ PRODUCTS ══ -->
    <div id="section-products" style="display:none">
      <div class="card">
        <div class="card-head"><h2><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> إضافة منتج جديد</h2></div>
        <form method="POST" action="/add-product" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="form-group form-full">
              <label><svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> اسم المنتج</label>
              <input type="text" name="name" placeholder="برجر الفحم الكلاسيكي" required>
            </div>
            <div class="form-group form-full">
              <label><svg viewBox="0 0 24 24"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg> الوصف</label>
              <textarea name="description" placeholder="وصف مختصر..." rows="2"></textarea>
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> السعر</label>
              <input type="number" name="price" step="0.01" placeholder="12.99" required>
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> القسم</label>
              <select name="category">
                <?php foreach($cats as $c): ?>
                <option value="<?php echo htmlspecialchars($c['slug']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group form-full">
              <label><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg> صورة المنتج</label>
              <input type="file" name="image" accept="image/*">
            </div>
          </div>
          <button type="submit" class="btn-submit"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> إضافة المنتج</button>
        </form>
      </div>

      <div class="card">
        <div class="card-head">
          <h2><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg> قائمة المنتجات <span style="font-size:.8rem;color:var(--fg3);font-weight:500">(<?php echo count($products); ?>)</span></h2>
        </div>
        <?php if (empty($products)): ?>
        <div class="empty-state"><svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>أضف أول منتج لمطعمك</div>
        <?php else: ?>
        <table class="prod-table">
          <thead><tr><th>المنتج</th><th>السعر</th><th>القسم</th><th></th></tr></thead>
          <tbody>
            <?php foreach($products as $p):
              $catObj = array_filter($cats, fn($c)=>$c['slug']==$p['category']);
              $catObj = reset($catObj);
              $catName = $catObj ? $catObj['name'] : $p['category'];
            ?>
            <tr id="pr-<?php echo $p['id']; ?>">
              <td><div style="display:flex;align-items:center;gap:12px">
                <?php if($p['image_url']): ?>
                <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="prod-img" id="pimg-<?php echo $p['id']; ?>">
                <?php else: ?>
                <div class="prod-img-placeholder" id="pimg-<?php echo $p['id']; ?>"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                <?php endif; ?>
                <div>
                  <div class="prod-name" id="pname-<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></div>
                  <div class="prod-desc" id="pdesc-<?php echo $p['id']; ?>"><?php echo htmlspecialchars(substr($p['description'],0,50)); ?></div>
                </div>
              </div></td>
              <td><span class="prod-price" id="pprice-<?php echo $p['id']; ?>"><?php echo number_format($p['price'],2); ?> ر.س</span></td>
              <td><span class="badge badge-cat"><?php echo htmlspecialchars($catName); ?></span></td>
              <td><div class="btn-actions">
                <button class="btn-icon btn-edit" onclick="openEdit(<?php echo $p['id']; ?>)"><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                <button class="btn-icon btn-del" onclick="delProduct(<?php echo $p['id']; ?>)"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
              </div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- ══ CATEGORIES ══ -->
    <div id="section-categories" style="display:none">
      <div class="card">
        <div class="card-head"><h2><svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> أقسام المنيو</h2></div>
        <?php if (!empty($cats)): ?>
        <div class="cats-list">
          <?php foreach($cats as $c): ?>
          <div class="cat-row" id="cat-<?php echo $c['id']; ?>">
            <div class="cat-row-info">
              <div class="cat-icon-dot" style="background:<?php echo $c['color']; ?>22;color:<?php echo $c['color']; ?>">
                <?php echo getFoodIconSvg($c['icon'], 18); ?>
              </div>
              <div>
                <div class="cat-row-name"><?php echo htmlspecialchars($c['name']); ?></div>
                <div class="cat-row-slug"><?php echo htmlspecialchars(FOOD_ICONS[$c['icon']]['label'] ?? $c['icon']); ?></div>
              </div>
            </div>
            <button class="btn-icon btn-del" onclick="delCat(<?php echo $c['id']; ?>)"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="card-head" style="margin-bottom:16px;margin-top:<?php echo !empty($cats)?'20px':'0'; ?>">
          <h2 style="font-size:.88rem"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> إضافة قسم جديد</h2>
        </div>
        <form method="POST" action="/add-category" id="form-addcat">
          <input type="hidden" name="icon" id="chosen-icon" value="utensils">
          <input type="hidden" name="color" id="chosen-color" value="#ED1C24">
          <div class="form-grid" style="margin-bottom:16px">
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> اسم القسم</label>
              <input type="text" name="name" placeholder="مثال: مشويات" required>
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> اللون</label>
              <input type="color" id="cat-color-pick" value="#ED1C24" onchange="document.getElementById('chosen-color').value=this.value;updateIconPreviewColor(this.value)" style="height:44px;padding:4px 8px;cursor:pointer">
            </div>
          </div>

          <!-- ── FOOD ICON PICKER ── -->
          <div class="form-group form-full" style="margin-bottom:16px">
            <label style="margin-bottom:10px">
              <svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/></svg>
              اختر أيقونة القسم
            </label>

            <!-- Selected preview -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
              <div id="icon-preview-dot" style="width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#ED1C2422;color:#ED1C24;flex-shrink:0">
                <?php echo getFoodIconSvg('utensils', 22); ?>
              </div>
              <span id="icon-preview-label" style="font-size:13px;font-weight:600;color:var(--fg)">أدوات</span>
            </div>

            <!-- Group tabs -->
            <div style="display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap">
              <?php foreach(ICON_GROUPS as $gkey => $glabel): ?>
              <button type="button" class="icon-group-tab <?php echo $gkey==='main'?'active':''; ?>" onclick="showIconGroup('<?php echo $gkey; ?>', this)" style="padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;font-family:inherit;border:1px solid var(--border);background:<?php echo $gkey==='main'?'var(--red)':'var(--bg3)'; ?>;color:<?php echo $gkey==='main'?'#fff':'var(--fg2)'; ?>;cursor:pointer;transition:.15s">
                <?php echo $glabel; ?>
              </button>
              <?php endforeach; ?>
            </div>

            <!-- Icon grids per group -->
            <?php foreach(ICON_GROUPS as $gkey => $glabel): ?>
            <div class="icon-group-panel" id="icongroup-<?php echo $gkey; ?>" style="display:<?php echo $gkey==='main'?'grid':'none'; ?>;grid-template-columns:repeat(auto-fill,minmax(64px,1fr));gap:8px;padding:14px;background:#0d0d0d;border:1px solid var(--border);border-radius:13px">
              <?php foreach(FOOD_ICONS as $ikey => $idata): if ($idata['group'] !== $gkey) continue; ?>
              <button type="button"
                class="food-icon-opt <?php echo $ikey==='utensils'?'selected':''; ?>"
                data-icon="<?php echo $ikey; ?>"
                data-label="<?php echo htmlspecialchars($idata['label']); ?>"
                onclick="selectIcon('<?php echo $ikey; ?>', '<?php echo htmlspecialchars($idata['label']); ?>', this)"
                title="<?php echo htmlspecialchars($idata['label']); ?>"
                style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:10px 6px;border-radius:10px;border:1.5px solid <?php echo $ikey==='utensils'?'var(--red-border)':'transparent'; ?>;background:<?php echo $ikey==='utensils'?'var(--red-dim)':'transparent'; ?>;color:<?php echo $ikey==='utensils'?'var(--red)':'var(--fg3)'; ?>;cursor:pointer;transition:.15s;font-family:inherit">
                <?php echo getFoodIconSvg($ikey, 28); ?>
                <span style="font-size:10px;font-weight:600;text-align:center;line-height:1.2"><?php echo htmlspecialchars($idata['label']); ?></span>
              </button>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>

          <button type="submit" class="btn-submit"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> إضافة القسم</button>
        </form>
      </div>
    </div>

    <!-- ══ SPLASH ══ -->
    <div id="section-splash" style="display:none">
      <div class="card">
        <div class="card-head"><h2><svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg> الصفحة الترحيبية للعميل</h2></div>
        <p style="font-size:13px;color:var(--fg2);margin-bottom:20px;line-height:1.7">عندما يفتح العميل رابط منيوك، يشوف أول شي صفحة ترحيبية فيها فيديو خلفية + أيقونات تواصل اجتماعي + أزرار اللغة.</p>

        <form id="splash-form" enctype="multipart/form-data">
          <!-- Video Upload -->
          <div class="form-group form-full" style="margin-bottom:18px">
            <label><svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg> فيديو الخلفية (حجم أقصى 100MB)</label>
            <div class="img-preview-wrap" id="video-upload-wrap" style="height:160px;cursor:pointer" onclick="document.getElementById('video-input').click()">
              <?php if (!empty($user['video_url'])): ?>
              <video src="/<?php echo htmlspecialchars($user['video_url']); ?>" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:12px" muted playsinline id="video-preview-el"></video>
              <div class="img-preview-label" id="video-label" style="display:none">
              <?php else: ?>
              <div class="img-preview-label" id="video-label">
              <?php endif; ?>
                <svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                <span>اضغط لرفع الفيديو</span>
                <span style="font-size:11px;color:var(--fg3)">MP4, MOV, WebM</span>
              </div>
            </div>
            <input type="file" name="video" id="video-input" accept="video/mp4,video/mov,video/webm,video/quicktime" style="display:none" onchange="previewVideo(this)">
          </div>

          <!-- Social Links -->
          <div style="font-size:11px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:12px">روابط التواصل الاجتماعي</div>
          <div class="form-grid">
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg> انستقرام</label>
              <input type="url" name="social_instagram" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($user['social_instagram']??''); ?>">
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg> تيك توك</label>
              <input type="url" name="social_tiktok" placeholder="https://tiktok.com/..." value="<?php echo htmlspecialchars($user['social_tiktok']??''); ?>">
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg> سناب شات</label>
              <input type="url" name="social_snapchat" placeholder="https://snapchat.com/..." value="<?php echo htmlspecialchars($user['social_snapchat']??''); ?>">
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> الموقع (خرائط)</label>
              <input type="url" name="social_location" placeholder="https://maps.google.com/..." value="<?php echo htmlspecialchars($user['social_location']??''); ?>">
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.1 19.79 19.79 0 0 1 1.61 4.49 2 2 0 0 1 3.6 2.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91A16 16 0 0 0 14.09 16l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.31v-.39z"/></svg> واتساب</label>
              <input type="url" name="social_whatsapp" placeholder="https://wa.me/964..." value="<?php echo htmlspecialchars($user['social_whatsapp']??''); ?>">
            </div>
            <div class="form-group">
              <label><svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg> فيسبوك</label>
              <input type="url" name="social_facebook" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($user['social_facebook']??''); ?>">
            </div>
          </div>

          <button type="submit" class="btn-submit" style="margin-top:18px">
            <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            حفظ الإعدادات
          </button>
        </form>
      </div>
    </div>

    <!-- ══ THEMES ══ -->
    <div id="section-themes" style="display:none">
      <!-- Hero Image Upload -->
      <div class="card" style="margin-bottom:16px">
        <div class="card-head"><h2><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg> صورة الغلاف (Hero Image)</h2></div>
        <div style="padding:20px">
          <p style="font-size:13px;color:var(--fg2);margin-bottom:16px;line-height:1.7">ارفع صورة تظهر كغلاف رئيسي في صفحة المنيو. تدعم JPG، PNG، WebP (حد أقصى 10MB).</p>
          <?php if (!empty($user['hero_image'])): ?>
          <div style="position:relative;margin-bottom:16px;border-radius:14px;overflow:hidden;max-height:200px">
            <img src="/<?php echo htmlspecialchars($user['hero_image']); ?>" style="width:100%;object-fit:cover;max-height:200px;display:block" id="hero-preview-img">
            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);display:flex;align-items:flex-end;padding:14px">
              <span style="color:#fff;font-size:12px;font-weight:700;opacity:.8">الصورة الحالية</span>
            </div>
          </div>
          <?php else: ?>
          <div id="hero-preview-wrap" style="display:none;margin-bottom:16px;border-radius:14px;overflow:hidden;max-height:200px">
            <img id="hero-preview-img" src="" style="width:100%;object-fit:cover;max-height:200px;display:block">
          </div>
          <?php endif; ?>
          <div onclick="document.getElementById('hero-file').click()" id="hero-drop-zone" style="border:2px dashed var(--border);border-radius:14px;padding:28px;text-align:center;cursor:pointer;transition:.2s;background:var(--bg)">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="var(--fg3)" fill="none" stroke-width="1.5" style="margin:0 auto 10px"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <p style="font-size:13px;color:var(--fg2);font-weight:600" id="hero-drop-label">اضغط لاختيار الصورة</p>
            <p style="font-size:11px;color:var(--fg3);margin-top:4px">JPG · PNG · WebP · حد 10MB</p>
          </div>
          <input type="file" id="hero-file" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="heroFileSelected(this)">
          <button id="btn-hero-upload" onclick="uploadHeroImage()" style="display:none;margin-top:12px;width:100%;padding:12px;border-radius:12px;background:var(--red);color:#fff;border:none;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s">
            <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" fill="none" stroke-width="2" style="vertical-align:middle;margin-left:6px"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
            رفع الصورة
          </button>
          <div id="hero-msg" style="margin-top:10px;font-size:13px;font-weight:600;text-align:center"></div>
        </div>
      </div>

      <!-- Theme Grid -->
      <div class="card">
        <div class="card-head"><h2><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><line x1="2" y1="12" x2="22" y2="12"/></svg> اختر ثيم منيوك</h2></div>
        <div class="themes-grid">
          <?php
          $themeInfo = [
            'dark'   => ['strip'=>'linear-gradient(90deg,#1a1a2e,#e94560)','bg'=>'#12121f','text'=>'#e94560','label'=>'Dark Luxury'],
            'light'  => ['strip'=>'linear-gradient(90deg,#f0fdf4,#e11d48)','bg'=>'#f8fafc','text'=>'#e11d48','label'=>'Light Fresh'],
            'street' => ['strip'=>'linear-gradient(90deg,#1c1c1c,#f59e0b)','bg'=>'#1c1c1c','text'=>'#f59e0b','label'=>'Street Food'],
            'fine'   => ['strip'=>'linear-gradient(90deg,#0d0d0d,#ef4444)','bg'=>'#0d0d0d','text'=>'#ef4444','label'=>'Fine Dining'],
            'cafe'   => ['strip'=>'linear-gradient(90deg,#fdf6e3,#795548)','bg'=>'#fdf6e3','text'=>'#795548','label'=>'Cafe Style'],
          ];
          foreach($themes as $t):
            $info = $themeInfo[$t['name']] ?? ['strip'=>'linear-gradient(90deg,#333,#666)','bg'=>'#222','text'=>'#fff','label'=>$t['display_name']];
            $selected = $user['selected_theme'] == $t['name'];
          ?>
          <div class="theme-card <?php echo $selected?'selected':''; ?>" onclick="selectTheme('<?php echo $t['name']; ?>')">
            <div class="theme-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div class="theme-strip" style="background:<?php echo $info['strip']; ?>"></div>
            <div class="theme-body" style="background:<?php echo $info['bg']; ?>">
              <div class="theme-name" style="color:<?php echo $info['text']; ?>"><?php echo $info['label']; ?></div>
              <div class="theme-price"><?php echo $t['price_monthly']; ?> $/شهر</div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- ══ EDIT MODAL ══ -->
<div class="modal-bg" id="edit-modal">
  <div class="modal">
    <div class="modal-head">
      <h3><svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> تعديل المنتج</h3>
      <button class="modal-close" onclick="closeEdit()"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form id="edit-form" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-grid">
        <div class="form-group form-full">
          <div class="img-preview-wrap" id="edit-img-wrap" onclick="document.getElementById('edit-image').click()">
            <img id="edit-img-preview" src="" style="display:none">
            <div class="img-preview-label">
              <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>اضغط لتغيير الصورة</span>
            </div>
          </div>
          <input type="file" name="image" id="edit-image" accept="image/*" style="display:none" onchange="previewEditImg(this)">
        </div>
        <div class="form-group form-full">
          <label><svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> اسم المنتج</label>
          <input type="text" name="name" id="edit-name" placeholder="اسم المنتج" required>
        </div>
        <div class="form-group form-full">
          <label><svg viewBox="0 0 24 24"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg> الوصف</label>
          <textarea name="description" id="edit-desc" rows="2" placeholder="الوصف..."></textarea>
        </div>
        <div class="form-group">
          <label><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> السعر</label>
          <input type="number" name="price" id="edit-price" step="0.01" required>
        </div>
        <div class="form-group">
          <label><svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> القسم</label>
          <select name="category" id="edit-cat">
            <?php foreach($cats as $c): ?>
            <option value="<?php echo htmlspecialchars($c['slug']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:18px">
        <button type="submit" class="btn-submit" style="flex:1;justify-content:center"><svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> حفظ التعديلات</button>
        <button type="button" class="t-btn t-btn-ghost" onclick="closeEdit()">إلغاء</button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const PRODUCTS = <?php echo json_encode(array_column($products, null, 'id'), JSON_UNESCAPED_UNICODE); ?>;

function selectIcon(key, label, btn) {
  document.getElementById('chosen-icon').value = key;
  document.getElementById('icon-preview-label').textContent = label;
  const color = document.getElementById('cat-color-pick').value;
  const dot = document.getElementById('icon-preview-dot');
  dot.innerHTML = btn.querySelector('svg').outerHTML;
  document.querySelectorAll('.food-icon-opt').forEach(b => {
    b.classList.remove('selected');
    b.style.border = '1.5px solid transparent';
    b.style.background = 'transparent';
    b.style.color = 'var(--fg3)';
  });
  btn.classList.add('selected');
  btn.style.border = '1.5px solid var(--red-border)';
  btn.style.background = 'var(--red-dim)';
  btn.style.color = 'var(--red)';
}

function showIconGroup(group, tab) {
  document.querySelectorAll('.icon-group-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.icon-group-tab').forEach(t => {
    t.style.background = 'var(--bg3)'; t.style.color = 'var(--fg2)';
    t.style.borderColor = 'var(--border)';
  });
  const panel = document.getElementById('icongroup-' + group);
  if (panel) panel.style.display = 'grid';
  tab.style.background = 'var(--red)'; tab.style.color = '#fff';
}

function updateIconPreviewColor(color) {
  const dot = document.getElementById('icon-preview-dot');
  dot.style.background = color + '22';
  dot.style.color = color;
}

function showSection(name) {
  ['overview','products','categories','themes','splash'].forEach(s => {
    document.getElementById('section-'+s).style.display = s===name?'block':'none';
  });
  document.querySelectorAll('.nav-item').forEach(el=>el.classList.remove('active'));
  const n = document.getElementById('nav-'+name);
  if(n) n.classList.add('active');
}

function previewVideo(input) {
  if (!input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  let vid = document.getElementById('video-preview-el');
  const wrap = document.getElementById('video-upload-wrap');
  const label = document.getElementById('video-label');
  if (!vid) {
    vid = document.createElement('video');
    vid.id = 'video-preview-el';
    vid.muted = true;
    vid.setAttribute('playsinline','');
    vid.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:12px';
    wrap.prepend(vid);
  }
  vid.src = url;
  label.style.display = 'none';
  wrap.classList.add('has-img');
}

document.getElementById('splash-form')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = this.querySelector('[type=submit]');
  const origHTML = btn.innerHTML;
  btn.textContent = '...جاري الحفظ'; btn.disabled = true;
  try {
    const fd = new FormData(this);
    const r = await fetch('/update-profile', {method:'POST', body: fd});
    const d = await r.json();
    if (d.success) {
      toast('تم حفظ الإعدادات بنجاح');
      if (d.video_url) {
        let vid = document.getElementById('video-preview-el');
        const wrap = document.getElementById('video-upload-wrap');
        const label = document.getElementById('video-label');
        if (!vid) {
          vid = document.createElement('video');
          vid.id = 'video-preview-el';
          vid.muted = true;
          vid.setAttribute('playsinline','');
          vid.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:12px';
          wrap.prepend(vid);
        }
        vid.src = '/' + d.video_url;
        label.style.display = 'none';
        wrap.classList.add('has-img');
      }
      document.getElementById('video-input').value = '';
    } else {
      toast(d.msg || 'فشل الحفظ', 'error');
    }
  } catch(err) {
    toast('حدث خطأ', 'error');
  }
  btn.innerHTML = origHTML; btn.disabled = false;
});

function toast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

function copyLink() {
  navigator.clipboard.writeText('<?php echo $menu_url; ?>');
  toast('تم نسخ الرابط');
}

function selectTheme(theme) {
  fetch('/update-theme',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'theme='+theme})
    .then(()=>{toast('تم تغيير الثيم');setTimeout(()=>location.reload(),800);});
}

let heroFileData = null;
function heroFileSelected(input) {
  if (!input.files || !input.files[0]) return;
  heroFileData = input.files[0];
  const wrap  = document.getElementById('hero-preview-wrap');
  const img   = document.getElementById('hero-preview-img');
  const label = document.getElementById('hero-drop-label');
  const btn   = document.getElementById('btn-hero-upload');
  if (img) {
    img.src = URL.createObjectURL(heroFileData);
    if (wrap) wrap.style.display = '';
  }
  if (label) label.textContent = heroFileData.name;
  if (btn) btn.style.display = '';
}

async function uploadHeroImage() {
  if (!heroFileData) return;
  const btn = document.getElementById('btn-hero-upload');
  const msg = document.getElementById('hero-msg');
  btn.disabled = true;
  btn.textContent = 'جاري الرفع...';
  msg.textContent = '';
  const fd = new FormData();
  fd.append('hero_image', heroFileData);
  try {
    const r = await fetch('/update-profile', {method:'POST', body: fd});
    const d = await r.json();
    if (d.success) {
      msg.style.color = '#4ade80';
      msg.textContent = 'تم رفع الصورة بنجاح ✓';
      btn.textContent = 'تم الرفع ✓';
      setTimeout(() => location.reload(), 1000);
    } else {
      msg.style.color = '#f87171';
      msg.textContent = d.msg || 'فشل الرفع';
      btn.disabled = false;
      btn.textContent = 'رفع الصورة';
    }
  } catch(e) {
    msg.style.color = '#f87171';
    msg.textContent = 'حدث خطأ في الاتصال';
    btn.disabled = false;
    btn.textContent = 'رفع الصورة';
  }
}

function delProduct(id) {
  if (!confirm('هل أنت متأكد من حذف هذا المنتج؟')) return;
  fetch('/delete-product',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id})
    .then(r=>r.json()).then(d=>{
      if(d.success){document.querySelectorAll('#pr-'+id).forEach(el=>el.remove());toast('تم حذف المنتج');}
      else toast('فشل الحذف','error');
    });
}

function delCat(id) {
  if (!confirm('حذف هذا القسم؟')) return;
  fetch('/delete-category',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id})
    .then(r=>r.json()).then(d=>{
      if(d.success){document.getElementById('cat-'+id)?.remove();toast('تم حذف القسم');}
      else toast('فشل الحذف','error');
    });
}

function openEdit(id) {
  const p = PRODUCTS[id];
  if (!p) return;
  document.getElementById('edit-id').value    = id;
  document.getElementById('edit-name').value  = p.name;
  document.getElementById('edit-desc').value  = p.description || '';
  document.getElementById('edit-price').value = p.price;
  document.getElementById('edit-cat').value   = p.category;
  const wrap = document.getElementById('edit-img-wrap');
  const img  = document.getElementById('edit-img-preview');
  if (p.image_url) {
    img.src = p.image_url; img.style.display = 'block'; wrap.classList.add('has-img');
  } else {
    img.style.display = 'none'; wrap.classList.remove('has-img');
  }
  document.getElementById('edit-image').value = '';
  document.getElementById('edit-modal').classList.add('open');
}

function closeEdit() {
  document.getElementById('edit-modal').classList.remove('open');
}

document.getElementById('edit-modal').addEventListener('click', function(e){
  if(e.target===this) closeEdit();
});

function previewEditImg(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById('edit-img-preview');
    const wrap = document.getElementById('edit-img-wrap');
    img.src = e.target.result; img.style.display = 'block'; wrap.classList.add('has-img');
  };
  reader.readAsDataURL(input.files[0]);
}

document.getElementById('edit-form').addEventListener('submit', async function(e){
  e.preventDefault();
  const btn = this.querySelector('[type=submit]');
  btn.textContent = '...جاري الحفظ'; btn.disabled = true;
  const fd = new FormData(this);
  try {
    const r = await fetch('/edit-product', {method:'POST', body:fd});
    const d = await r.json();
    if (d.success) {
      const id = parseInt(fd.get('id'));
      document.querySelectorAll('#pname-'+id).forEach(el=>el.textContent=fd.get('name'));
      document.querySelectorAll('#pdesc-'+id).forEach(el=>el.textContent=(fd.get('description')||'').substring(0,50));
      document.querySelectorAll('#pprice-'+id).forEach(el=>el.textContent=parseFloat(fd.get('price')).toFixed(2)+' ر.س');
      if(d.image_url){
        document.querySelectorAll('#pimg-'+id).forEach(el=>{
          el.outerHTML=`<img src="${d.image_url}" class="prod-img" id="pimg-${id}">`;
        });
      }
      closeEdit(); toast('تم حفظ التعديلات');
    } else { toast(d.msg || 'فشل الحفظ','error'); }
  } catch(err) { toast('حدث خطأ','error'); }
  btn.innerHTML = '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#fff" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> حفظ التعديلات';
  btn.disabled = false;
});
</script>
</body>
</html>
