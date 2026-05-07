<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'VRide — Vehicle Rentals' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="preconnect" href="https://images.unsplash.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --blue: #1A8CFF;
  --blue-dark: #0A6FE8;
  --blue-glow: rgba(26,140,255,0.2);
  --bg: #070A12;
  --bg2: #0B0E1A;
  --bg3: #101422;
  --card: #0D1020;
  --border: rgba(255,255,255,0.07);
  --border-blue: rgba(26,140,255,0.2);
  --txt: #C8D4EE;
  --txt2: #5A6A8E;
  --white: #FFFFFF;
  --success: #00C77A;
  --danger: #E8365D;
  --warn: #F5A623;
  --sidebar-w: 0px;
  --nav-h: 80px;
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }
body {
  background:var(--bg);
  color:var(--txt);
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;
  overflow-x:hidden;
  min-height:100vh;
  line-height:1.5;
}
a { text-decoration:none; color:inherit; }
::-webkit-scrollbar { width:4px; }
::-webkit-scrollbar-track { background:var(--bg2); }
::-webkit-scrollbar-thumb { background:rgba(26,140,255,0.4); border-radius:2px; }

/* ── MAIN NAV (MATCHES INDEX) ── */
#mn {
  position:fixed; top:0; left:0; right:0; z-index:200;
  height:var(--nav-h);
  display:grid; grid-template-columns:auto 1fr auto; align-items:center;
  padding:0 3rem; background:rgba(5,7,9,0.95); backdrop-filter:blur(10px);
  border-bottom:1px solid rgba(26,140,255,0.14);
}
#mn.scrolled { background:rgba(5,7,9,1); }
.nl { display:flex; align-items:center; gap:0.35rem; }
.logo-img { height:38px; width:auto; display:block; mix-blend-mode:screen; }
.logo-text {
  font-size:1.48rem;
  font-weight:800;
  letter-spacing:.05em;
  text-transform:uppercase;
  color:var(--white);
  font-family:'Cinzel Decorative','Segoe UI',sans-serif;
  line-height:1;
  margin-left:-0.35rem;
  transform:translateY(7px);
}
.nav-links { display:flex; gap:2.2rem; list-style:none; justify-content:center; }
.nav-links a { color:var(--txt2); font-weight:500; font-size:0.82rem; transition:color 0.2s; position:relative; }
.nav-links a:hover, .nav-links a.on { color:#fff; }
.nav-links a::after { content:''; position:absolute; bottom:-4px; left:0; height:1.5px; background:var(--blue); width:0; transition:width 0.2s; }
.nav-links a:hover::after, .nav-links a.on::after { width:100%; }
.nb { display:flex; gap:0.8rem; align-items:center; }
.nbo { padding:0.45rem 1.2rem; font-size:0.75rem; font-weight:600; border-radius:6px; border:1px solid rgba(255,255,255,0.12); color:var(--txt); transition:all 0.2s; }
.nbo:hover { border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.04); }
.nbf { padding:0.45rem 1.25rem; font-size:0.75rem; font-weight:700; border-radius:6px; background:var(--blue); color:#fff; border:none; transition:all 0.2s; }
.nbf:hover { background:var(--blue-dark); transform:translateY(-1px); }
.nav-user { font-size:0.75rem; color:rgba(226,232,240,0.45); display:flex; align-items:center; gap:0.5rem; }

@media(max-width:768px) {
  #mn { padding:0.9rem 1.5rem; height:auto; display:flex; justify-content:space-between; }
  .nav-links { display:none; }
  .logo-img { height:30px; }
  .logo-text { font-size:1.28rem; letter-spacing:.05em; margin-left:-0.3rem; transform:translateY(7px); }
}

/* ── FLASH ────────────────────────────────────────── */
.flash {
  position:fixed; top:66px; right:1.5rem; z-index:1000;
  padding:0.75rem 1.3rem; border-radius:6px;
  font-size:0.85rem; font-weight:500;
  animation:flashIn 0.3s ease, flashOut 0.3s 3.5s forwards;
  max-width:340px;
}
.flash-success { background:rgba(0,199,122,0.1); border:1px solid rgba(0,199,122,0.25); color:var(--success); }
.flash-error   { background:rgba(232,54,93,0.1);  border:1px solid rgba(232,54,93,0.25);  color:var(--danger); }
@keyframes flashIn  { from{opacity:0;transform:translateX(16px)} to{opacity:1;transform:none} }
@keyframes flashOut { to{opacity:0;transform:translateX(16px)} }

/* ── BUTTONS ──────────────────────────────────────── */
.btn { display:inline-flex; align-items:center; gap:6px; padding:0.55rem 1.2rem; font-family:inherit; font-size:0.82rem; font-weight:600; border:none; border-radius:6px; cursor:pointer; transition:all 0.2s; line-height:1; }
.btn-primary  { background:var(--blue); color:#fff; }
.btn-primary:hover  { background:var(--blue-dark); transform:translateY(-1px); box-shadow:0 4px 16px var(--blue-glow); }
.btn-secondary { background:transparent; border:1px solid var(--border); color:var(--txt); }
.btn-secondary:hover { border-color:rgba(26,140,255,0.4); color:var(--blue); }
.btn-danger  { background:var(--danger); color:#fff; }
.btn-success { background:var(--success); color:#000; }
.btn-sm { padding:0.38rem 0.85rem; font-size:0.76rem; }

/* ── FORMS ────────────────────────────────────────── */
.form-card { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:1.8rem 2rem; }
.form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:1rem; }
.form-group:last-child { margin-bottom:0; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
label { font-size:0.72rem; font-weight:600; color:var(--txt2); letter-spacing:0.02em; }
input, select, textarea { background:var(--bg3); border:1px solid var(--border); color:var(--txt); font-family:inherit; font-size:0.88rem; padding:0.62rem 0.9rem; outline:none; width:100%; border-radius:6px; transition:border-color 0.2s; }
input:focus, select:focus, textarea:focus { border-color:rgba(26,140,255,0.5); box-shadow:0 0 0 3px rgba(26,140,255,0.07); }
input::placeholder, textarea::placeholder { color:var(--txt2); }
select option { background:var(--bg3); color:var(--txt); }
textarea { resize:vertical; min-height:90px; }
.form-section-title { font-size:0.7rem; font-weight:700; color:var(--txt2); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:1rem; padding-bottom:0.6rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.5rem; }
.form-section-title i { color:var(--blue); width:14px; text-align:center; }

/* ── VEHICLE CARDS ────────────────────────────────── */
.v-card { background:var(--card); border:1px solid var(--border); border-radius:8px; overflow:hidden; transition:transform 0.2s,border-color 0.2s,box-shadow 0.2s; }
.v-card:hover { transform:translateY(-3px); border-color:rgba(26,140,255,0.25); box-shadow:0 8px 32px rgba(0,0,0,0.3); }
.vc-img { height:200px; overflow:hidden; position:relative; background:var(--bg3); }
.vc-img img { width:100%; height:100%; object-fit:cover; transition:transform 0.4s; }
.v-card:hover .vc-img img { transform:scale(1.04); }
.vc-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(13,16,32,0.65) 0%,transparent 55%); }
.vc-body { padding:1rem 1.2rem; }
.vc-cat { font-size:0.68rem; font-weight:600; color:var(--blue); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem; }
.vc-name { font-size:0.98rem; font-weight:700; color:var(--white); margin-bottom:0.4rem; line-height:1.2; }
.vc-foot { display:flex; align-items:center; justify-content:space-between; margin-top:0.85rem; padding-top:0.85rem; border-top:1px solid var(--border); }
.vc-price-l { font-size:0.62rem; color:var(--txt2); text-transform:uppercase; letter-spacing:0.04em; }
.vc-price-v { font-size:1.15rem; font-weight:700; color:var(--white); line-height:1; }
.vc-price-v small { font-size:0.68rem; color:var(--txt2); font-weight:400; }

/* ── PAGE LAYOUT ──────────────────────────────────── */
.page-wrap { padding-top:var(--nav-h); padding-left:var(--sidebar-w); }
.sec-label { font-size:0.68rem; font-weight:600; color:var(--blue); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.35rem; display:flex; align-items:center; gap:8px; }
.sec-label::before { content:''; width:14px; height:1.5px; background:var(--blue); display:block; }
.sec-h { font-size:clamp(1.5rem,3vw,2.3rem); font-weight:800; color:var(--white); letter-spacing:-0.01em; line-height:1.1; }
.sec-h .dim { color:rgba(255,255,255,0.16); }

/* ── BADGES ───────────────────────────────────────── */
.badge { display:inline-block; padding:0.18rem 0.6rem; font-size:0.62rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase; border-radius:4px; }
.badge-pending  { background:rgba(245,166,35,0.1);  color:var(--warn);    border:1px solid rgba(245,166,35,0.25); }
.badge-approved { background:rgba(0,199,122,0.1);   color:var(--success); border:1px solid rgba(0,199,122,0.25); }
.badge-rejected { background:rgba(232,54,93,0.1);   color:var(--danger);  border:1px solid rgba(232,54,93,0.25); }
.badge-rented   { background:rgba(26,140,255,0.1);  color:var(--blue);    border:1px solid rgba(26,140,255,0.25); }
.badge-completed { background:rgba(168,85,247,0.1); color:#A855F7; border:1px solid rgba(168,85,247,0.25); }

/* ── TABLES ───────────────────────────────────────── */
.tbl { width:100%; border-collapse:collapse; }
.tbl th { font-size:0.68rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase; color:var(--txt2); padding:0.72rem 1rem; border-bottom:1px solid var(--border); text-align:left; }
.tbl td { padding:0.78rem 1rem; border-bottom:1px solid rgba(255,255,255,0.03); font-size:0.84rem; vertical-align:middle; }
.tbl tr:hover td { background:rgba(26,140,255,0.02); }

/* ── RESPONSIVE ───────────────────────────────────── */
@media (max-width:900px) {
  .nav-links a { padding:0 0.65rem; font-size:0.78rem; }
}
@media (max-width:768px) {
  .sidebar { display:none; }
  #mainNav { left:0; padding:0 1.2rem; }
  .page-wrap { padding-left:0; }
  .form-row { grid-template-columns:1fr; }
  .nav-links { display:none; }
  .nav-user-name { display:none; }
  /* Show hamburger hint if needed */
}
</style>
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
<script>
  (function(){
    var key = 'I08M-_Yllx7JgUTso';
    if (window.emailjs && typeof emailjs.init === 'function') {
      try { emailjs.init(key); } catch (e) { console.warn('emailjs.init failed', e); }
    } else {
      window.addEventListener('load', function(){
        if (window.emailjs && typeof emailjs.init === 'function') {
          try { emailjs.init(key); } catch (e) { console.warn('emailjs.init failed', e); }
        }
      });
    }
  })();
</script>
</head>
<body>

<!-- ── SIDEBAR ── -->
<!-- NAV -->
<nav id="mn">
            <a href="index.php" class="nl"><img src="img/lo.png" alt="VRide" class="logo-img" fetchpriority="high"><span class="logo-text">Ride</span></a>
  <ul class="nav-links">
    <li><a href="index.php" <?= basename($_SERVER['PHP_SELF'])=='index.php'?'class="on"':'' ?>>Home</a></li>
    <li><a href="vehicles.php" <?= basename($_SERVER['PHP_SELF'])=='vehicles.php'?'class="on"':'' ?>>Fleet</a></li>
    <li><a href="index.php#how">How It Works</a></li>
    <li><a href="contact.php" <?= basename($_SERVER['PHP_SELF'])=='contact.php'?'class="on"':'' ?>>Contact Us</a></li>
    <?php if(isLoggedIn()): ?>
    <li><a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="on"':'' ?>>Dashboard</a></li>
    <?php if(!empty($_SESSION['role']) && $_SESSION['role']==='admin'): ?>
    <li><a href="admin.php" <?= basename($_SERVER['PHP_SELF'])=='admin.php'?'class="on"':'' ?>>Admin</a></li>
    <?php endif; ?>
    <?php endif; ?>
  </ul>
  <div class="nb">
    <?php if(isLoggedIn()): ?>
      <span class="nav-user"><i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
      <a href="logout.php" class="nbo">Logout</a>
    <?php else: ?>
      <a href="login.php" class="nbo">Login</a>
      <a href="register.php" class="nbf">Register</a>
    <?php endif; ?>
  </div>
</nav>

<?php $flash = getFlash(); if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<script>
const nav = document.getElementById('mn');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 40));
if (window.scrollY > 40) nav.classList.add('scrolled');

// Instant Navigation: Pre-fetch PHP links on hover for immediate transitions
document.addEventListener('mouseover', e => {
  const a = e.target.closest('a');
  if (a && a.href && a.href.includes('.php') && !a.dataset.prefetched) {
    const link = document.createElement('link');
    link.rel = 'prefetch'; link.href = a.href;
    document.head.appendChild(link);
    a.dataset.prefetched = 'true';
  }
});
</script>
<?php include dirname(__DIR__) . '/lib/firebase_script.php'; ?>


