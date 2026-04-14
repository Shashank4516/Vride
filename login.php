<?php
require_once 'db.php';
$pageTitle = 'Login — VRide';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($pdo && $email && $pass) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            flash("Welcome back, {$user['name']}!");
            redirect($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        // Demo mode login for testing without DB
        if ($email === 'admin@vrental.com' && $pass === 'admin123') {
            $_SESSION['user_id'] = 1;
            $_SESSION['name']    = 'Admin';
            $_SESSION['email']   = $email;
            $_SESSION['role']    = 'admin';
            redirect('admin.php');
        } elseif ($email && $pass) {
            $_SESSION['user_id'] = 99;
            $_SESSION['name']    = explode('@',$email)[0];
            $_SESSION['email']   = $email;
            $_SESSION['role']    = 'user';
            redirect('dashboard.php');
        } else {
            $error = 'Please enter email and password.';
        }
    }
}
?>
<?php include 'header.php'; ?>
<style>
.auth-wrap {
  min-height:100vh;padding-left:48px;
  display:flex;align-items:center;justify-content:center;
  padding-top:5rem;padding-bottom:3rem;
  position:relative;overflow:hidden;
}
.auth-bg {
  position:absolute;inset:0;
  background:radial-gradient(ellipse 60% 60% at 50% 50%,rgba(26,140,255,.07) 0%,transparent 65%);
}
.auth-bg-grid {
  position:absolute;inset:0;
  background-image:linear-gradient(rgba(26,140,255,.03) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(26,140,255,.03) 1px,transparent 1px);
  background-size:50px 50px;
}
.auth-box {
  position:relative;z-index:2;
  width:100%;max-width:480px;
  padding:0 1.5rem;
}
.auth-card {
  background:var(--card);
  border:1px solid rgba(255,255,255,.07);
  padding:3rem 2.5rem;
}
.auth-logo {
  font-family:inherit;font-size:2rem;font-weight:700;
  color:var(--white);text-align:center;margin-bottom:.3rem;letter-spacing:.08em;
}
.auth-logo span { color:var(--blue); }
.auth-tagline {
  text-align:center;font-size:.78rem;color:var(--txt2);
  margin-bottom:2.5rem;letter-spacing:.1em;text-transform:uppercase;
  font-family:inherit;
}
.auth-title {
  font-family:inherit;font-size:1.4rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.1em;color:var(--white);
  margin-bottom:2rem;padding-bottom:.8rem;border-bottom:1px solid var(--border);
}
.error-box {
  background:rgba(255,56,96,.1);border:1px solid rgba(255,56,96,.25);
  color:var(--danger);padding:.85rem 1rem;
  font-family:inherit;font-size:.85rem;font-weight:600;
  margin-bottom:1.5rem;
}
.auth-footer {
  text-align:center;margin-top:1.5rem;
  font-size:.82rem;color:var(--txt2);
}
.auth-footer a { color:var(--blue);font-weight:600; }
.demo-box {
  margin-top:1.5rem;padding:1rem;
  background:rgba(26,140,255,.06);border:1px solid rgba(26,140,255,.2);
  font-size:.78rem;color:var(--txt2);line-height:1.6;
}
.demo-box strong { color:var(--blue);display:block;margin-bottom:.3rem; }
</style>

<div class="auth-wrap">
  <div class="auth-bg"></div>
  <div class="auth-bg-grid"></div>
  <div class="auth-box">
    <div class="auth-card">
      <div class="auth-logo">V<span>RIDE</span></div>
      <div class="auth-tagline">Premium Vehicle Rentals</div>
      <div class="auth-title"><i class="fas fa-lock"></i> Sign In to Continue</div>
      <?php if ($error): ?>
      <div class="error-box"><i class="fas fa-xmark"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="you@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:1.8rem">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
          Sign In
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>
      <div class="auth-footer">Don't have an account? <a href="register.php">Register Free</a></div>
      <div class="demo-box">
        <strong><i class="fas fa-robot"></i> Demo Credentials</strong>
        Admin: admin@vrental.com / admin123<br>
        User: any email / any password (demo mode)
      </div>
    </div>
  </div>
</div>
</body>
</html>
