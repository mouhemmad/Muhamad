<?php
require_once 'config/config.php';
if (!isLoggedIn()) { header("Location: /login"); exit(); }
$user = getUser();
$uid  = $_SESSION['user_id'];
$sub  = checkSubscription($uid);
$db   = Database::getConnection();

$req_stmt = $db->prepare("SELECT * FROM subscription_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
$req_stmt->execute([$uid]);
$last_req    = $req_stmt->fetch(PDO::FETCH_ASSOC);
$is_pending  = $last_req && $last_req['status'] === 'pending';
$is_rejected = $last_req && $last_req['status'] === 'rejected';

$plans = [
  'basic' => ['name'=>'أساسية','color'=>'#6366f1','icon'=>'<path d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
  'pro'   => ['name'=>'احترافية','color'=>'#f59e0b','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
];
$durations = [
  3  => ['label'=>'٣ أشهر',  'badge'=>''],
  6  => ['label'=>'٦ أشهر',  'badge'=>'وفّر 10%'],
  12 => ['label'=>'سنة كاملة','badge'=>'الأفضل قيمة'],
];
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>الاشتراك — Kozhen Studio</title>
<style>
@font-face{font-family:'GA';src:url('/Graphik_Arabic_Medium.woff2') format('woff2');font-weight:400 500;font-display:swap}
@font-face{font-family:'GA';src:url('/Graphik_Arabic_SemiBold.woff2') format('woff2');font-weight:600;font-display:swap}
@font-face{font-family:'GA';src:url('/Graphik_Arabic_Bold.woff2') format('woff2');font-weight:700 900;font-display:swap}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#080808;--bg2:#0f0f0f;--bg3:#161616;
  --fg:#f5f5f5;--fg2:#888;--fg3:#444;
  --border:#1e1e1e;--border2:#2a2a2a;
  --red:#ED1C24;--red-dim:rgba(237,28,36,.1);--red-border:rgba(237,28,36,.3);
}
body{font-family:'GA',sans-serif;background:var(--bg);color:var(--fg);min-height:100vh}

/* TOPBAR */
.topbar{height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 24px;border-bottom:1px solid var(--border);background:rgba(8,8,8,.95);backdrop-filter:blur(20px);position:sticky;top:0;z-index:100}
.topbar-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.topbar-logo img{width:26px;height:26px;border-radius:7px;object-fit:cover}
.topbar-logo-name{font-size:.92rem;font-weight:700;color:var(--fg)}
.t-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:50px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid var(--border);background:var(--bg2);color:var(--fg2);font-family:inherit;transition:.15s}
.t-btn:hover{color:var(--fg);border-color:var(--border2)}
.t-btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}

/* PAGE */
.page{padding:52px 20px;max-width:860px;margin:0 auto}
.page-head{text-align:center;margin-bottom:48px}
.page-head h1{font-size:clamp(1.8rem,5vw,2.8rem);font-weight:900;letter-spacing:-1px;margin-bottom:12px}
.page-head h1 span{color:var(--red)}
.page-head p{color:var(--fg2);font-size:.95rem}
.cur-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#4ade80;padding:8px 20px;border-radius:50px;font-size:13px;font-weight:700;margin-top:14px}

/* PLAN TOGGLE */
.plan-toggle{display:flex;gap:10px;justify-content:center;margin-bottom:32px;flex-wrap:wrap}
.plan-btn{padding:12px 28px;border-radius:14px;border:1.5px solid var(--border);background:var(--bg2);color:var(--fg2);font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:.2s;display:flex;align-items:center;gap:10px}
.plan-btn:hover{border-color:var(--border2);color:var(--fg)}
.plan-btn.active{border-color:var(--red-border);background:var(--red-dim);color:var(--red)}
.plan-btn svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2}

/* DURATION GRID */
.dur-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px}
@media(max-width:500px){.dur-grid{grid-template-columns:1fr}}
.dur-card{border-radius:16px;border:1.5px solid var(--border);background:var(--bg2);padding:20px;cursor:pointer;transition:.2s;position:relative;text-align:center}
.dur-card:hover{border-color:var(--border2)}
.dur-card.active{border-color:var(--red-border);background:var(--red-dim)}
.dur-badge{position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:var(--red);color:#fff;font-size:10px;font-weight:800;padding:3px 12px;border-radius:50px;white-space:nowrap}
.dur-label{font-size:1rem;font-weight:800;color:var(--fg);margin-bottom:4px}
.dur-sub{font-size:12px;color:var(--fg3)}
.dur-card.active .dur-label{color:var(--red)}

/* FORM */
.sub-form{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:28px;margin-bottom:20px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:11px;font-weight:700;color:var(--fg3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px}
.form-group input,.form-group textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--fg);padding:13px 16px;border-radius:12px;font-size:14px;font-family:inherit;outline:none;transition:.2s;resize:none}
.form-group input:focus,.form-group textarea:focus{border-color:#333}
.form-group input::placeholder,.form-group textarea::placeholder{color:var(--fg3)}
.selected-summary{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;gap:16px;flex-wrap:wrap}
.selected-summary span{font-size:13px;color:var(--fg2);font-weight:600}
.selected-summary strong{color:var(--fg)}
.btn-submit{width:100%;padding:15px;border-radius:14px;background:var(--red);color:#fff;border:none;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;transition:.15s;display:flex;align-items:center;justify-content:center;gap:10px}
.btn-submit:hover{opacity:.88}
.btn-submit svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:2.5}

/* PENDING SCREEN */
.pending-screen{text-align:center;padding:60px 20px}
.pending-icon{width:88px;height:88px;border-radius:24px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(245,158,11,.3)}50%{box-shadow:0 0 0 12px rgba(245,158,11,.0)}}
.pending-icon svg{width:40px;height:40px;stroke:#f59e0b;fill:none;stroke-width:1.8}
.pending-title{font-size:1.6rem;font-weight:900;letter-spacing:-.5px;margin-bottom:10px}
.pending-sub{color:var(--fg2);font-size:.95rem;line-height:1.7;max-width:400px;margin:0 auto 28px}
.pending-detail{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:20px;max-width:380px;margin:0 auto;text-align:right}
.pending-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:13.5px}
.pending-row:last-child{border-bottom:none}
.pending-row span:first-child{color:var(--fg3)}
.pending-row strong{color:var(--fg);font-weight:700}

/* REJECTED SCREEN */
.rejected-icon{width:88px;height:88px;border-radius:24px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px}
.rejected-icon svg{width:40px;height:40px;stroke:#f87171;fill:none;stroke-width:1.8}

/* ACTIVE SCREEN */
.active-icon{width:88px;height:88px;border-radius:24px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px}
.active-icon svg{width:40px;height:40px;stroke:#4ade80;fill:none;stroke-width:1.8}
</style>
</head>
<body>
<div class="topbar">
  <a href="/dashboard" class="topbar-logo">
    <img src="/kozhen-icon.jpeg" alt="Kozhen">
    <span class="topbar-logo-name">Kozhen Studio</span>
  </a>
  <a href="/dashboard" class="t-btn">
    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    لوحة التحكم
  </a>
</div>

<div class="page">
  <div class="page-head">
    <h1>اشترك في <span>Kozhen Menu</span></h1>
    <p>اختر الباقة والمدة المناسبة واتركنا نتواصل معك</p>
    <?php if($sub['status']==='active' && $sub['plan']!=='trial'): ?>
    <div class="cur-badge">
      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      باقتك الحالية نشطة — متبقي <?php echo $sub['days_left']; ?> يوم
    </div>
    <?php endif; ?>
  </div>

<?php if ($is_pending): ?>
  <!-- PENDING STATE -->
  <div class="pending-screen">
    <div class="pending-icon">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div class="pending-title">طلبك قيد المراجعة</div>
    <div class="pending-sub">استلمنا طلبك وسيتواصل معك فريقنا قريباً للتأكيد وإتمام الاشتراك. شكراً لاختيارك Kozhen!</div>
    <div class="pending-detail">
      <div class="pending-row">
        <span>الباقة</span>
        <strong><?php echo $plans[$last_req['plan']]['name'] ?? $last_req['plan']; ?></strong>
      </div>
      <div class="pending-row">
        <span>المدة</span>
        <strong><?php echo $durations[$last_req['duration_months']]['label'] ?? $last_req['duration_months'].' شهر'; ?></strong>
      </div>
      <div class="pending-row">
        <span>رقم التواصل</span>
        <strong><?php echo $last_req['phone'] ? htmlspecialchars($last_req['phone']) : '—'; ?></strong>
      </div>
      <div class="pending-row">
        <span>تاريخ الطلب</span>
        <strong><?php echo date('Y/m/d', strtotime($last_req['created_at'])); ?></strong>
      </div>
    </div>
  </div>

<?php else: ?>
  <?php if ($is_rejected): ?>
  <!-- REJECTED NOTICE -->
  <div class="pending-screen" style="padding:32px 20px">
    <div class="rejected-icon">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <div class="pending-title" style="font-size:1.2rem">لم يتم قبول طلبك السابق</div>
    <div class="pending-sub">يمكنك تقديم طلب جديد أدناه أو التواصل معنا مباشرة.</div>
  </div>
  <?php endif; ?>

  <!-- SUBSCRIPTION FORM -->

  <!-- Step 1: Choose Plan -->
  <div class="plan-toggle">
    <?php foreach($plans as $pk => $pv): ?>
    <button type="button" class="plan-btn <?php echo $pk==='basic'?'active':''; ?>" data-plan="<?php echo $pk; ?>" onclick="selectPlan('<?php echo $pk; ?>', this)">
      <svg viewBox="0 0 24 24"><?php echo $pv['icon']; ?></svg>
      <?php echo $pv['name']; ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Step 2: Choose Duration -->
  <div class="dur-grid">
    <?php foreach($durations as $months => $dv): ?>
    <div class="dur-card <?php echo $months===3?'active':''; ?>" data-months="<?php echo $months; ?>" onclick="selectDuration(<?php echo $months; ?>, this)">
      <?php if($dv['badge']): ?><div class="dur-badge"><?php echo $dv['badge']; ?></div><?php endif; ?>
      <div class="dur-label"><?php echo $dv['label']; ?></div>
      <div class="dur-sub"><?php echo $months; ?> أشهر</div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Step 3: Contact Info -->
  <div class="sub-form">
    <div class="selected-summary">
      <span>الباقة: <strong id="sum-plan">أساسية</strong></span>
      <span>المدة: <strong id="sum-dur">٣ أشهر</strong></span>
    </div>
    <div class="form-group">
      <label>رقم التواصل (واتساب / هاتف)</label>
      <input type="tel" id="inp-phone" placeholder="+964 7XX XXX XXXX" dir="ltr">
    </div>
    <div class="form-group">
      <label>ملاحظات (اختياري)</label>
      <textarea id="inp-notes" rows="3" placeholder="أي تفاصيل إضافية تريد إضافتها..."></textarea>
    </div>
    <button type="button" class="btn-submit" id="btn-submit" onclick="submitRequest()">
      <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      إرسال الطلب
    </button>
  </div>

<?php endif; ?>
</div>

<script>
const planNames = <?php echo json_encode(array_map(fn($p)=>$p['name'], $plans), JSON_UNESCAPED_UNICODE); ?>;
const durNames  = <?php echo json_encode(array_map(fn($d)=>$d['label'], $durations), JSON_UNESCAPED_UNICODE); ?>;

let selPlan = 'basic';
let selMonths = 3;

function selectPlan(key, btn) {
  selPlan = key;
  document.querySelectorAll('.plan-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('sum-plan').textContent = planNames[key] || key;
}

function selectDuration(months, card) {
  selMonths = months;
  document.querySelectorAll('.dur-card').forEach(c=>c.classList.remove('active'));
  card.classList.add('active');
  document.getElementById('sum-dur').textContent = durNames[months] || months + ' أشهر';
}

async function submitRequest() {
  const phone = document.getElementById('inp-phone').value.trim();
  const notes = document.getElementById('inp-notes').value.trim();
  const btn = document.getElementById('btn-submit');
  btn.disabled = true;
  btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" stroke="currentColor" fill="none" stroke-width="2" style="animation:spin .8s linear infinite"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg> جاري الإرسال...';

  const fd = new FormData();
  fd.append('plan', selPlan);
  fd.append('duration_months', selMonths);
  fd.append('phone', phone);
  fd.append('notes', notes);

  const res = await fetch('/request-subscription', {method:'POST', body:fd});
  const data = await res.json();
  if (data.success) {
    location.reload();
  } else {
    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" stroke="currentColor" fill="none" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> إرسال الطلب';
    alert('حدث خطأ، حاول مرة أخرى');
  }
}
</script>
<style>@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}</style>
</body>
</html>
