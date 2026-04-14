<?php
require_once 'db.php';
$pageTitle = 'Register — VRide';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city  = trim($_POST['city'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    if ($pass !== $pass2) { $error = 'Passwords do not match.'; }
    elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
    else {
        $pdo = getDB();
        if ($pdo) {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name,email,phone,city,password) VALUES (?,?,?,?,?)");
                $stmt->execute([$name,$email,$phone,$city,$hash]);
                flash("Account created! Please sign in.");
                redirect('login.php');
            } catch (PDOException $e) {
                $error = 'Email already registered.';
            }
        } else {
            // Demo mode
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
<?php include 'header.php'; ?>
<style>
.auth-wrap{min-height:100vh;padding-left:48px;display:flex;align-items:center;justify-content:center;padding-top:5rem;padding-bottom:3rem;position:relative;overflow:hidden;}
.auth-bg{position:absolute;inset:0;background:radial-gradient(ellipse 60% 60% at 50% 50%,rgba(26,140,255,.07) 0%,transparent 65%);}
.auth-bg-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(26,140,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(26,140,255,.03) 1px,transparent 1px);background-size:50px 50px;}
.auth-box{position:relative;z-index:2;width:100%;max-width:520px;padding:0 1.5rem;}
.auth-card{background:var(--card);border:1px solid rgba(255,255,255,.07);padding:3rem 2.5rem;}
.auth-logo{font-family:inherit;font-size:2rem;font-weight:700;color:var(--white);text-align:center;margin-bottom:.3rem;letter-spacing:.08em;}
.auth-logo span{color:var(--blue);}
.auth-tagline{text-align:center;font-size:.78rem;color:var(--txt2);margin-bottom:2.5rem;letter-spacing:.1em;text-transform:uppercase;font-family:inherit;}
.auth-title{font-family:inherit;font-size:1.4rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--white);margin-bottom:2rem;padding-bottom:.8rem;border-bottom:1px solid var(--border);}
.error-box{background:rgba(255,56,96,.1);border:1px solid rgba(255,56,96,.25);color:var(--danger);padding:.85rem 1rem;font-family:inherit;font-size:.85rem;font-weight:600;margin-bottom:1.5rem;}
.auth-footer{text-align:center;margin-top:1.5rem;font-size:.82rem;color:var(--txt2);}
.auth-footer a{color:var(--blue);font-weight:600;}
</style>
<div class="auth-wrap">
  <div class="auth-bg"></div><div class="auth-bg-grid"></div>
  <div class="auth-box">
    <div class="auth-card">
      <div class="auth-logo">V<span>RIDE</span></div>
      <div class="auth-tagline">Create Your Free Account</div>
      <div class="auth-title"><i class="fas fa-pen"></i> Register Now</div>
      <?php if($error): ?><div class="error-box"><i class="fas fa-xmark"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="register.php">
        <div class="form-row">
          <div class="form-group"><label>Full Name *</label><input type="text" name="name" placeholder="Your name" required></div>
          <div class="form-group"><label>Phone Number</label><input type="tel" name="phone" placeholder="+91 XXXXX XXXXX"></div>
        </div>
        <div class="form-group"><label>Email Address *</label><input type="email" name="email" placeholder="you@email.com" required></div>
        <div class="form-group"><label>Your City</label><input type="text" name="city" placeholder="e.g. Mumbai, Delhi, Bangalore"></div>
        <div class="form-row">
          <div class="form-group"><label>Password *</label><input type="password" name="password" placeholder="Min. 6 characters" required></div>
          <div class="form-group"><label>Confirm Password *</label><input type="password" name="password2" placeholder="Repeat password" required></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem;">
          Create Account
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>
      <div class="auth-footer">Already have an account? <a href="login.php">Sign In</a></div>
    </div>
  </div>
</div>
</body>
</html>
