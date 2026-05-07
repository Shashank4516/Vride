<?php
require_once dirname(__DIR__) . '/lib/db.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'VRide - Smart Rentals';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  /* ══ TRUST BAR ══ */
#tb{background:linear-gradient(to right,rgba(26,140,255,.05),rgba(26,140,255,.02),rgba(26,140,255,.05));border-top:1px solid var(--br);border-bottom:1px solid var(--br);padding:1.3rem 3rem;}
.tbi{max-width:1200px;margin:0 auto;display:flex;justify-content:space-around;flex-wrap:wrap;gap:1rem;}
.tit{display:flex;align-items:center;gap:.65rem;}
.tiic{font-size:1rem;color:var(--bl);width:28px;text-align:center;}
.titx{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(200,212,240,.65);}

:root {
  --bl: #1A8CFF;
  --blg: rgba(26, 140, 255, .28);
  --bk: #000;
  --bg: #050709;
  --bg2: #080B12;
  --bg3: #0C101A;
  --cd: #0A0D17;
  --br: rgba(26, 140, 255, .14);
  --tx: #C8D4F0;
  --tx2: #5A6A8E;
  --wh: #fff;
  --ok: #00D68F;
  --rd: #FF3860;
  --yn: #FFB830;
  --gd: #F5C842;
  --accent: var(--bl);
}

* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
  color: var(--tx);
  background: var(--bk);
  min-height: 100vh;
  overflow-x: hidden;
}

a { color: inherit; text-decoration: none; }

.wrap {
  width: min(1200px, 92%);
  margin: 0 auto;
}

.nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 500;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 3.5rem;
  height: 64px;
  background: rgba(5, 7, 9, 0.98);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 1rem;
}

.logo {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
}

.logo img {
  height: 38px;
  width: auto;
  mix-blend-mode: screen;
}
.logo-text {
  font-size: 1.48rem;
  font-weight: 800;
  letter-spacing: .05em;
  text-transform: uppercase;
  color: var(--wh);
  font-family: 'Cinzel Decorative','Segoe UI',sans-serif;
  line-height: 1;
  margin-left: -0.35rem;
  transform: translateY(7px);
}

.nav-links {
  list-style: none;
  display: flex;
  align-items: center;
  gap: 2.2rem;
}

.nav-links a {
  color: rgba(226, 232, 240, 0.6);
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  transition: color 0.3s;
}

.nav-links a:hover { color: var(--wh); }

.nav-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-btn {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.62rem 1.4rem;
  border-radius: 4px;
  transition: 0.3s;
}

.btn-login { background: transparent; border: 1px solid rgba(255, 255, 255, 0.2); color: var(--tx); }
.btn-login:hover { background: rgba(255, 255, 255, 0.05); }

.btn-register { background: var(--bl); color: var(--wh); border: 1px solid var(--bl); }
.btn-register:hover { opacity: 0.9; transform: translateY(-1px); }

/* Trust Bar */
.trust {
  padding: 4rem 0;
  background: var(--bg);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.trust-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  opacity: 0.65;
  filter: grayscale(1) brightness(1.2);
}

.trust img { height: 34px; width: auto; transition: 0.3s; }
.trust img:hover { filter: grayscale(0); opacity: 1; }


/* ══ HERO ══ */
#hero {
  position: relative;
  height: 100vh;
  min-height: 640px;
  display: flex;
  align-items: center;
  background: #050709;
  overflow: hidden;
}

.hl{position:relative;z-index:10;padding:0 3.5rem;max-width:580px;}
.hpre{font-size:.56rem;font-weight:400;letter-spacing:.45em;text-transform:uppercase;color:var(--bl);display:flex;align-items:center;gap:.7rem;margin-bottom:1.6rem;opacity:0;animation:ru .7s .15s forwards;}
.hpre::before{content:'';width:20px;height:1px;background:var(--bl);}
.hh{font-size:clamp(3.1rem,6.4vw,5.9rem);font-weight:700;line-height:1.02;letter-spacing:-0.01em;text-transform:uppercase;opacity:0;animation:ru .8s .3s forwards;display:inline-block;}
.hh .line{display:flex;align-items:center;gap:.55rem;}
.hh .line + .line{margin-top:.08em;}
.hh .hh-ic{
  font-size:.32em;
  color:#d8f4ff;
  text-shadow:0 0 8px rgba(26,140,255,.85),0 0 16px rgba(12,88,210,.55);
  transform:translateY(-.05em);
}
.hh .lb{
  color:#69c3ff;
  text-shadow:
    0 0 6px rgba(16, 141, 225, 0.95),
    0 0 14px #1A8CFF,
    0 0 28px rgba(2, 45, 173, 0.82),
    0 0 52px rgba(4, 17, 47, 0.75);
}
.hh .lw{
  color:#eefdff;
  text-shadow:
    0 0 6px rgba(190,245,255,.95),
    0 0 14px rgba(132,220,255,.85),
    0 0 28px rgba(71,182,255,.65);
}
.hsub{font-size:1rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--bl);opacity:0;animation:ru .8s .48s forwards; margin:1rem 0 0; margin:15px;}
.hsub .dm{color:rgba(200,212,240,.45);}
.hdesc{font-size:.88rem;line-height:1.9;color:var(--tx2);max-width:400px;font-weight:300;opacity:0;animation:ru .8s .6s forwards;margin:.8rem 0 2rem;}
.hbtns{display:flex;gap:.9rem;flex-wrap:wrap;opacity:0;animation:ru .8s .74s forwards;}
.hbn{display:inline-flex;align-items:center;gap:.55rem;background:var(--bl);color:var(--bk);padding:.82rem 2.2rem;border-radius:40px;font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;box-shadow:0 0 30px rgba(26,140,255,.4),0 0 65px rgba(26,140,255,.12);border:none;cursor:pointer;transition:all .35s;}
.hbn:hover{background:#3AB0FF;transform:translateY(-2px);}
.hbo{display:inline-flex;align-items:center;gap:.55rem;background:transparent;border:1.5px solid rgba(26,140,255,.3);color:var(--tx2);padding:.8rem 2rem;border-radius:40px;font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;cursor:pointer;transition:all .3s;}
.hbo:hover{border-color:var(--bl);color:var(--bl);}
.hero-nav{display:flex;gap:.75rem;margin-top:1.15rem;opacity:0;animation:ru .8s .82s forwards;}

/* ══ HERO CAR — Fixed slideshow container ══ */
.hcar{
  position:absolute;right:0;bottom:0;
  width:60%;height:100%;
  z-index:5;
  overflow:hidden;
}
/* Each slide is position:absolute, fills the container */
.hslide{
  position:absolute;inset:0;
  opacity:0;
  transition:opacity .7s ease;
  pointer-events:none;
}
.hslide.active{opacity:1;pointer-events:auto;}
.hslide img{
  width:100%;height:100%;
  object-fit:cover;
  object-position:center center;
  display:block;
}
/* Dark gradient overlay so text stays readable */
.hslide::after{
  content:'';
  position:absolute;inset:0;
  background:linear-gradient(to right, rgba(0,0,0,.85) 0%, rgba(0,0,0,.3) 40%, transparent 70%);
}
/* Ground shadow */
.hcar-shadow{position:absolute;bottom:0;left:0;right:0;height:120px;background:linear-gradient(to top,rgba(0,0,0,.9),transparent);z-index:6;pointer-events:none;}

/* ══ HERO BOTTOM BAR ══ */
.hbot{
  position:absolute; bottom:0; left:0; right:0; z-index:20;
  display:flex; align-items:center; justify-content:space-between;
  padding:1.5rem 3.5rem;
  background:rgba(0,0,0,0.85); backdrop-filter:blur(16px);
  border-top:1px solid rgba(255,255,255,0.06);
}
.arrp{display:flex;gap:.75rem;}
.arr{width:46px;height:46px;border-radius:50%;background:transparent;border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;transition:all .3s;}
.arr:hover{border-color:#3B82F6;color:#3B82F6;background:rgba(59,130,246,0.08);}
.loc{display:flex;align-items:center;gap:.7rem;font-size:.82rem;font-weight:600;letter-spacing:.02em;color:rgba(255,255,255,.5);}
.loc i{color:#3B82F6;font-size:.9rem;}
.hst{text-align:left;}
.hstn{font-size:1.6rem;font-weight:800;color:#fff;line-height:1;}
.hstl{font-size:.7rem;color:rgba(255,255,255,.4);margin-top:.2rem;font-weight:600;}

@keyframes ru{from{opacity:0;transform:translateY(22px);}to{opacity:1;transform:none;}}


/* Motions Section */
.motions { padding: 8rem 0; background: var(--bk); }
.sec-title { text-align: center; margin-bottom: 3rem; }
.sec-title h2 { font-size: 3.2rem; font-weight: 900; color: var(--wh); text-transform: uppercase; letter-spacing: -0.02em; }
.sec-title h2 span { color: var(--bl); }

.video-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2.2rem;
}

.v-card {
  position: relative;
  border-radius: 28px;
  overflow: hidden;
  aspect-ratio: 16/10.5;
  background: var(--cd);
  box-shadow: 0 0 30px rgba(0, 150, 255, 0.2);
}

.v-card::before {
  content: "";
  position: absolute;
  width: 200%;
  height: 200%;
  top: -50%;
  left: -50%;
  background: conic-gradient(from 0deg, transparent 70%, #00ffff 80%, #0055ff 100%);
  animation: rollGlow 3s linear infinite;
  z-index: 0;
}

.v-card::after {
  content: "";
  position: absolute;
  inset: 3px;
  background: var(--cd);
  border-radius: 25px;
  z-index: 1;
}

@keyframes rollGlow {
  100% { transform: rotate(360deg); }
}

.v-card video { 
  position: absolute;
  inset: 3px;
  width: calc(100% - 6px); 
  height: calc(100% - 6px); 
  object-fit: cover; 
  border-radius: 25px;
  z-index: 2;
}

.v-overlay {
  position: absolute;
  inset: 3px;
  border-radius: 25px;
  display: flex;
  align-items: flex-end;
  padding: 2.5rem;
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 60%);
  z-index: 3;
}

.v-overlay h3 { font-size: 1.4rem; font-weight: 700; color: var(--wh); text-transform: uppercase; letter-spacing: 0.05em; }

/* Payment Methods */
.payments { padding: 6rem 0; background: var(--bg2); }
.pay-grid {
  display: flex;
  justify-content: center;
  gap: 4rem;
  flex-wrap: wrap;
}

.pay-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.2rem;
  transition: 0.3s;
}

.pay-item:hover { transform: translateY(-5px); }
.pay-item i { font-size: 3rem; color: var(--bl); opacity: 0.9; filter: drop-shadow(0 0 10px var(--blg)); }
.pay-item span { font-weight: 700; color: var(--tx2); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; }

/* How It Works */
.how { padding: 8rem 0; background: var(--bk); }
.how-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.step {
  position: relative;
  background: var(--cd);
  padding: 3.5rem 2.5rem;
  border-radius: 24px;
  text-align: center;
  transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  overflow: hidden;
}

.step:hover { transform: translateY(-12px); background: var(--bg3); }

.step::after {
  content: '';
  position: absolute;
  inset: 0;
  border: 2px solid var(--bl);
  border-radius: 24px;
  animation: edgeRun 6s linear infinite;
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask-composite: exclude;
  -webkit-mask-composite: destination-out;
  pointer-events: none;
}

@keyframes edgeRun {
  0% { clip-path: inset(0 0 98% 0); }
  25% { clip-path: inset(0 0 0 98%); }
  50% { clip-path: inset(98% 0 0 0); }
  75% { clip-path: inset(0 98% 0 0); }
  100% { clip-path: inset(0 0 98% 0); }
}

.step-icon {
  width: 72px; height: 72px;
  background: var(--blg);
  color: var(--bl);
  display: flex;
  align-items: center; justify-content: center;
  margin: 0 auto 2rem;
  border-radius: 20px;
  font-size: 1.8rem;
  box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}

.step h3 { margin-bottom: 1.2rem; color: var(--wh); font-size: 1.4rem; font-weight: 800; text-transform: uppercase; }
.step p { color: var(--tx2); line-height: 1.6; font-size: 0.95rem; }

/* FAQs */
.faq { padding: 8rem 0; background: var(--bg2); }
.faq-grid { max-width: 850px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.2rem; }
.faq-item {
  background: var(--cd);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 1.8rem;
  cursor: pointer;
  transition: 0.3s;
}

.faq-item:hover { border-color: var(--bl); background: var(--bg3); }
.faq-q { display: flex; justify-content: space-between; align-items: center; font-weight: 700; color: var(--wh); font-size: 1.1rem; }
.faq-q i { color: var(--bl); font-size: 0.9rem; transition: 0.3s; }
.faq-a { margin-top: 1.2rem; color: var(--tx2); line-height: 1.7; font-size: 1rem; display: none; }

/* Footer */
.footer { padding: 5rem 0 2rem; background: var(--bk); border-top: 1px solid rgba(255,255,255,0.06); }
.f-top { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 4rem; margin-bottom: 5rem; }
.f-col h4 { color: var(--wh); margin-bottom: 2rem; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.12em; font-weight: 800; }
.f-col ul { list-style: none; display: flex; flex-direction: column; gap: 1rem; }
.f-col a { color: var(--tx2); font-size: 0.95rem; transition: 0.3s; font-weight: 600; }
.f-col a:hover { color: var(--bl); padding-left: 5px; }

.f-btm { border-top: 1px solid rgba(255,255,255,0.06); padding-top: 2.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--tx2); }
.f-socials { display: flex; gap: 1.8rem; }
.f-socials i { font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
.f-socials i:hover { color: var(--bl); transform: scale(1.1); }

/* Toasts */
#toast {
  position: fixed;
  bottom: 2.5rem;
  right: 2.5rem;
  background: var(--bl);
  color: var(--wh);
  padding: 1.2rem 2.5rem;
  border-radius: 14px;
  font-weight: 800;
  box-shadow: 0 15px 40px rgba(0,0,0,0.6);
  display: none;
  z-index: 1000;
  animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-size: 0.9rem;
}

@keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 768px) {
  .nav { padding: 0 1.5rem; }
  .nav-inner { justify-content: center; }
  .nav-links, .nav-actions { display: none; }
  .f-top { grid-template-columns: 1fr; gap: 3rem; }
  .video-grid { grid-template-columns: 1fr; }
  .sec-title h2 { font-size: 2.2rem; }
}
</style>
</head>
<body>

<header class="nav">
  <div class="wrap nav-inner">
    <a href="home.php" class="logo">
      <img src="img/lo.png" alt="VRide">
        <span class="logo-text">Ride</span>
    </a>
    <ul class="nav-links">
      <li><a href="home.php">HOME</a></li>
      <li><a href="#how">HOW IT WORKS</a></li>
    
      <li><a href="contact.php">CONTACT US</a></li>
    </ul>
    <div class="nav-actions">
      <a href="login.php" class="nav-btn btn-login">Login</a>
      <a href="register.php" class="nav-btn btn-register">Sign Up</a>
    </div>
  </div>
</header>

<section id="hero">

  <div class="hl">
    <h1 class="hh">
      <span class="line"><span class="lb">ENJOY</span></span>
      <span class="line"><span class="lw" style="font-size: 4.1rem;">YOUR</span></span>
      <span class="line"><span class="lb" >ROYAL RIDES......</span>
    </h1>
    <p class="hsub">FIND THE <span style="color:var(--wh)">BEST VEHICLE</span><br>FOR RENT <span class="dm">TODAY</span></p>
    <div class="hbtns">
      <a href="login.php" class="hbn" onclick="showToast(event)">BOOK NOW <i class="fa-solid fa-arrow-right"></i></a>
      <a href="list_vehicle.php" class="hbo"><i class="fa-solid fa-plus"></i> LIST YOUR VEHICLE</a>
    </div>
    <div class="hero-nav"style="size: 1.5rem;" aria-label="Slideshow controls">
      <button class="arr" id="heroPrev" type="button" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
      <button class="arr" id="heroNext" type="button" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
  </div>

  <!-- HERO SLIDESHOW — full-bleed, equal-size slides -->
  <div class="hcar" id="heroWrap">
    <?php
    $heroSlides = [
      ["img/pexels-ene-marius-241207761-28904247.jpg", "Lamborghini Aventador"],
      ["img/pexels-matus-burian-6692972-5975536.jpg", "Mercedes GLE"],
      ["img/pexels-matus-burian-6692972-5976645.jpg", "Mahindra Thar"],
    ];
    foreach($heroSlides as $idx => $sl): ?>
    <div class="hslide <?= $idx===0?'active':'' ?>" data-index="<?= $idx ?>">
      <img src="<?= $sl[0] ?>" alt="<?= $sl[1] ?>">
    </div>
    <?php endforeach; ?>
    <div class="hcar-shadow"></div>
  </div>

  
</section>

<!-- TRUST BAR -->
<div id="tb">
  <div class="tbi">
    <?php
    $trustItems = [
      ['fa-solid fa-shield-halved','Admin-Verified'],
      ['fa-solid fa-bolt',         'Instant Booking'],
      ['fa-solid fa-truck-fast',   'Doorstep Delivery'],
      ['fa-solid fa-lock',         'Secure Payments'],
      ['fa-solid fa-rotate-left',  'Free Cancellation'],
      ['fa-solid fa-headset',      '24/7 Support'],
    ];
    foreach($trustItems as $t): ?>
    <div class="tit">
      <div class="tiic"><i class="<?= $t[0] ?>"></i></div>
      <div class="titx"><?= $t[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<section class="motions" id="motions">
  <div class="wrap">
    <div class="sec-title">
      <h2>Pure <span>Motion</span></h2>
    </div>
    <div class="video-grid">
      <div class="v-card">
        <video autoplay muted loop playsinline>
          <source src="img/11943164_1080_1920_30fps.mp4" type="video/mp4">
        </video>
        <div class="v-overlay">
          <h3>Supercar Thrills</h3>
        </div>
      </div>
      <div class="v-card">
        <video autoplay muted loop playsinline>
          <source src="img/20493620-hd_1080_1920_60fps.mp4" type="video/mp4">
        </video>
        <div class="v-overlay">
          <h3>Open Road Freedom</h3>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="payments">
  <div class="wrap">
    <div class="sec-title">
      <h2>Secure <span>Payments</span></h2>
    </div>
    <div class="pay-grid">
      <div class="pay-item"><i class="fa-brands fa-cc-visa"></i><span>Visa Card</span></div>
      <div class="pay-item"><i class="fa-brands fa-cc-mastercard"></i><span>Mastercard</span></div>
      <div class="pay-item"><i class="fa-brands fa-google-pay"></i><span>Google Pay</span></div>
      <div class="pay-item"><i class="fa-solid fa-mobile-screen"></i><span>UPI Payment</span></div>
    </div>
  </div>
</section>

<section class="how" id="how">
  <div class="wrap">
    <div class="sec-title">
      <h2>How it <span>Works</span></h2>
    </div>
    <div class="how-grid">
      <div class="step">
        <div class="step-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h3>Find Vehicle</h3>
        <p>Browse through our vast collection of luxury cars and bikes.</p>
      </div>
      <div class="step">
        <div class="step-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <h3>Book Online</h3>
        <p>Select your dates and book with your preferred payment method.</p>
      </div>
      <div class="step">
        <div class="step-icon"><i class="fa-solid fa-key"></i></div>
        <h3>Drive Away</h3>
        <p>Get delivery at your doorstep and enjoy your premium ride.</p>
      </div>
      <div class="step">
        <div class="step-icon"><i class="fa-solid fa-rotate-left"></i></div>
        <h3>Easy Return</h3>
        <p>Hassle-free return process once your journey ends.</p>
      </div>
    </div>
  </div>
</section>

<section class="faq">
  <div class="wrap">
    <div class="sec-title">
      <h2>Common <span>Questions</span></h2>
    </div>
    <div class="faq-grid">
      <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-q">What documents do I need? <i class="fa-solid fa-plus"></i></div>
        <div class="faq-a">You need a valid Driving License, Aadhar Card, and a security deposit.</div>
      </div>
      <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-q">Is insurance included? <i class="fa-solid fa-plus"></i></div>
        <div class="faq-a">Yes, all our rentals come with comprehensive insurance coverage.</div>
      </div>
      <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-q">Can I list my own vehicle? <i class="fa-solid fa-plus"></i></div>
        <div class="faq-a">Yes! Use the 'List Your Vehicle' button to start earning from your asset.</div>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="wrap">
    <div class="f-top">
      <div class="f-col">
        <a href="home.php" class="logo"><img src="img/lo.png" alt="VRide" style="height:38px; mix-blend-mode:screen;"><span class="logo-text">Ride</span></a>
        <p style="margin-top:1.5rem; color:var(--tx2); line-height:1.7; font-weight:600;">Redefining luxury rentals in India. Experience the ride of your life with our premium fleet and seamless service.</p>
      </div>
      <div class="f-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="#">Luxury Cars</a></li>
          <li><a href="#">Super Bikes</a></li>
          <li><a href="#">Vintage Collection</a></li>
        </ul>
      </div>
      <div class="f-col">
        <h4>Support</h4>
        <ul>
          <li><a href="contact.php">Contact Us</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
        </ul>
      </div>
    </div>
    <div class="f-btm">
      <p>&copy; 2026 VRide Anti. All rights reserved.</p>
      <div class="f-socials">
        <i class="fa-brands fa-instagram"></i>
        <i class="fa-brands fa-facebook-f"></i>
        <i class="fa-brands fa-x-twitter"></i>
      </div>
    </div>
  </div>
</footer>

<div id="toast">Please login first.</div>

<script>
function showToast(event) {
  if (event) event.preventDefault();
  const t = document.getElementById('toast');
  if (t.style.display === 'block') return;
  t.style.display = 'block';
  setTimeout(() => {
    window.location.href = 'login.php';
  }, 2300);
}

const heroSlideEls = document.querySelectorAll('.hslide');
let heroCurrent = 0;
let heroTimer = null;

function showHeroSlide(index) {
  if (!heroSlideEls.length) return;
  heroSlideEls[heroCurrent].classList.remove('active');
  heroCurrent = (index + heroSlideEls.length) % heroSlideEls.length;
  heroSlideEls[heroCurrent].classList.add('active');
}

function startHeroAuto() {
  if (heroSlideEls.length <= 1) return;
  if (heroTimer) clearInterval(heroTimer);
  heroTimer = setInterval(() => showHeroSlide(heroCurrent + 1), 4500);
}

function resetHeroAuto() {
  if (heroSlideEls.length <= 1) return;
  if (heroTimer) clearInterval(heroTimer);
  heroTimer = null;
  startHeroAuto();
}

startHeroAuto();

document.getElementById('heroPrev')?.addEventListener('click', () => {
  showHeroSlide(heroCurrent - 1);
  resetHeroAuto();
});

document.getElementById('heroNext')?.addEventListener('click', () => {
  showHeroSlide(heroCurrent + 1);
  resetHeroAuto();
});

document.addEventListener('visibilitychange', () => {
  if (!heroSlideEls.length || heroSlideEls.length <= 1) return;
  if (document.hidden) {
    clearInterval(heroTimer);
    heroTimer = null;
  } else {
    startHeroAuto();
  }
});

function toggleFaq(el) {
  const ans = el.querySelector('.faq-a');
  const icon = el.querySelector('i');
  if(ans.style.display === 'block') {
    ans.style.display = 'none';
    icon.className = 'fa-solid fa-plus';
  } else {
    ans.style.display = 'block';
    icon.className = 'fa-solid fa-minus';
  }
}
</script>
<?php include dirname(__DIR__) . '/lib/firebase_script.php'; ?>

</body>
</html>
