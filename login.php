<?php
require_once 'db.php';

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
.topbar img { height: 40px; width: auto; }

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
  <a href="index.php"><img src="img/logo.png" alt="VRide"></a>
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
</body>
</html>
