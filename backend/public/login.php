<?php
require_once dirname(__DIR__) . '/lib/db.php';

$pageTitle = 'Login — VRide';
$error = '';

// Standard email/password login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = 'Please enter both email and password.';
    } elseif ($pdo) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            unset($_SESSION['auth_via']);

            flash('Welcome back, ' . $user['name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin.php' : 'index.php');
        } else {
            $error = 'Incorrect email or password. Please try again.';
        }
    } else {
        $error = 'Database connection is unavailable. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: #e9edf8;
  min-height: 100vh;
  background: radial-gradient(circle at 20% 10%, #161b2f, #10131d 60%);
}
a { color: inherit; text-decoration: none; }

.topbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 20;
  height: 56px;
  background: rgba(13, 16, 24, 0.86);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1rem;
}
.topbar .brand {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}
.topbar .logo-img {
  height: 30px;
  width: auto;
  display: block;
  mix-blend-mode: screen;
}
.topbar .logo-text {
  font-size: 1.48rem;
  font-weight: 800;
  letter-spacing: .05em;
  text-transform: uppercase;
  color: #fff;
  font-family: 'Cinzel Decorative','Segoe UI',sans-serif;
  line-height: 1;
  margin-left: -0.35rem;
  transform: translateY(7px);
  text-shadow: 0 0 12px rgba(26,140,255,.18);
}

.wrap {
  min-height: 100vh;
  padding-top: 56px;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

.left {
  position: relative;
  overflow: hidden;
  background:
    linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.7)),
    radial-gradient(circle at 20% 10%, rgba(26,140,255,.24), transparent 45%),
    #080b14;
  border-right: 1px solid rgba(255,255,255,.08);
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 1.4rem;
}
.badge {
  width: fit-content;
  padding: .4rem .8rem;
  border-radius: 999px;
  background: rgba(26,140,255,.2);
  color: #1A8CFF;
  border: 1px solid rgba(26,140,255,.42);
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
}
.left h1 {
  font-size: clamp(2rem, 4vw, 3.2rem);
  line-height: 1.08;
  max-width: 560px;
}
.left h1 span { color: #1A8CFF; }
.left p {
  max-width: 560px;
  color: rgba(233,237,248,.68);
  line-height: 1.7;
}
.cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  max-width: 640px;
  margin-top: .4rem;
}
.ride-card {
  height: 180px;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,.14);
  position: relative;
}
.ride-video {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 0;
}
.ride-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,.62), rgba(0,0,0,.12));
  z-index: 1;
}
.ride-label {
  position: absolute;
  left: .7rem;
  bottom: .65rem;
  z-index: 2;
  font-size: .76rem;
  letter-spacing: .06em;
  font-weight: 700;
}

.right {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background: #131620;
}
.panel {
  width: 100%;
  max-width: 520px;
  background: #171b27;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 20px;
  padding: 2rem;
}
.panel h2 {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: .4rem;
}
.panel .sub {
  color: rgba(233,237,248,.58);
  margin-bottom: 1.2rem;
}

.alert {
  margin-bottom: 1rem;
  padding: .85rem 1rem;
  border-radius: 10px;
  font-size: .9rem;
  border: 1px solid rgba(239,68,68,.4);
  background: rgba(239,68,68,.1);
  color: #fecaca;
}

.field { margin-bottom: .9rem; }
.field label {
  display: block;
  margin-bottom: .35rem;
  color: rgba(233,237,248,.78);
  font-size: .82rem;
  font-weight: 600;
}
.field input {
  width: 100%;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.16);
  background: #1d2232;
  color: #fff;
  font-size: .95rem;
  padding: .82rem .95rem;
  outline: none;
}
.field input:focus {
  border-color: #1A8CFF;
  box-shadow: 0 0 0 3px rgba(26,140,255,.18);
}

.login-btn {
  width: 100%;
  border: none;
  border-radius: 12px;
  background: #1A8CFF;
  color: #fff;
  font-weight: 800;
  font-size: 1rem;
  padding: .86rem 1rem;
  cursor: pointer;
  margin-top: .3rem;
}
.login-btn:hover { background: #1A8CFF; }

.forgot {
  text-align: center;
  margin: 1rem 0;
}
.forgot a {
  color: rgba(233,237,248,.82);
  font-weight: 600;
}

.sep {
  height: 1px;
  background: rgba(255,255,255,.12);
  margin: 1rem 0;
}

.google-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: .9rem;
}
.google-note {
  text-align: center;
  color: rgba(233,237,248,.5);
  font-size: .8rem;
  margin-top: .15rem;
}
.alert-firebase {
  display: none;
  align-items: flex-start;
  gap: .5rem;
  padding: .65rem .85rem;
  border-radius: 10px;
  background: rgba(232, 54, 93, 0.12);
  border: 1px solid rgba(232, 54, 93, 0.35);
  color: #f9b4c9;
  font-size: .82rem;
  line-height: 1.45;
  margin-bottom: .9rem;
}
.login-btn-google {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .55rem;
  border: 1px solid rgba(255,255,255,.14);
  border-radius: 12px;
  background: rgba(255,255,255,.06);
  color: #e9edf8;
  font-weight: 700;
  font-size: .95rem;
  padding: .78rem 1rem;
  cursor: pointer;
  font-family: inherit;
  transition: background .2s, border-color .2s;
}
.login-btn-google:hover {
  background: rgba(255,255,255,.1);
  border-color: rgba(255,255,255,.22);
}
.login-btn-google svg { flex-shrink: 0; }

.create-btn {
  display: block;
  width: 100%;
  text-align: center;
  border: 1px solid #1A8CFF;
  color: #1A8CFF;
  border-radius: 12px;
  font-weight: 700;
  padding: .78rem 1rem;
  transition: all .2s;
}
.create-btn:hover {
  background: rgba(26,140,255,.12);
}

@media (max-width: 980px) {
  .wrap { grid-template-columns: 1fr; }
  .left { display: none; }
  .right { padding: 1.2rem .8rem; }
  .panel { padding: 1.2rem; border-radius: 16px; }
  .panel h2 { font-size: 1.6rem; }
}
</style>
</head>
<body>
<header class="topbar">
  <a href="index.php" class="brand"><img src="img/lo.png" alt="VRide" class="logo-img"><span class="logo-text">Ride</span></a>
  <a href="register.php" style="color:#1A8CFF;font-weight:700;font-size:.9rem;">Create Account</a>
</header>

<main class="wrap">
  <section class="left">
    <div class="badge">VRide Booking App</div>
    <h1>Login into <span>VRide</span> </h1>
    <p>Choose your ride, confirm booking, and track your trip in one clean experience built for city travelers.</p>
    <div class="cards">
      <div class="ride-card bike">
        <video class="ride-video" autoplay muted loop playsinline preload="metadata">
          <source src="img/15059932_1080_1920_30fps.mp4" type="video/mp4">
        </video>
        <div class="ride-label">Bike Rider Bookings</div>
      </div>
      <div class="ride-card car">
        <video class="ride-video" autoplay muted loop playsinline preload="metadata">
          <source src="img/14228180-hd_1920_1080_60fps.mp4" type="video/mp4">
        </video>
        <div class="ride-label">Car Rider Bookings</div>
      </div>
    </div>
  </section>

  <section class="right">
    <div class="panel">
      <h2>Login into VRide</h2>
      <p class="sub">Use your email and password to continue.</p>

      <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div id="firebase-auth-alert" class="alert-firebase" role="alert" hidden></div>

      <div class="google-wrap">
        <button type="button" id="firebase-google-login" class="login-btn-google" aria-label="Continue with Google">
          <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Continue with Google
        </button>
      </div>
      <p class="google-note">Secured with Firebase — you’ll return here after Google.</p>

      <div class="sep"></div>

      <form method="POST" action="login.php">
        <div class="field">
          <label for="email">Mobile number, username or email</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button class="login-btn" type="submit">Log in</button>
      </form>

      <div class="forgot"><a href="#">Forgot password?</a></div>

      <div class="sep"></div>

      <a class="create-btn" href="register.php">Create new account</a>
    </div>
  </section>
</main>
<?php include dirname(__DIR__) . '/lib/firebase_auth_pages_script.php'; ?>
</body>
</html>
