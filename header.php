<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'VRide — Vehicle Rentals' ?></title>
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
  --sidebar-w: 48px;
  --nav-h: 54px;
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

/* ── SIDEBAR ─────────────────────────────────────── */
.sidebar {
  position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
  z-index:300; background:var(--bg2);
  border-right:1px solid var(--border);
  display:flex; flex-direction:column; align-items:center;
  padding:1rem 0 1.5rem;
}
.sidebar-logo-mark {
  width:30px; height:30px; background:var(--blue);
  border-radius:7px; display:flex; align-items:center;
  justify-content:center; margin-bottom:1.5rem; flex-shrink:0;
}
.sidebar-logo-mark svg { width:16px; height:16px; fill:#fff; }
.sidebar-nav { display:flex; flex-direction:column; gap:4px; align-items:center; flex:1; }
.sidebar-nav-item {
  width:36px; height:36px; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  color:var(--txt2); transition:background 0.15s, color 0.15s;
  position:relative; font-size:13px;
}
.sidebar-nav-item:hover { background:rgba(26,140,255,0.12); color:var(--blue); }
.sidebar-nav-item.active { background:rgba(26,140,255,0.15); color:var(--blue); }
.sidebar-nav-item::after {
  content:attr(data-label); position:absolute; left:calc(100% + 8px);
  top:50%; transform:translateY(-50%); background:var(--card);
  color:var(--white); font-size:11px; font-weight:500;
  white-space:nowrap; padding:4px 10px; border-radius:4px;
  border:1px solid var(--border); opacity:0; pointer-events:none;
  transition:opacity 0.12s; z-index:999;
}
.sidebar-nav-item:hover::after { opacity:1; }
.sidebar-divider { width:20px; height:1px; background:var(--border); margin:6px 0; }

/* ── TOP NAVBAR ───────────────────────────────────── */
#mainNav {
  position:fixed;
  top:0; left:var(--sidebar-w); right:0;
  z-index:200;
  height:var(--nav-h);
  /* Three-column layout: logo | links | actions */
  display:grid;
  grid-template-columns:auto 1fr auto;
  align-items:center;
  gap:1.5rem;
  padding:0 2rem;
  transition:background 0.3s, border-color 0.3s;
}
#mainNav.scrolled {
  background:rgba(7,10,18,0.96);
  backdrop-filter:blur(12px);
  border-bottom:1px solid var(--border);
}

.nav-logo {
  display:flex; align-items:center; gap:8px;
  font-size:1rem; font-weight:700; color:var(--white);
  white-space:nowrap; flex-shrink:0;
}
.nav-logo-dot { width:7px; height:7px; background:var(--blue); border-radius:50%; flex-shrink:0; }

/* Center column: links */
.nav-links {
  display:flex;
  align-items:center;
  justify-content:center;
  list-style:none;
  gap:0;
  /* No wrapping — links stay in a single row */
  flex-wrap:nowrap;
  overflow:hidden;
}
.nav-links li { flex-shrink:0; }
.nav-links a {
  display:flex;
  align-items:center;
  height:var(--nav-h);
  padding:0 1rem;
  color:var(--txt2);
  font-size:0.82rem;
  font-weight:500;
  white-space:nowrap;
  transition:color 0.2s;
  position:relative;
}
.nav-links a::after {
  content:'';
  position:absolute;
  bottom:0; left:1rem; right:1rem;
  height:2px;
  background:var(--blue);
  transform:scaleX(0);
  transition:transform 0.2s;
  border-radius:2px 2px 0 0;
}
.nav-links a:hover { color:var(--white); }
.nav-links a:hover::after { transform:scaleX(0.6); }
.nav-links a.active { color:var(--white); }
.nav-links a.active::after { transform:scaleX(1); }

/* Right column: actions */
.nav-actions {
  display:flex;
  align-items:center;
  gap:8px;
  flex-shrink:0;
  white-space:nowrap;
}
.nav-user-name { font-size:0.8rem; color:var(--txt2); }

.btn-nav {
  display:inline-flex; align-items:center;
  padding:0.42rem 1rem;
  font-size:0.78rem; font-weight:600;
  border-radius:5px; transition:all 0.2s;
  font-family:inherit; cursor:pointer;
  white-space:nowrap; line-height:1;
}
.btn-nav-outline { border:1px solid var(--border); color:var(--txt2); background:transparent; }
.btn-nav-outline:hover { border-color:rgba(255,255,255,0.25); color:var(--white); }
.btn-nav-fill { background:var(--blue); color:#fff; border:none; }
.btn-nav-fill:hover { background:var(--blue-dark); }

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
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
  <a href="index.php" class="sidebar-logo-mark" title="VRide">
    <svg viewBox="0 0 16 16"><path d="M8 1L14 4.5V11.5L8 15L2 11.5V4.5L8 1Z"/></svg>
  </a>
  <nav class="sidebar-nav">
    <a href="index.php"    class="sidebar-nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>"    data-label="Home">
      <i class="fa-solid fa-house"></i>
    </a>
    <a href="vehicles.php" class="sidebar-nav-item <?= basename($_SERVER['PHP_SELF'])=='vehicles.php'?'active':'' ?>" data-label="Fleet">
      <i class="fa-solid fa-car-side"></i>
    </a>
    <?php if(isLoggedIn()): ?>
    <a href="dashboard.php" class="sidebar-nav-item <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" data-label="Dashboard">
      <i class="fa-solid fa-table-cells-large"></i>
    </a>
    <?php endif; ?>
    <?php if(isAdmin()): ?>
    <a href="admin.php" class="sidebar-nav-item <?= basename($_SERVER['PHP_SELF'])=='admin.php'?'active':'' ?>" data-label="Admin">
      <i class="fa-solid fa-user-shield"></i>
    </a>
    <?php endif; ?>
    <div class="sidebar-divider"></div>
    <a href="contact.php" class="sidebar-nav-item <?= basename($_SERVER['PHP_SELF'])=='contact.php'?'active':'' ?>" data-label="Contact">
      <i class="fa-solid fa-envelope"></i>
    </a>
    <?php if(isLoggedIn()): ?>
    <a href="logout.php" class="sidebar-nav-item" data-label="Logout">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
    <?php endif; ?>
  </nav>
</aside>

<!-- ── TOP NAV ── -->
<nav id="mainNav">
  <a href="index.php" class="nav-logo">
    <div class="nav-logo-dot"></div>
    VRide
  </a>

  <ul class="nav-links">
    <li><a href="index.php"    <?= basename($_SERVER['PHP_SELF'])=='index.php'   ?'class="active"':'' ?>>Home</a></li>
    <li><a href="vehicles.php" <?= basename($_SERVER['PHP_SELF'])=='vehicles.php'?'class="active"':'' ?>>Fleet</a></li>
    <?php if(isLoggedIn()): ?>
    <li><a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>>Dashboard</a></li>
    <?php endif; ?>
    <?php if(isAdmin()): ?>
    <li><a href="admin.php" <?= basename($_SERVER['PHP_SELF'])=='admin.php'?'class="active"':'' ?>>Admin</a></li>
    <?php endif; ?>
    <li><a href="contact.php" <?= basename($_SERVER['PHP_SELF'])=='contact.php'?'class="active"':'' ?>>Contact</a></li>
  </ul>

  <div class="nav-actions">
    <?php if(isLoggedIn()): ?>
      <span class="nav-user-name">Hi, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
      <a href="logout.php" class="btn-nav btn-nav-outline">Log out</a>
    <?php else: ?>
      <a href="login.php"    class="btn-nav btn-nav-outline">Log in</a>
      <a href="register.php" class="btn-nav btn-nav-fill">Register</a>
    <?php endif; ?>
  </div>
</nav>

<?php $flash = getFlash(); if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<script>
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 40));
if (window.scrollY > 40) nav.classList.add('scrolled');
</script>
