<?php
require_once 'config/config.php';

// ── Admin logout ─────────────────────────────────────────────
if (isset($_GET['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in']);
    header("Location: /admin"); exit();
}

// ── Admin login ──────────────────────────────────────────────
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: /admin"); exit();
    } else {
        $login_error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}

$is_admin = $_SESSION['admin_logged_in'] ?? false;

// ── Fetch data for dashboard ─────────────────────────────────
if ($is_admin) {
    $db    = Database::getConnection();
    $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $today = date('Y-m-d');

    $stats = ['total'=>0, 'active'=>0, 'trial'=>0, 'expired'=>0, 'disabled'=>0];
    foreach ($users as &$u) {
        $stats['total']++;
        $s = $u['subscription_status'];
        if ($s === 'disabled') { $stats['disabled']++; $u['_status'] = 'disabled'; }
        elseif ($s === 'trial') {
            if ($today > ($u['trial_end']??'')) { $stats['expired']++; $u['_status']='expired'; }
            else { $stats['trial']++; $u['_status']='trial'; }
        } elseif (in_array($s, ['basic','pro'])) {
            if ($today > ($u['subscription_end']??'')) { $stats['expired']++; $u['_status']='expired'; }
            else { $stats['active']++; $u['_status']=$s; }
        } else { $stats['expired']++; $u['_status']='expired'; }
        // days left
        $end = $s==='trial' ? ($u['trial_end']??null) : ($u['subscription_end']??null);
        $u['_end']  = $end;
        $u['_days'] = $end && $today <= $end ? ceil((strtotime($end)-strtotime($today))/86400) : 0;
    }
    unset($u);
    // Subscription requests
    $sub_requests = $db->query("
        SELECT sr.*, u.restaurant_name, u.username, u.email
        FROM subscription_requests sr
        JOIN users u ON sr.user_id = u.id
        ORDER BY sr.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    $pending_count = count(array_filter($sub_requests, fn($r)=>$r['status']==='pending'));
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Kozhen Studio</title>
<style>
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Medium.woff2') format('woff2');font-weight:500}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Bold.woff2') format('woff2');font-weight:700}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0a0a;--bg2:#111;--bg3:#161616;
  --fg:#f5f5f5;--fg2:#aaa;--fg3:#555;
  --border:#222;--border2:#2a2a2a;
  --accent:#fff;
  --green:#22c55e;--yellow:#f59e0b;--blue:#3b82f6;--red:#ef4444;--purple:#a855f7;
}
body{font-family:'GraphikArabic','Tajawal',sans-serif;background:var(--bg);color:var(--fg);min-height:100vh}

/* ── LOGIN ── */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:var(--bg)}
.login-box{width:100%;max-width:380px}
.login-logo{text-align:center;margin-bottom:36px}
.login-logo img{width:56px;height:56px;object-fit:contain;filter:invert(1)}
.login-logo-name{font-size:1.3rem;font-weight:700;color:var(--fg);margin-top:10px;letter-spacing:-.3px}
.login-logo-badge{display:inline-block;font-size:11px;font-weight:700;color:var(--fg3);border:1px solid var(--border);border-radius:50px;padding:3px 12px;margin-top:6px;letter-spacing:1px;text-transform:uppercase}
.login-card{background:var(--bg2);border:1px solid var(--border);border-radius:22px;padding:30px}
.login-title{font-size:1.05rem;font-weight:700;margin-bottom:22px;color:var(--fg)}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:11px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:7px}
.form-group input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--fg);padding:11px 14px;border-radius:11px;font-size:14px;font-family:inherit;outline:none;transition:.2s}
.form-group input:focus{border-color:#444}
.form-group input::placeholder{color:var(--fg3)}
.btn-login{width:100%;background:var(--fg);color:#000;border:none;padding:12px;border-radius:11px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;margin-top:4px}
.btn-login:hover{background:#e0e0e0}
.login-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;padding:11px 14px;border-radius:11px;font-size:13px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.login-error svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}

/* ── TOPBAR ── */
.topbar{background:rgba(10,10,10,.95);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 20px;position:sticky;top:0;z-index:200}
.topbar-left{display:flex;align-items:center;gap:10px}
.topbar-logo{display:flex;align-items:center;gap:9px;text-decoration:none}
.topbar-logo img{width:28px;height:28px;object-fit:contain;filter:invert(1)}
.topbar-logo-name{font-size:.95rem;font-weight:700;color:var(--fg)}
.admin-badge{font-size:10px;font-weight:700;color:#000;background:#fff;border-radius:50px;padding:2px 9px;letter-spacing:.5px;text-transform:uppercase}
.topbar-right{display:flex;align-items:center;gap:8px}
.t-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:50px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid transparent;font-family:inherit;transition:.15s;white-space:nowrap}
.t-btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.t-btn-ghost{background:var(--bg2);border-color:var(--border);color:var(--fg2)}
.t-btn-ghost:hover{background:var(--bg3);color:var(--fg)}

/* ── MAIN ── */
.main{padding:24px 22px;max-width:1200px;margin:0 auto}

/* ── STATS ── */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:24px}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:18px 20px}
.stat-label{font-size:11px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px}
.stat-value{font-size:2.2rem;font-weight:700;line-height:1;letter-spacing:-1.5px}
.stat-sub{font-size:11px;color:var(--fg3);margin-top:5px}
.stat-total .stat-value{color:var(--fg)}
.stat-active .stat-value{color:var(--green)}
.stat-trial .stat-value{color:var(--yellow)}
.stat-expired .stat-value{color:var(--red)}
.stat-disabled .stat-value{color:var(--fg3)}

/* ── TABLE ── */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:18px;overflow:hidden;margin-bottom:20px}
.card-head{padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.card-head h2{font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px}
.card-head h2 svg{width:16px;height:16px;stroke:var(--fg3);fill:none;stroke-width:2}
.search-box{background:var(--bg3);border:1px solid var(--border);color:var(--fg);padding:8px 14px;border-radius:50px;font-size:13px;font-family:inherit;outline:none;width:200px}
.search-box::placeholder{color:var(--fg3)}
.search-box:focus{border-color:#444}
.tbl{width:100%;border-collapse:collapse}
.tbl th{text-align:right;font-size:10.5px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.6px;padding:10px 16px;border-bottom:1px solid var(--border);white-space:nowrap}
.tbl td{padding:13px 16px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;font-size:13.5px}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:rgba(255,255,255,.018)}
.user-cell{display:flex;align-items:center;gap:11px;min-width:0}
.avatar{width:36px;height:36px;border-radius:10px;background:var(--bg3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--fg2);flex-shrink:0;text-transform:uppercase}
.user-name{font-size:13.5px;font-weight:700;color:var(--fg);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px}
.user-handle{font-size:11.5px;color:var(--fg3);margin-top:1px}
.email-cell{font-size:12.5px;color:var(--fg3);white-space:nowrap}
.plan-badge{display:inline-flex;align-items:center;padding:4px 11px;border-radius:50px;font-size:11.5px;font-weight:700;white-space:nowrap}
.plan-trial{background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.2)}
.plan-basic{background:rgba(59,130,246,.1);color:#60a5fa;border:1px solid rgba(59,130,246,.2)}
.plan-pro{background:rgba(168,85,247,.1);color:#c084fc;border:1px solid rgba(168,85,247,.2)}
.plan-expired{background:rgba(239,68,68,.08);color:#f87171;border:1px solid rgba(239,68,68,.18)}
.plan-disabled{background:rgba(255,255,255,.04);color:#555;border:1px solid var(--border)}
.days-pill{font-size:12px;font-weight:600;color:var(--fg3);white-space:nowrap}
.days-ok{color:var(--green)}
.days-warn{color:var(--yellow)}
.days-bad{color:var(--red)}
.actions-cell{display:flex;align-items:center;gap:6px;flex-wrap:wrap}

/* ── REQUESTS ── */
.req-pending{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#fbbf24}
.req-approved{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:var(--green)}
.req-rejected{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);color:var(--red)}
.req-badge{display:inline-flex;align-items:center;padding:3px 11px;border-radius:50px;font-size:11.5px;font-weight:700;white-space:nowrap}
.btn-approve{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:var(--green);padding:6px 13px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;white-space:nowrap}
.btn-approve:hover{background:rgba(34,197,94,.22)}
.btn-reject{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;padding:6px 13px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;white-space:nowrap}
.btn-reject:hover{background:rgba(239,68,68,.18)}
.req-plan-sel{background:var(--bg3);border:1px solid var(--border);color:var(--fg);padding:5px 9px;border-radius:8px;font-size:12px;font-family:inherit;outline:none;cursor:pointer}
.req-plan-sel option{background:#111}
.tab-row{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap}
.tab-btn{padding:8px 18px;border-radius:50px;border:1px solid var(--border);background:var(--bg2);color:var(--fg2);font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;display:flex;align-items:center;gap:6px}
.tab-btn.active{background:var(--fg);color:#000;border-color:transparent}
.tab-badge{background:#f59e0b;color:#000;border-radius:50px;padding:1px 8px;font-size:11px;font-weight:800}
.act-select{background:var(--bg3);border:1px solid var(--border);color:var(--fg);padding:6px 10px;border-radius:9px;font-size:12.5px;font-family:inherit;outline:none;cursor:pointer}
.act-select:focus{border-color:#444}
.act-select option{background:#111}
.btn-apply{background:var(--fg);color:#000;border:none;padding:6px 13px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;white-space:nowrap}
.btn-apply:hover{background:#ddd}
.btn-del-user{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;width:32px;height:32px;border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:.15s}
.btn-del-user:hover{background:rgba(239,68,68,.18)}
.btn-del-user svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}
.join-date{font-size:12px;color:var(--fg3);white-space:nowrap}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(80px);background:#111;border:1px solid var(--border);color:var(--fg);padding:12px 22px;border-radius:50px;font-size:13.5px;font-weight:600;z-index:9999;transition:.3s cubic-bezier(.175,.885,.32,1.275);white-space:nowrap;pointer-events:none}
.toast.show{transform:translateX(-50%) translateY(0)}
.toast.ok{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:var(--green)}
.toast.err{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.25);color:#f87171}

@media(max-width:768px){
  .email-cell,.join-date{display:none}
  .search-box{width:140px}
  .actions-cell{flex-direction:column;align-items:flex-start}
}
</style>
</head>
<body>

<?php if (!$is_admin): ?>
<!-- ══ LOGIN ══ -->
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">
      <img src="kozhen-logo-dark.png" alt="Kozhen">
      <div class="login-logo-name">Kozhen Studio</div>
      <span class="login-logo-badge">Admin Panel</span>
    </div>
    <div class="login-card">
      <div class="login-title">تسجيل دخول المشرف</div>
      <?php if ($login_error): ?>
      <div class="login-error">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php echo htmlspecialchars($login_error); ?>
      </div>
      <?php endif; ?>
      <form method="POST" autocomplete="off">
        <input type="hidden" name="admin_login" value="1">
        <div class="form-group">
          <label>اسم المستخدم</label>
          <input type="text" name="username" placeholder="admin" required autofocus autocomplete="username">
        </div>
        <div class="form-group">
          <label>كلمة المرور</label>
          <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn-login">دخول</button>
      </form>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ══ DASHBOARD ══ -->
<div class="topbar">
  <div class="topbar-left">
    <a href="/admin" class="topbar-logo">
      <img src="kozhen-logo-dark.png" alt="K">
      <span class="topbar-logo-name">Kozhen Studio</span>
    </a>
    <span class="admin-badge">ADMIN</span>
  </div>
  <div class="topbar-right">
    <a href="/" class="t-btn t-btn-ghost">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      الرئيسية
    </a>
    <a href="/admin?logout=1" class="t-btn t-btn-ghost">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      خروج
    </a>
  </div>
</div>

<div class="main">

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat stat-total">
      <div class="stat-label">إجمالي العملاء</div>
      <div class="stat-value"><?php echo $stats['total']; ?></div>
      <div class="stat-sub">مسجّل في المنصة</div>
    </div>
    <div class="stat stat-active">
      <div class="stat-label">مشتركون نشطون</div>
      <div class="stat-value"><?php echo $stats['active']; ?></div>
      <div class="stat-sub">basic / pro فعّال</div>
    </div>
    <div class="stat stat-trial">
      <div class="stat-label">تجريبي</div>
      <div class="stat-value"><?php echo $stats['trial']; ?></div>
      <div class="stat-sub">فترة تجريبية</div>
    </div>
    <div class="stat stat-expired">
      <div class="stat-label">منتهي</div>
      <div class="stat-value"><?php echo $stats['expired']; ?></div>
      <div class="stat-sub">انتهى الاشتراك</div>
    </div>
    <div class="stat stat-disabled">
      <div class="stat-label">محظور</div>
      <div class="stat-value"><?php echo $stats['disabled']; ?></div>
      <div class="stat-sub">حساب موقوف</div>
    </div>
  </div>

  <!-- REQUESTS SECTION -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-head">
      <h2>
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        طلبات الاشتراك
        <?php if ($pending_count > 0): ?>
        <span style="background:#f59e0b;color:#000;border-radius:50px;padding:1px 10px;font-size:11px;font-weight:900;margin-right:4px"><?php echo $pending_count; ?> جديد</span>
        <?php endif; ?>
      </h2>
    </div>
    <?php if (empty($sub_requests)): ?>
    <div style="text-align:center;padding:40px 20px;color:var(--fg3)">لا توجد طلبات بعد</div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table class="tbl" id="reqTable">
      <thead>
        <tr>
          <th>العميل</th>
          <th>البريد</th>
          <th>الهاتف</th>
          <th>الباقة المطلوبة</th>
          <th>المدة</th>
          <th>تاريخ الطلب</th>
          <th>الحالة</th>
          <th>إجراء</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sub_requests as $r): ?>
        <?php
          $planLabels = ['basic'=>'Basic','pro'=>'Pro'];
          $durLabels  = [3=>'٣ أشهر',6=>'٦ أشهر',12=>'سنة'];
          $statusLabel = match($r['status']) { 'pending'=>'قيد المراجعة','approved'=>'مقبول','rejected'=>'مرفوض', default=>$r['status'] };
          $statusClass = 'req-'.$r['status'];
        ?>
        <tr id="rrow-<?php echo $r['id']; ?>">
          <td>
            <div class="user-cell">
              <div class="avatar" style="font-size:11px"><?php echo mb_substr($r['restaurant_name'],0,2,'UTF-8'); ?></div>
              <div>
                <div class="user-name"><?php echo htmlspecialchars($r['restaurant_name']); ?></div>
                <div class="user-handle">@<?php echo htmlspecialchars($r['username']); ?></div>
              </div>
            </div>
          </td>
          <td class="email-cell"><?php echo htmlspecialchars($r['email']); ?></td>
          <td style="font-size:13px;direction:ltr;text-align:right"><?php echo $r['phone'] ? htmlspecialchars($r['phone']) : '—'; ?></td>
          <td><span class="plan-badge <?php echo $r['plan']==='pro'?'plan-pro':'plan-basic'; ?>"><?php echo $planLabels[$r['plan']]??$r['plan']; ?></span></td>
          <td style="font-size:13px;color:var(--fg2)"><?php echo $durLabels[(int)$r['duration_months']]??($r['duration_months'].' شهر'); ?></td>
          <td class="join-date"><?php echo substr($r['created_at'],0,10); ?></td>
          <td><span class="req-badge <?php echo $statusClass; ?>" id="rstatus-<?php echo $r['id']; ?>"><?php echo $statusLabel; ?></span></td>
          <td>
            <?php if ($r['status']==='pending'): ?>
            <div class="actions-cell">
              <select class="req-plan-sel" id="rplan-<?php echo $r['id']; ?>">
                <option value="basic" <?php echo $r['plan']==='basic'?'selected':''; ?>>Basic</option>
                <option value="pro"   <?php echo $r['plan']==='pro'  ?'selected':''; ?>>Pro</option>
              </select>
              <select class="req-plan-sel" id="rdur-<?php echo $r['id']; ?>">
                <option value="3"  <?php echo $r['duration_months']==3 ?'selected':''; ?>>٣ أشهر</option>
                <option value="6"  <?php echo $r['duration_months']==6 ?'selected':''; ?>>٦ أشهر</option>
                <option value="12" <?php echo $r['duration_months']==12?'selected':''; ?>>سنة</option>
              </select>
              <button class="btn-approve" onclick="approveReq(<?php echo $r['id']; ?>,<?php echo $r['user_id']; ?>)">قبول</button>
              <button class="btn-reject"  onclick="rejectReq(<?php echo $r['id']; ?>,<?php echo $r['user_id']; ?>)">رفض</button>
            </div>
            <?php else: ?>
            <span style="color:var(--fg3);font-size:12px">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- USERS TABLE -->
  <div class="card">
    <div class="card-head">
      <h2>
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        العملاء (<?php echo $stats['total']; ?>)
      </h2>
      <input class="search-box" type="text" id="searchInput" placeholder="بحث..." oninput="filterTable(this.value)">
    </div>
    <?php if (empty($users)): ?>
    <div style="text-align:center;padding:50px 20px;color:var(--fg3)">لا يوجد عملاء بعد</div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table class="tbl" id="usersTable">
      <thead>
        <tr>
          <th>العميل</th>
          <th>البريد</th>
          <th>الخطة</th>
          <th>المتبقي</th>
          <th>تاريخ الانضمام</th>
          <th>إدارة الخطة</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <?php
          $initials = mb_substr($u['restaurant_name'], 0, 2, 'UTF-8');
          $status   = $u['_status'];
          $badgeClass = match($status) {
            'basic'    => 'plan-basic',
            'pro'      => 'plan-pro',
            'trial'    => 'plan-trial',
            'disabled' => 'plan-disabled',
            default    => 'plan-expired',
          };
          $badgeLabel = match($status) {
            'basic'    => 'Basic',
            'pro'      => 'Pro',
            'trial'    => 'تجريبي',
            'disabled' => 'محظور',
            default    => 'منتهي',
          };
          $daysClass = $u['_days'] > 14 ? 'days-ok' : ($u['_days'] > 5 ? 'days-warn' : 'days-bad');
        ?>
        <tr id="urow-<?php echo $u['id']; ?>" data-search="<?php echo htmlspecialchars(strtolower($u['restaurant_name'].' '.$u['username'].' '.$u['email'])); ?>">
          <td>
            <div class="user-cell">
              <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
              <div>
                <div class="user-name"><?php echo htmlspecialchars($u['restaurant_name']); ?></div>
                <div class="user-handle">@<?php echo htmlspecialchars($u['username']); ?></div>
              </div>
            </div>
          </td>
          <td class="email-cell"><?php echo htmlspecialchars($u['email']); ?></td>
          <td><span class="plan-badge <?php echo $badgeClass; ?>" id="badge-<?php echo $u['id']; ?>"><?php echo $badgeLabel; ?></span></td>
          <td>
            <span class="days-pill <?php echo $u['_days'] > 0 ? $daysClass : 'days-bad'; ?>" id="days-<?php echo $u['id']; ?>">
              <?php echo $u['_days'] > 0 ? $u['_days'].' يوم' : ($u['_end'] ? 'منتهي' : '—'); ?>
            </span>
          </td>
          <td class="join-date"><?php echo substr($u['created_at'],0,10); ?></td>
          <td>
            <div class="actions-cell">
              <select class="act-select" id="plan-<?php echo $u['id']; ?>">
                <option value="trial"    <?php echo $u['subscription_status']==='trial'   ?'selected':''; ?>>تجريبي</option>
                <option value="basic"    <?php echo $u['subscription_status']==='basic'   ?'selected':''; ?>>Basic</option>
                <option value="pro"      <?php echo $u['subscription_status']==='pro'     ?'selected':''; ?>>Pro</option>
                <option value="disabled" <?php echo $u['subscription_status']==='disabled'?'selected':''; ?>>محظور</option>
              </select>
              <select class="act-select" id="dur-<?php echo $u['id']; ?>">
                <option value="30">شهر</option>
                <option value="90">3 أشهر</option>
                <option value="365">سنة</option>
              </select>
              <button class="btn-apply" onclick="applyPlan(<?php echo $u['id']; ?>)">تطبيق</button>
            </div>
          </td>
          <td>
            <button class="btn-del-user" onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars(addslashes($u['restaurant_name'])); ?>')" title="حذف">
              <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /main -->
<?php endif; ?>

<div class="toast" id="toast"></div>

<script>
function toast(msg, type='ok') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

function applyPlan(uid) {
  const plan = document.getElementById('plan-'+uid).value;
  const dur  = document.getElementById('dur-'+uid).value;
  fetch('/admin-action', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=set_plan&user_id='+uid+'&plan='+encodeURIComponent(plan)+'&duration='+dur
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const labels = {trial:'تجريبي',basic:'Basic',pro:'Pro',disabled:'محظور'};
      const classes = {trial:'plan-trial',basic:'plan-basic',pro:'plan-pro',disabled:'plan-disabled'};
      const badge = document.getElementById('badge-'+uid);
      badge.textContent = labels[d.plan] || d.plan;
      badge.className = 'plan-badge ' + (classes[d.plan] || 'plan-expired');
      const daysEl = document.getElementById('days-'+uid);
      if (d.plan === 'disabled') { daysEl.textContent = '—'; daysEl.className='days-pill'; }
      else { daysEl.textContent = dur + ' يوم'; daysEl.className='days-pill days-ok'; }
      toast('تم تحديث الخطة بنجاح');
    } else { toast('حدث خطأ','err'); }
  }).catch(()=>toast('فشل الاتصال','err'));
}

function deleteUser(uid, name) {
  if (!confirm('حذف حساب "' + name + '"؟ لا يمكن التراجع.')) return;
  fetch('/admin-action', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=delete_user&user_id='+uid
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const row = document.getElementById('urow-'+uid);
      if (row) { row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
      toast('تم حذف الحساب');
    } else { toast('فشل الحذف','err'); }
  });
}

function filterTable(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#usersTable tbody tr').forEach(row => {
    row.style.display = row.dataset.search.includes(q) ? '' : 'none';
  });
}

function approveReq(rid, uid) {
  const plan  = document.getElementById('rplan-'+rid)?.value || 'basic';
  const months= document.getElementById('rdur-'+rid)?.value  || '3';
  if (!confirm('قبول الطلب وتفعيل الاشتراك؟')) return;
  fetch('/admin-action', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=approve_request&request_id='+rid+'&user_id='+uid+'&plan='+encodeURIComponent(plan)+'&duration_months='+months
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const s = document.getElementById('rstatus-'+rid);
      if (s) { s.textContent='مقبول'; s.className='req-badge req-approved'; }
      const cell = document.querySelector('#rrow-'+rid+' .actions-cell');
      if (cell) cell.innerHTML = '<span style="color:var(--fg3);font-size:12px">—</span>';
      toast('تم قبول الطلب وتفعيل الاشتراك');
    } else { toast('حدث خطأ','err'); }
  }).catch(()=>toast('فشل الاتصال','err'));
}

function rejectReq(rid, uid) {
  if (!confirm('رفض هذا الطلب؟')) return;
  fetch('/admin-action', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=reject_request&request_id='+rid+'&user_id='+uid
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const s = document.getElementById('rstatus-'+rid);
      if (s) { s.textContent='مرفوض'; s.className='req-badge req-rejected'; }
      const cell = document.querySelector('#rrow-'+rid+' .actions-cell');
      if (cell) cell.innerHTML = '<span style="color:var(--fg3);font-size:12px">—</span>';
      toast('تم رفض الطلب','err');
    } else { toast('حدث خطأ','err'); }
  }).catch(()=>toast('فشل الاتصال','err'));
}
</script>
</body>
</html>
