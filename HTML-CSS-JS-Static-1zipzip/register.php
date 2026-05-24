<?php
require_once 'config/config.php';
if (isLoggedIn()) { header("Location: /dashboard"); exit(); }
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $restaurant_name = trim($_POST['restaurant_name'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'اسم المستخدم يجب أن يحتوي على أحرف إنجليزية وأرقام فقط';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $trial_start = date('Y-m-d');
        $trial_end = date('Y-m-d', strtotime('+' . TRIAL_DAYS . ' days'));
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, restaurant_name, trial_start, trial_end) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash, $restaurant_name, $trial_start, $trial_end]);
            $_SESSION['user_id'] = $db->lastInsertId();
            header("Location: /dashboard"); exit();
        } catch (PDOException $e) {
            $error = 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً';
        }
    }
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إنشاء حساب — Kozhen Studio</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap">
<style>
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Light.woff2') format('woff2');font-weight:300}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Medium.woff2') format('woff2');font-weight:500}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_SemiBold.woff2') format('woff2');font-weight:600}
@font-face{font-family:'GraphikArabic';src:url('Graphik_Arabic_Bold.woff2') format('woff2');font-weight:700}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0a0a;--bg2:#141414;--fg:#f5f5f5;--fg2:#aaa;--fg3:#555;
  --border:#242424;--card:#111111;
}
body{
  font-family:'GraphikArabic','Noto Naskh Arabic','Tajawal',sans-serif;
  background:var(--bg);color:var(--fg);min-height:100vh;display:flex;flex-direction:column;
}
body::before{
  content:'';position:fixed;inset:0;
  background-image:repeating-linear-gradient(-45deg,#fff 0,#fff 1px,transparent 1px,transparent 40px);
  opacity:.015;pointer-events:none;z-index:0;
}
.page{
  position:relative;z-index:1;flex:1;
  display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;
}

.logo-wrap{display:flex;align-items:center;gap:12px;text-decoration:none;margin-bottom:32px}
.logo-img{width:48px;height:48px;object-fit:contain;border-radius:10px;filter:invert(1)}
.logo-text{font-size:1.15rem;font-weight:700;color:var(--fg);letter-spacing:-0.3px}

.card{
  background:var(--card);border:1px solid var(--border);border-radius:20px;
  padding:36px 32px;width:100%;max-width:480px;
}
.card-head{margin-bottom:24px;text-align:center}
.card-icon{
  width:52px;height:52px;border-radius:14px;
  background:#1a1a1a;border:1px solid #2e2e2e;
  display:flex;align-items:center;justify-content:center;margin:0 auto 18px;color:var(--fg);
}
.card-icon svg{width:24px;height:24px;stroke:currentColor;fill:none;stroke-width:2}
.card h2{font-size:1.35rem;font-weight:700;letter-spacing:-0.4px;margin-bottom:6px}
.card-sub{color:var(--fg2);font-size:.9rem;font-weight:400}

.trial-badge{
  display:flex;align-items:center;gap:8px;
  background:#1a1a1a;border:1px solid #2e2e2e;color:var(--fg2);
  padding:10px 14px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:22px;
}
.trial-badge svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}

.error-box{
  background:rgba(220,38,38,.12);border:1px solid rgba(220,38,38,.35);color:#f87171;
  padding:11px 14px;border-radius:12px;font-size:13.5px;margin-bottom:18px;
  display:flex;align-items:center;gap:8px;
}
.error-box svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}

.form-row{display:grid;gap:14px}
.form-row.cols-2{grid-template-columns:1fr 1fr}
@media(max-width:480px){.form-row.cols-2{grid-template-columns:1fr}}
.form-group{margin-bottom:14px}
.form-group label{
  display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:600;
  color:var(--fg3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;
}
.form-group label svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2}
.form-group input{
  width:100%;background:#0d0d0d;border:1px solid var(--border);color:var(--fg);
  padding:13px 16px;border-radius:12px;font-size:15px;font-family:inherit;
  outline:none;transition:border-color .2s,background .2s;
}
.form-group input:focus{border-color:#666;background:#111}
.form-group input::placeholder{color:var(--fg3)}
.hint{font-size:11px;color:var(--fg3);margin-top:5px}

.btn-submit{
  width:100%;background:#fff;color:#000;border:none;
  padding:14px;border-radius:12px;font-size:15px;font-weight:700;
  cursor:pointer;font-family:inherit;transition:opacity .2s,transform .15s;
  display:flex;align-items:center;justify-content:center;gap:8px;margin-top:8px;
}
.btn-submit:hover{opacity:.88;transform:translateY(-1px)}
.btn-submit svg{width:17px;height:17px;stroke:#000;fill:none;stroke-width:2.5}

.card-footer{text-align:center;margin-top:20px;color:var(--fg2);font-size:13.5px}
.card-footer a{color:#fff;text-decoration:none;font-weight:600;border-bottom:1px solid #444}
.card-footer a:hover{border-color:#fff}
.divider{border:none;border-top:1px solid var(--border);margin:20px 0}
</style>
</head>
<body>
<div class="page">
  <a href="index.php" class="logo-wrap">
    <img src="kozhen-logo.png" alt="Kozhen Studio" class="logo-img">
    <span class="logo-text">Kozhen Studio</span>
  </a>

  <div class="card">
    <div class="card-head">
      <div class="card-icon">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      </div>
      <h2>ابدأ مطعمك الرقمي</h2>
      <p class="card-sub">أنشئ منيوك في أقل من دقيقة</p>
    </div>

    <div class="trial-badge">
      <svg viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
      شهر تجريبي مجاني — بدون بطاقة ائتمان
    </div>

    <?php if ($error): ?>
    <div class="error-box">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label>
          <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
          اسم المطعم
        </label>
        <input type="text" name="restaurant_name" placeholder="مطعم الأصالة" required value="<?php echo htmlspecialchars($_POST['restaurant_name'] ?? '') ?>">
      </div>

      <div class="form-row cols-2">
        <div class="form-group">
          <label>
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7"/></svg>
            اسم المستخدم
          </label>
          <input type="text" name="username" placeholder="myrestaurant" pattern="[a-zA-Z0-9_]+" required value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username">
          <div class="hint">أحرف إنجليزية وأرقام فقط</div>
        </div>
        <div class="form-group">
          <label>
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            البريد الإلكتروني
          </label>
          <input type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email">
        </div>
      </div>

      <div class="form-group">
        <label>
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          كلمة المرور
        </label>
        <input type="password" name="password" placeholder="••••••••" minlength="6" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn-submit">
        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        إنشاء الحساب مجاناً
      </button>
    </form>

    <hr class="divider">
    <div class="card-footer">لديك حساب بالفعل؟ <a href="/login">سجّل دخولك</a></div>
  </div>
</div>
</body>
</html>
