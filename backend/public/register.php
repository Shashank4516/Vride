<?php
require_once dirname(__DIR__) . '/lib/db.php';
$pageTitle = 'Sign Up — VRide';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city  = trim($_POST['city'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (!$name || !$email || !$pass)    { $error = 'Please fill in all required fields.'; }
    elseif ($pass !== $pass2)           { $error = 'Passwords do not match.'; }
    elseif (strlen($pass) < 6)          { $error = 'Password must be at least 6 characters.'; }
    else {
        $pdo = getDB();
        if ($pdo) {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name,email,phone,city,password) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $email, $phone, $city, $hash]);
                flash("Account created! Welcome to VRide.");
                redirect('login.php');
            } catch (PDOException $e) {
                $error = 'This email is already registered. Try signing in.';
            }
        } else {
            // Demo mode — no DB
            $_SESSION['user_id'] = 99;
            $_SESSION['name']    = $name;
            $_SESSION['email']   = $email;
            $_SESSION['role']    = 'user';
            flash("Welcome to VRide, $name!");
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { -webkit-font-smoothing:antialiased; }
body {
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  background: #0f1117; color: #e2e8f0; min-height: 100vh;
}
a { text-decoration: none; color: inherit; }

/* Top bar */
.top-bar {
  position:fixed; top:0; left:0; right:0; z-index:100;
  display:flex; align-items:center; justify-content:space-between;
  padding:0 2rem; height:48px;
  background:rgba(15,17,23,0.95); border-bottom:1px solid rgba(255,255,255,0.07);
  backdrop-filter:blur(12px);
}
.top-bar-logo { display:inline-flex; align-items:center; gap:.35rem; }
.top-bar-logo img { height:42px; width:auto; mix-blend-mode:screen; }
.logo-text {
  font-size:1.48rem;
  font-weight:800;
  letter-spacing:.05em;
  text-transform:uppercase;
  color:#fff;
  font-family:'Cinzel Decorative','Segoe UI',sans-serif;
  line-height:1;
  margin-left:-0.35rem;
  transform:translateY(7px);
}
.top-bar-nav { display:flex; align-items:center; gap:1.5rem; }
.top-bar-nav a { font-size:.82rem; font-weight:500; color:rgba(226,232,240,.55); transition:color .2s; }
.top-bar-nav a:hover { color:#e2e8f0; }
.top-bar-cta { display:flex; align-items:center; gap:.75rem; }
.btn-ghost {
  padding:.42rem 1.1rem; border-radius:6px; font-size:.82rem; font-weight:500;
  border:1px solid rgba(255,255,255,.12); color:rgba(226,232,240,.7);
  transition:all .2s; cursor:pointer; background:none;
}
.btn-ghost:hover { border-color:rgba(255,255,255,.25); color:#e2e8f0; }
.btn-primary-sm {
  padding:.42rem 1.2rem; border-radius:6px; font-size:.82rem; font-weight:600;
  background:#3b82f6; color:#fff; border:none; cursor:pointer; transition:background .2s;
}
.btn-primary-sm:hover, .btn-primary-sm.active { background:#2563eb; }
@media(max-width:640px){ .top-bar-nav{display:none;} .top-bar{padding:0 1rem;} }

/* Page grid */
.page {
  min-height:100vh; display:grid;
  grid-template-columns:1fr 480px; padding-top:48px;
}
@media(max-width:900px){ .page{grid-template-columns:1fr;} }

/* Hero panel */
.hero-panel {
  position:relative; overflow:hidden;
  min-height:calc(100vh - 48px);
  display:flex; flex-direction:column; justify-content:flex-end; padding:3rem;
}
.hero-bg {
  position:absolute; inset:0;
  background-image:url('img/bike_hero.png');
  background-size:cover; background-position:center;
}
.hero-overlay {
  position:absolute; inset:0;
  background:linear-gradient(to top,rgba(10,12,16,.9) 0%,rgba(10,12,16,.45) 50%,rgba(10,12,16,.2) 100%);
}
.hero-content { position:relative; z-index:2; max-width:440px; }
.hero-badge {
  display:inline-flex; align-items:center; gap:.5rem;
  background:rgba(59,130,246,.15); border:1px solid rgba(59,130,246,.3);
  color:#93c5fd; font-size:.72rem; font-weight:600;
  letter-spacing:.06em; text-transform:uppercase;
  padding:.32rem .8rem; border-radius:20px; margin-bottom:1rem;
}
.hero-title { font-size:clamp(2rem,3.5vw,2.8rem); font-weight:800; line-height:1.15; color:#fff; margin-bottom:.8rem; }
.hero-title span { color:#60a5fa; }
.hero-sub { font-size:.88rem; color:rgba(226,232,240,.6); line-height:1.7; margin-bottom:1.8rem; }
.hero-steps { display:flex; flex-direction:column; gap:.7rem; }
.hero-step { display:flex; align-items:center; gap:.8rem; font-size:.82rem; color:rgba(226,232,240,.65); }
.step-num {
  width:26px; height:26px; border-radius:50%;
  background:rgba(59,130,246,.2); border:1px solid rgba(59,130,246,.35);
  display:flex; align-items:center; justify-content:center;
  font-size:.7rem; font-weight:700; color:#93c5fd; flex-shrink:0;
}
@media(max-width:900px){ .hero-panel{display:none;} }

/* Form panel */
.form-panel {
  background:#0f1117; display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  padding:2.5rem 2rem; min-height:calc(100vh - 48px);
}
.form-card { width:100%; max-width:380px; }

.form-heading { margin-bottom:1.5rem; }
.form-heading h1 { font-size:1.45rem; font-weight:700; color:#f1f5f9; margin-bottom:.3rem; }
.form-heading p { font-size:.82rem; color:rgba(226,232,240,.4); }

/* Alert */
.alert {
  display:flex; align-items:flex-start; gap:.6rem;
  padding:.75rem 1rem; border-radius:8px; font-size:.82rem;
  font-weight:500; margin-bottom:1.2rem; line-height:1.5;
}
.alert-error { background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.25); color:#fca5a5; }
.alert i { margin-top:1px; flex-shrink:0; }

/* Social */
.social-row { display:flex; gap:.6rem; margin-bottom:1.2rem; }
.social-btn {
  flex:1; display:flex; align-items:center; justify-content:center; gap:.55rem;
  padding:.65rem .8rem; border-radius:8px; font-size:.81rem; font-weight:600;
  font-family:inherit; background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.1); color:#e2e8f0;
  cursor:pointer; transition:background .2s,border-color .2s,transform .15s;
  text-decoration:none;
}
.social-btn:hover { background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.18); transform:translateY(-1px); }
.social-btn svg { flex-shrink:0; }

/* Divider */
.divider {
  display:flex; align-items:center; gap:.8rem;
  margin-bottom:1.2rem; color:rgba(226,232,240,.25); font-size:.75rem;
}
.divider::before,.divider::after { content:''; flex:1; height:1px; background:rgba(255,255,255,.07); }

/* Fields */
.field { margin-bottom:.85rem; }
.field label { display:block; font-size:.73rem; font-weight:600; color:rgba(226,232,240,.5); margin-bottom:.38rem; }
.field-wrap { position:relative; }
.field input {
  width:100%; background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.1); color:#f1f5f9;
  font-family:inherit; font-size:.88rem; padding:.66rem 1rem;
  border-radius:8px; outline:none;
  transition:border-color .2s,background .2s,box-shadow .2s;
}
.field input:focus { border-color:#3b82f6; background:rgba(59,130,246,.05); box-shadow:0 0 0 3px rgba(59,130,246,.12); }
.field input::placeholder { color:rgba(226,232,240,.2); }
.field input.has-toggle { padding-right:2.6rem; }
.toggle-pw {
  position:absolute; right:.8rem; top:50%; transform:translateY(-50%);
  background:none; border:none; color:rgba(226,232,240,.3);
  cursor:pointer; font-size:.85rem; transition:color .2s;
}
.toggle-pw:hover { color:rgba(226,232,240,.7); }

.field-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
@media(max-width:400px){ .field-row{grid-template-columns:1fr;} }

/* Submit */
.submit-btn {
  width:100%; padding:.78rem 1rem; background:#3b82f6;
  color:#fff; border:none; border-radius:8px;
  font-family:inherit; font-size:.92rem; font-weight:700;
  cursor:pointer; transition:background .2s,transform .15s,box-shadow .2s;
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  margin-top:.5rem;
}
.submit-btn:hover { background:#2563eb; transform:translateY(-1px); box-shadow:0 4px 16px rgba(59,130,246,.35); }
.submit-btn:active { transform:translateY(0); }

.form-footer { text-align:center; margin-top:1.2rem; font-size:.81rem; color:rgba(226,232,240,.4); }
.form-footer a { color:#3b82f6; font-weight:600; }

.terms-note { text-align:center; font-size:.71rem; color:rgba(226,232,240,.25); margin-top:.85rem; line-height:1.5; }
.terms-note a { color:#3b82f6; }
</style>
</head>
<body>

<header class="top-bar">
  <a href="index.php" class="top-bar-logo">
    <img src="img/lo.png" alt="VRide" fetchpriority="high"><span class="logo-text">Ride</span>
  </a>
  
  <div class="top-bar-cta">
    <a href="login.php" class="btn-ghost">Sign In</a>
    <a href="register.php" class="btn-primary-sm active">Register</a>
  </div>
</header>

<main class="page">

  <!-- Left: Hero -->
  <div class="hero-panel">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <div class="hero-badge"><i class="fas fa-star"></i> Join 12,000+ Riders</div>
      <h1 class="hero-title">Your Next Ride<br>Starts <span>Here</span></h1>
      <p class="hero-sub">Create your free account and get instant access to 500+ verified bikes, scooters, and cars across 15+ cities.</p>
      <div class="hero-steps">
        <div class="hero-step"><div class="step-num">1</div> Create your free account</div>
        <div class="hero-step"><div class="step-num">2</div> Browse verified vehicles near you</div>
        <div class="hero-step"><div class="step-num">3</div> Book and ride — it's that simple</div>
      </div>
    </div>
  </div>

  <!-- Right: Form -->
  <div class="form-panel">
    <div class="form-card">

      <div class="form-heading">
        <h1>Create account</h1>
        <p>Join VRide and start riding today — it's free</p>
      </div>

      <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
      <?php endif; ?>

      <div id="firebase-auth-alert" class="alert alert-error" role="alert" style="display:none;"></div>

      <!-- Social sign-up -->
      <div class="social-row">
        <button type="button" id="firebase-google-register" class="social-btn">
          <svg width="17" height="17" viewBox="0 0 24 24">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Sign up with Google
        </button>
        <a href="login.php?social=facebook" class="social-btn">
          <i class="fab fa-facebook-f" style="color:#1877F2;font-size:.95rem;"></i> Facebook
        </a>
      </div>

      <div class="divider">or register with email</div>

      <form method="POST" action="register.php" novalidate>
        <div class="field-row">
          <div class="field">
            <label>Full name *</label>
            <input type="text" name="name" placeholder="Your name"
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Phone</label>
            <input type="tel" name="phone" placeholder="+91 XXXXX"
              value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="field">
          <label>Email address *</label>
          <input type="email" name="email" placeholder="you@example.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label>City</label>
          <input type="text" name="city" placeholder="e.g. Mumbai, Delhi, Bangalore"
            value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
        </div>
        <div class="field-row">
          <div class="field">
            <label>Password *</label>
            <div class="field-wrap">
              <input type="password" id="pw1" name="password"
                placeholder="Min. 6 chars" class="has-toggle" required>
              <button type="button" class="toggle-pw" onclick="togglePw('pw1',this)">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="field">
            <label>Confirm *</label>
            <div class="field-wrap">
              <input type="password" id="pw2" name="password2"
                placeholder="Repeat" class="has-toggle" required>
              <button type="button" class="toggle-pw" onclick="togglePw('pw2',this)">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
        </div>
        <button type="submit" class="submit-btn" id="reg-btn">
          <i class="fas fa-user-plus"></i> Create Account
        </button>
      </form>

      <p class="terms-note">
        By creating an account, you agree to our
        <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
      </p>

      <div class="form-footer">
        Already have an account? <a href="login.php">Sign In</a>
      </div>

    </div>
  </div>
</main>

<script>
function togglePw(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text'; icon.className = 'fas fa-eye-slash';
  } else {
    input.type = 'password'; icon.className = 'fas fa-eye';
  }
}
document.querySelector('form').addEventListener('submit', function() {
  const btn = document.getElementById('reg-btn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account…';
  btn.disabled = true;
});
</script>
<?php include dirname(__DIR__) . '/lib/firebase_auth_pages_script.php'; ?>
</body>
</html>
