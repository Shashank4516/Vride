<?php
require_once 'db.php';
if (!isLoggedIn()) {
  redirect('home.php');
}

$pageTitle = 'VRide — Premium Vehicle Rentals';
$pdo = getDB();
$featured = [];
if ($pdo) {
    $featured = $pdo->query("SELECT * FROM vehicles WHERE status='approved' ORDER BY created_at DESC LIMIT 8")->fetchAll();
}

$bookedNowIds = [];
$bookedNowUntilById = [];
if ($pdo) {
  $bookedRows = $pdo->query("SELECT vehicle_id, MAX(return_date) AS booked_until FROM bookings WHERE status='approved' AND CURDATE() BETWEEN pickup_date AND return_date GROUP BY vehicle_id")->fetchAll();
  foreach ($bookedRows as $row) {
    $vehicleId = (int)($row['vehicle_id'] ?? 0);
    if ($vehicleId > 0) {
      $bookedNowUntilById[$vehicleId] = $row['booked_until'] ?? null;
    }
  }
  $bookedNowIds = array_keys($bookedNowUntilById);
}

$realVehicles = [
  ["name"=>"Lamborghini Huracán","type"=>"4wheeler","price"=>"₹12,000",
   "imgs"=>["https://images.unsplash.com/photo-1657217674164-9cbf85acfc6d?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fExhbWJvcmdoaW5pJTIwSHVyYWMlQzMlQTFufGVufDB8fDB8fHww",
            "https://images.unsplash.com/photo-1657769106786-b6f50ac90f5f?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fExhbWJvcmdoaW5pJTIwSHVyYWMlQzMlQTFufGVufDB8fDB8fHww",
            "https://images.unsplash.com/photo-1519245659620-e859806a8d3b?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8TGFtYm9yZ2hpbmklMjBIdXJhYyVDMyVBMW58ZW58MHx8MHx8fDA%3D"],
   "tag"=>"HOT","seats"=>2,"speed"=>"325 km/h","fuel"=>"Petrol","city"=>"Mumbai","id"=>1],
  ["name"=>"Royal Enfield Classic 350","cat"=>"Cruiser Bike","type"=>"2wheeler","price"=>"₹350",
   "imgs"=>["https://images.unsplash.com/photo-1694956792421-e946fff94564?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cm95YWwlMjBlbmZpZWxkJTIwY2xhc3NpYyUyMDM1MHxlbnwwfHwwfHx8MA%3D%3D",
            "https://images.unsplash.com/photo-1721543480826-b7e5ff28a3cb?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MjB8fHJveWFsJTIwZW5maWVsZCUyMGNsYXNzaWMlMjAzNTB8ZW58MHx8MHx8fDA%3D",
            "https://images.unsplash.com/photo-1723120589136-7522d0b05eb3?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fHJveWFsJTIwZW5maWVsZCUyMGNsYXNzaWMlMjAzNTB8ZW58MHx8MHx8fDA%3D"],
   "tag"=>"POPULAR","seats"=>2,"speed"=>"130 km/h","fuel"=>"Petrol","city"=>"Delhi","id"=>2],
  ["name"=>"Mercedes-Benz GLE","cat"=>"Luxury SUV","type"=>"4wheeler","price"=>"₹6,500",
   "imgs"=>["https://images.unsplash.com/photo-1654306369985-0fb9e1a2baf5?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OHx8TWVyY2VkZXMtQmVueiUyMEdMRXxlbnwwfHwwfHx8MA%3D%3D",
            "https://images.unsplash.com/photo-1654306489816-fa96b11518f8?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
            "https://images.unsplash.com/photo-1669234226129-8ede05b40eff?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"],
   "tag"=>"ELITE","seats"=>5,"speed"=>"230 km/h","fuel"=>"Diesel","city"=>"Bangalore","id"=>3],
  ["name"=>"Yamaha MT-15 V2","cat"=>"Naked Sport","type"=>"2wheeler","price"=>"₹450",
   "imgs"=>["https://images.unsplash.com/photo-1722720251730-3f5df2030e2c?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8WWFtYWhhJTIwTVQtMTUlMjBWMiUyMGJsdWV8ZW58MHx8MHx8fDA%3D",
            "https://images.unsplash.com/photo-1761583780505-a4edc9ecec78?q=80&w=327&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
            "https://images.unsplash.com/photo-1761583780655-f66645789e21?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8WWFtYWhhJTIwTVQtMTUlMjBWMiUyMGJsdWV8ZW58MHx8MHx8fDA%3D"],
   "tag"=>"NEW","seats"=>2,"speed"=>"145 km/h","fuel"=>"Petrol","city"=>"Pune","id"=>4],
  ["name"=>"Porsche 911 Turbo S","cat"=>"Sports Car","type"=>"4wheeler","price"=>"₹15,000",
   "imgs"=>["https://images.unsplash.com/photo-1698131789135-9328c3e5644a?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8UG9yc2NoZSUyMDkxMSUyMFR1cmJvJTIwU3xlbnwwfHwwfHx8MA%3D%3D",
            "https://images.unsplash.com/photo-1698131788896-87ed9eb974c1?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fFBvcnNjaGUlMjA5MTElMjBUdXJibyUyMFN8ZW58MHx8MHx8fDA%3D",
            "https://images.unsplash.com/photo-1698131789050-4a9ec6fb27cc?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fFBvcnNjaGUlMjA5MTElMjBUdXJibyUyMFN8ZW58MHx8MHx8fDA%3D"],
   "tag"=>"VIP","seats"=>4,"speed"=>"330 km/h","fuel"=>"Petrol","city"=>"Mumbai","id"=>5],
  ["name"=>"Honda Activa 6G","cat"=>"Scooter","type"=>"2wheeler","price"=>"₹200",
   "imgs"=>["https://images.unsplash.com/photo-1744298350102-7db880bf551c?q=80&w=2008&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
            "https://images.unsplash.com/photo-1621417696521-5e9fb2e436a9?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fHJlZCUyMEhvbmRhJTIwQWN0aXZhJTIwNkd8ZW58MHx8MHx8fDA%3D",
            "https://media.istockphoto.com/id/1283756297/photo/speedmer-moped-with-scratched-muddy-glass-close-up-photos.webp?a=1&b=1&s=612x612&w=0&k=20&c=Toe6RyKev6tp7QijTaDKfRrWbPx_j7E7vddQDx8jx8c="],
   "tag"=>"BUDGET","seats"=>2,"speed"=>"90 km/h","fuel"=>"Petrol","city"=>"Chennai","id"=>6],
  ["name"=>"Mahindra Thar 4x4","cat"=>"Off-Road","type"=>"4wheeler","price"=>"₹3,000",
   "imgs"=>["https://images.unsplash.com/photo-1710225358761-4f5891df657d?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8dGhhcnxlbnwwfHwwfHx8MA%3D%3D",
            "https://images.unsplash.com/photo-1710225395366-b0bfc6514eb0?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OHx8dGhhcnxlbnwwfHwwfHx8MA%3D%3D",
            "https://images.unsplash.com/photo-1710225410609-4557fefb50fd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fHRoYXJ8ZW58MHx8MHx8fDA%3D"],
   "tag"=>"ADVENTURE","seats"=>4,"speed"=>"160 km/h","fuel"=>"Diesel","city"=>"Goa","id"=>7],
  ["name"=>"KTM Duke 390","cat"=>"Naked Sport","type"=>"2wheeler","price"=>"₹550",
   "imgs"=>["https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=800&q=80",
            "https://images.unsplash.com/photo-1589874876262-6af49c17b326?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fGR1a2UlMjAzOTB8ZW58MHx8MHx8fDA%3D",
            "https://images.unsplash.com/photo-1670012300918-6e0dda76af1a?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fGR1a2UlMjAzOTB8ZW58MHx8MHx8fDA%3D"],
   "tag"=>"SPORTY","seats"=>2,"speed"=>"167 km/h","fuel"=>"Petrol","city"=>"Hyderabad","id"=>8],
];


$formattedFeatured = array_map(function($v){
  $imgs = array_filter([$v['image'], $v['image2'] ?? null, $v['image3'] ?? null]);
  if(empty($imgs)) $imgs = ["https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80"];
  return [
    "name"=>$v['title'],"cat"=>$v['category']??'Vehicle',"type"=>$v['type'],
    "price"=>"₹".number_format($v['final_price']??$v['price_per_day']),
    "imgs"=>$imgs,
    "tag"=>"NEW","seats"=>"N/A","speed"=>"N/A","fuel"=>"N/A","city"=>$v['city']??'India',"id"=>$v['id']
  ];
}, $featured);

foreach ($formattedFeatured as &$vf) {
  $vf['is_booked_now'] = in_array((int)($vf['id'] ?? 0), $bookedNowIds, true);
  $vf['booked_until'] = $bookedNowUntilById[(int)($vf['id'] ?? 0)] ?? null;
}
unset($vf);

$displayVehicles = array_merge($formattedFeatured, $realVehicles);
// Keep only 8 latest vehicles on the homepage. They should ideally be sorted, which we assume is done by the query.
$displayVehicles = array_slice($displayVehicles, 0, 8);

$heroSlides = [
 
      ["img/1 (3).jpg", "Mahindra Thar"],
      ["img/1 (4).jpg", "Porsche 911"],
      ["img/image.png", "Lamborghini Huracán"],
      
      
];

foreach ($displayVehicles as &$dv) {
  if (!isset($dv['is_booked_now'])) $dv['is_booked_now'] = false;
  if (!isset($dv['booked_until'])) $dv['booked_until'] = null;
}
unset($dv);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="preconnect" href="https://images.unsplash.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
<style>
:root{--bl:#1A8CFF;--blg:rgba(26,140,255,.28);--bk:#000;--bg:#050709;--bg2:#080B12;--bg3:#0C101A;--cd:#0A0D17;--br:rgba(26,140,255,.14);--tx:#C8D4F0;--tx2:#5A6A8E;--wh:#fff;--ok:#00D68F;--rd:#FF3860;--yn:#FFB830;--gd:#F5C842;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}html{scroll-behavior:smooth;}
body{background:var(--bk);color:var(--tx);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;overflow-x:hidden;}
::-webkit-scrollbar{width:3px;}::-webkit-scrollbar-thumb{background:var(--bl);}
a{text-decoration:none;color:inherit;}

/* ══ NAV ══ */
nav {
  position:fixed; top:0; left:0; right:0; z-index:500;
  display:flex; align-items:center; justify-content:space-between;
  padding:0 3.5rem; height:64px;
  background:rgba(5,7,9,0.98); backdrop-filter:blur(20px);
  border-bottom:1px solid transparent; transition:all 0.4s;
}
nav.sc{background:rgba(14, 20, 27, 1); border-bottom:1px solid rgba(255,255,255,0.08);}
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
@media(max-width:768px){.logo-img{height:30px;}.logo-text{font-size:1.28rem;letter-spacing:.05em;margin-left:-0.3rem;transform:translateY(7px);}}
.nav-links { display:flex; gap:2.2rem; list-style:none; align-items:center; }
.nav-links a {
  color:rgba(226,232,240,0.6); font-size:0.75rem; font-weight:600;
  letter-spacing:0.04em; text-transform:uppercase; transition:color 0.3s;
  position:relative; padding: 0.5rem 0;
}
.nav-links a::after {
  content:''; position:absolute; bottom:0; left:0; width:0; height:1.5px;
  background:#3B82F6; transition:width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.nav-links a:hover, .nav-links a.on { color:#fff; }
.nav-links a:hover::after, .nav-links a.on::after { width:100%; }
.nb { display:flex; gap:0.8rem; align-items:center; }
.nbo {
  padding:0.45rem 1.2rem; font-size:0.75rem; font-weight:600;
  border-radius:6px; border:1px solid rgba(255,255,255,0.12);
  color:rgba(226,232,240,0.7); background:transparent; transition:all 0.3s;
}
.nbo:hover { border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.04); }
.nbf {
  padding:0.45rem 1.25rem; font-size:0.75rem; font-weight:700;
  border-radius:6px; background:#3B82F6; color:#fff; border:none;
  transition:all 0.3s; box-shadow:0 4px 12px rgba(59,130,246,0.2);
}
.nbf:hover { background:#2563EB; transform:translateY(-1px); }
.nav-user { font-size:0.75rem; color:rgba(226,232,240,0.45); display:flex; align-items:center; gap:0.5rem; }

.flash-banner {
  width: min(920px, 92vw); margin: 76px auto 0; padding: .72rem 1.1rem; font-size: .84rem;
  font-weight: 600; border-radius: 11px; line-height: 1.45; text-align: center;
  position: relative; z-index: 490;
}
.flash-banner.flash-success { background: rgba(0,214,127,.14); border: 1px solid rgba(0,214,127,.34); color: var(--ok); }
.flash-banner.flash-error   { background: rgba(255,56,96,.12); border: 1px solid rgba(255,56,96,.26); color: var(--rd); }

/* ══ HERO ══ */
#hero {
  position: relative;
  height: 100vh;
  min-height: 640px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding-left: 3.5rem;
  background: #050709;
  overflow: hidden;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 8;
  background:
    radial-gradient(circle at center, rgba(5,7,9,0.08), rgba(5,7,9,0.32) 68%),
    linear-gradient(to bottom, rgba(5,7,9,0.08), rgba(5,7,9,0.42));
  pointer-events: none;
}

.hero-center {
  position: relative;
  z-index: 12;
  width: min(720px, 48%);
  text-align: left;
  padding: 0 0 0 0;
  background: transparent;
  border: none;
  box-shadow: none;
  backdrop-filter: none;
  animation: heroFloat 4s ease-in-out infinite;
}

.hero-kicker {
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: .38em;
  text-transform: uppercase;
  color: var(--bl);
  margin-bottom: 1rem;
}

.hero-title {
  font-size: clamp(2.2rem, 4.8vw, 3.6rem);
  line-height: 1.02;
  text-transform: none;
  font-weight: 800;
  color: var(--wh);
  margin-bottom: .45rem;
  text-shadow: 0 8px 30px rgba(0,0,0,.45);
}

.hero-title span {
  color: var(--bl);
  display: inline-block;
  animation: namePulse 2.8s ease-in-out infinite;
}

.hero-sub {
  font-size: .98rem;
  line-height: 1.6;
  color: var(--tx2);
  max-width: 560px;
  margin: 0 0 1.8rem 0;
  text-shadow: 0 4px 18px rgba(0,0,0,.28);
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-start;
  flex-wrap: wrap;
}

.hero-note {
  margin-top: 1.2rem;
  font-size: .72rem;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: rgba(200,212,240,.55);
}

/* ══ HERO CAR — Fixed slideshow container ══ */
.hcar{
  position:absolute;
  top:0;
  bottom:0;
  /* align flush to the right edge so no gap remains */
  right:0;
  /* take the remaining 52% to pair with .hero-center at 48% */
  width:52%;
  max-width:none;
  height:100%;
  z-index:6;
  overflow:hidden;
  /* remove rounded corners so it visually connects to the left side */
  border-radius:0;
  box-shadow:0 20px 60px rgba(2,6,23,0.6);
  /* remove visible border so it doesn't look like a separate panel */
  border:none;
  backdrop-filter:blur(6px);
}
/* Background slideshow (full-bleed) */
.hero-bg{position:absolute;inset:0;z-index:4;overflow:hidden;pointer-events:none}
.hbslide{position:absolute;inset:0;opacity:0;transition:opacity .9s ease,transform .9s cubic-bezier(.16,1,.3,1);transform:scale(1.06);}
.hbslide.active{opacity:1;transform:none}
.hbslide img{width:100%;;height:100%;object-fit:cover;object-position:center;display:block;filter:blur(8px) brightness(.46);transform:scale(1.06)}
.hero-overlay{z-index:6}
/* Each slide is position:absolute, fills the container */
.hslide{
  position:absolute;inset:0;
  opacity:0;
  transition:opacity .7s ease, transform .6s cubic-bezier(.16,1,.3,1);
  pointer-events:none;
  border-radius:inherit;
  overflow:hidden;
}
.hslide.active{opacity:1;pointer-events:auto;}
.hslide img{
  width:100%;height:100%;
  object-fit:cover;
  object-position:center center;
  display:block;
  border-radius:inherit;
}
/* Dark gradient overlay so text stays readable */
.hslide::after{
  content:'';
  position:absolute;inset:0;
  /* darken and blend the left edge so the slideshow merges with the hero content */
  background:linear-gradient(to left, rgba(5,7,9,0.95) 0%, rgba(5,7,9,0.65) 20%, rgba(5,7,9,0.32) 40%, transparent 60%);
}
/* Ground shadow */
.hcar-shadow{position:absolute;bottom:0;left:0;right:0;height:120px;background:linear-gradient(to top,rgba(0,0,0,.9),transparent);z-index:6;pointer-events:none;}

/* Right-corner carousel controls */
.hcar-ctrl {
  position: absolute;
  right: 1rem;
  bottom: 1rem;
  z-index: 14;
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .55rem .7rem;
  border-radius: 999px;
  background: rgba(2,6,23,.5);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,.12);
}
.harr {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.25);
  background: rgba(15,23,42,.45);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .25s ease;
}
.harr:hover {
  border-color: var(--bl);
  color: var(--bl);
  background: rgba(26,140,255,.16);
}
.hdots {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
}
.hd {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  border: 0;
  background: rgba(255,255,255,.38);
  cursor: pointer;
  transition: all .25s ease;
}
.hd.on {
  width: 18px;
  border-radius: 999px;
  background: var(--bl);
  box-shadow: 0 0 12px rgba(26,140,255,.45);
}

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
@keyframes heroPop{from{opacity:0;transform:translateY(22px) scale(.98);}to{opacity:1;transform:none;}}
@keyframes heroFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-8px);}}
@keyframes namePulse{0%,100%{transform:scale(1);filter:drop-shadow(0 0 0 rgba(26,140,255,0));}50%{transform:scale(1.03);filter:drop-shadow(0 0 14px rgba(26,140,255,.45));}}

@media(max-width:768px){
  .hero-center{
    width:min(100% - 1.5rem, 760px);
  }
  .hero-actions{
    flex-direction:column;
    align-items:stretch;
  }
  .hero-actions .hbn,
  .hero-actions .hbo{
    width:100%;
    justify-content:center;
  }

  /* On small screens show slideshow full-bleed behind message */
  .hcar{position:absolute;inset:0;right:0;width:100%;max-width:none;border-radius:0;box-shadow:none;border:none;backdrop-filter:none}
  .hcar-ctrl{right:.75rem;bottom:.75rem;padding:.5rem .6rem}
}

/* ══ SEARCH BAR ══ */
#sb{background:var(--bg2);border-bottom:1px solid var(--br);}
.sbi{max-width:1200px;margin:0 auto;display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;padding:1.6rem 3rem;}
.sbg{display:flex;flex-direction:column;gap:.35rem;flex:1;min-width:130px;}
.sbl{font-size:.56rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--tx2);}
.sbin{background:var(--bg3);border:1px solid rgba(255,255,255,.07);color:var(--tx);font-size:.84rem;padding:.68rem 1rem;outline:none;transition:border-color .3s;}
.sbin:focus{border-color:var(--bl);}
.sbbtn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 2rem;background:var(--bl);color:var(--bk);font-size:.76rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;border:none;cursor:pointer;transition:all .3s;white-space:nowrap;}
.sbbtn:hover{background:#3AB0FF;box-shadow:0 0 18px var(--blg);}

/* ══ TRUST BAR ══ */
#tb{background:linear-gradient(to right,rgba(26,140,255,.05),rgba(26,140,255,.02),rgba(26,140,255,.05));border-top:1px solid var(--br);border-bottom:1px solid var(--br);padding:1.3rem 3rem;}
.tbi{max-width:1200px;margin:0 auto;display:flex;justify-content:space-around;flex-wrap:wrap;gap:1rem;}
.tit{display:flex;align-items:center;gap:.65rem;}
.tiic{font-size:1rem;color:var(--bl);width:28px;text-align:center;}
.titx{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(200,212,240,.65);}

/* ══ SECTION ══ */
.sec{padding:5.5rem 3rem;}
.sec-alt{background:var(--bg2);}
.si{max-width:1200px;margin:0 auto;}
.stag{font-size:.6rem;font-weight:700;letter-spacing:.32em;text-transform:uppercase;color:var(--bl);display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;}
.stag::before{content:'';width:18px;height:1.5px;background:var(--bl);}
.sh{font-size:clamp(1.9rem,3.5vw,2.8rem);font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--wh);line-height:1;}
.sh .dim{-webkit-text-stroke:1px rgba(26,140,255,.2);color:transparent;}
.st{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;margin-bottom:3.5rem;}

/* ══ FILTER TABS ══ */
.fstrip{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:2.4rem;}
.ftab{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem 1.3rem;border-radius:30px;background:transparent;border:1px solid rgba(255,255,255,.07);color:var(--tx2);font-size:.7rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:all .3s;font-family:inherit;}
.ftab:hover,.ftab.on{background:var(--bl);color:var(--bk);border-color:var(--bl);}
.ftab i{font-size:.75rem;}

/* ══ VEHICLE CARDS ══ */
.vg{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1.4rem;}
.vc{
  background: #0A0D17;
  border: 1px solid rgba(255,255,255,0.06);
  overflow: hidden;
  position: relative;
  transition: transform 0.4s cubic-bezier(.16,1,.3,1), border-color 0.3s, box-shadow 0.4s;
}
.vc:hover {
  transform: translateY(-8px);
  border-color: rgba(59,130,246,0.3);
 
}
.vcb{position:absolute;top:.8rem;left:.8rem;z-index:3;padding:.18rem .65rem;font-size:.56rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;}
.bh{background:rgba(255,56,96,.12);border:1px solid rgba(255,56,96,.28);color:#FF3860;}
.bp{background:rgba(26,140,255,.12);border:1px solid rgba(26,140,255,.28);color:var(--bl);}
.be{background:rgba(245,200,66,.08);border:1px solid rgba(245,200,66,.25);color:var(--gd);}
.bn{background:rgba(0,214,143,.08);border:1px solid rgba(0,214,143,.25);color:var(--ok);}
.bv{background:rgba(168,85,247,.12);border:1px solid rgba(168,85,247,.25);color:#A855F7;}
.bb{background:rgba(0,214,143,.08);border:1px solid rgba(0,214,143,.25);color:var(--ok);}
.ba{background:rgba(255,184,48,.08);border:1px solid rgba(255,184,48,.25);color:var(--yn);}
.bs{background:rgba(255,56,96,.1);border:1px solid rgba(255,56,96,.25);color:#FF3860;}
.vcb-booked{top:2.4rem;background:rgba(255,56,96,.12);border:1px solid rgba(255,56,96,.28);color:#FF3860;}
.vct{position:absolute;top:.8rem;right:.8rem;z-index:3;width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:var(--bl);font-size:.75rem;}

/* ── FIXED card image: uniform height, always fills, carousel ── */
.vcim{
  position:relative;
  height:270px;
  overflow:hidden;
  background:var(--bg3);
  display:flex;align-items:center;justify-content:center;
}
.vcim-wrap{position:absolute;inset:0;}
.vcim-slide{
  position:absolute;inset:0;
  opacity:0;
  transition:opacity .5s ease, transform .6s cubic-bezier(.16,1,.3,1);
  background:var(--bg3);
}
.vcim-slide.on{opacity:1;z-index:1;}
.vcim img{
  width:100%;height:100%;
  object-fit:cover;
  object-position:center;
  display:block;
}
.vc:hover .vcim .vcim-slide.on img{transform:scale(1.06);filter:brightness(1.08);}

/*Carousel Elements*/
.vcar-arr{
  position:absolute;top:50%;transform:translateY(-50%);
  width:28px;height:28px;border-radius:50%;
  
  border:1px solid rgba(255,255,255,.1);
  color:var(--wh);display:flex;align-items:center;justify-content:center;
  cursor:pointer;z-index:10;opacity:0;transition:all .3s;
  font-size:.7rem;
}
.vcar-arr-l{left:.6rem;}
.vcar-arr-r{right:.6rem;}
.vc:hover .vcar-arr{opacity:1;}
.vcar-arr:hover{background:var(--bl);color:var(--bk);border-color:var(--bl);}

.vcar-dots{
  position:absolute;bottom:.8rem;left:50%;transform:translateX(-50%);
  display:flex;gap:.35rem;z-index:10;
}
.vcar-dot{
  width:6px;height:6px;border-radius:50%;
  background:rgba(255,255,255,.3);cursor:pointer;transition:all .3s;
}
.vcar-dot.on{background:var(--bl);width:14px;border-radius:4px;}


.vc:hover .vcar-count{opacity:1;}

.vcov{position:absolute;inset:0;background:linear-gradient(to top,var(--cd) 5%,transparent 60%);z-index:5;pointer-events:none;}


.vcbd{padding:1.1rem 1.3rem 1.4rem;}
.vcc{font-size:.56rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--bl);margin-bottom:.22rem;}
.vcn{font-size:1.18rem;font-weight:700;color:var(--wh);margin-bottom:.75rem;line-height:1.1;}
.vcsp{display:flex;gap:.9rem;flex-wrap:wrap;margin-bottom:.85rem;}
.vcs{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;color:var(--tx2);}
.vcs i{color:var(--bl);font-size:.65rem;}
.vcf{display:flex;align-items:center;justify-content:space-between;padding-top:.7rem;border-top:1px solid rgba(255,255,255,.05);}
.vcp{font-size:1.22rem;font-weight:700;color:var(--bl);line-height:1;}
.vcp small{font-size:.62rem;color:var(--tx2);}
.vcpl{font-size:.5rem;letter-spacing:.15em;text-transform:uppercase;color:var(--tx2);margin-bottom:.1rem;}
.vclo{display:inline-flex;align-items:center;gap:.3rem;font-size:.65rem;color:var(--tx2);margin-top:.12rem;}
.vclo i{color:var(--bl);font-size:.6rem;}
.vcb2{display:flex;gap:.4rem;}
.bsm{padding:.42rem 1rem;font-family:inherit;font-size:.67rem;font-weight:700;letter-spacing:.13em;text-transform:uppercase;border-radius:2px;cursor:pointer;transition:all .3s;border:none;}
.bdt{background:transparent;border:1px solid rgba(255,255,255,.1);color:var(--tx2);}
.bdt:hover{border-color:var(--bl);color:var(--bl);}
.brt{background:var(--bl);color:var(--bk);box-shadow:0 0 10px var(--blg);}
.brt:hover{background:#3AB0FF;}
.bsm-disabled{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:var(--tx2);cursor:not-allowed;pointer-events:none;opacity:.6;}
.btn-disabled{pointer-events:none;opacity:.6;filter:saturate(.7);}

/* ══ PAYMENTS ══ */
#pay{padding:5.5rem 3rem;background:linear-gradient(135deg,var(--bg2),var(--bg));}
.payg{display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;align-items:center;max-width:1200px;margin:0 auto;}
.paym{display:grid;grid-template-columns:repeat(3,1fr);gap:.9rem;margin-top:2rem;}
.pmc{background:var(--cd);border:1px solid rgba(255,255,255,.05);padding:1.3rem 1rem;text-align:center;transition:all .3s;position:relative;overflow:hidden;}
.pmc:hover{border-color:rgba(26,140,255,.22);transform:translateY(-4px);}
.pmc::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:var(--bl);opacity:0;transition:opacity .3s;}
.pmc:hover::after{opacity:1;}
.pmi{font-size:1.6rem;margin-bottom:.65rem;display:flex;align-items:center;justify-content:center;color:var(--bl);}
.pmn{font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--wh);margin-bottom:.25rem;}
.pmd{font-size:.68rem;color:var(--tx2);line-height:1.5;}
.payr{background:var(--cd);border:1px solid rgba(26,140,255,.12);padding:2.5rem;position:relative;overflow:hidden;}
.payr::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--bl),transparent);}
.paysec{display:flex;flex-direction:column;gap:.95rem;margin-top:1.4rem;}
.psi{display:flex;align-items:flex-start;gap:.85rem;}
.psic{width:36px;height:36px;background:rgba(26,140,255,.1);border:1px solid rgba(26,140,255,.18);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.88rem;color:var(--bl);flex-shrink:0;}
.pst{font-size:.76rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--wh);}
.psd{font-size:.74rem;color:var(--tx2);margin-top:.15rem;line-height:1.5;}

/* ══ HOW IT WORKS ══ */
.hwg{display:grid;grid-template-columns:repeat(4,1fr);gap:1.4rem;}
.hws{padding:2rem 1.6rem;background:var(--cd);border:1px solid rgba(255,255,255,.05);position:relative;transition:all .35s;}
.hws:hover{border-color:rgba(26,140,255,.2);transform:translateY(-5px);}
.hwn{font-size:2.8rem;font-weight:900;color:rgba(26,140,255,.1);line-height:1;margin-bottom:.8rem;}
.hwi{font-size:1.5rem;margin-bottom:.9rem;color:var(--bl);display:flex;}
.hwt{font-size:.88rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--wh);margin-bottom:.45rem;}
.hwd{font-size:.79rem;color:var(--tx2);line-height:1.72;}
.hwl{position:absolute;top:50%;right:-1px;width:1.5px;height:35%;transform:translateY(-50%);background:linear-gradient(to bottom,transparent,var(--bl),transparent);opacity:.22;}

/* ══ TESTIMONIALS ══ */
.teg{display:grid;grid-template-columns:repeat(3,1fr);gap:1.4rem;}
.tec{background:var(--cd);border:1px solid rgba(255,255,255,.05);padding:1.8rem;position:relative;}
.tec::before{content:'"';position:absolute;top:1rem;right:1.4rem;font-size:4rem;font-family:serif;color:rgba(26,140,255,.08);line-height:1;}
.tes{color:var(--gd);font-size:.85rem;margin-bottom:.9rem;display:flex;gap:.15rem;}
.tet{font-size:.83rem;line-height:1.85;color:var(--tx2);margin-bottom:1.2rem;font-style:italic;}
.ten{font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--wh);}
.ter{font-size:.68rem;color:var(--bl);margin-top:.12rem;}

/* ══ FAQ ══ */
.faqw{display:flex;flex-direction:column;gap:.7rem;max-width:800px;}
.faqi{background:var(--cd);border:1px solid rgba(255,255,255,.05);}
.faqq{padding:1.2rem 1.4rem;font-size:.88rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--wh);cursor:pointer;display:flex;justify-content:space-between;align-items:center;transition:color .3s;font-family:inherit;}
.faqq:hover{color:var(--bl);}
.faqq .faq-icon{color:var(--bl);font-size:.9rem;transition:transform .3s;}
.faqq.open .faq-icon{transform:rotate(45deg);}
.faqa{padding:0 1.4rem;max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s;}
.faqa.op{max-height:200px;padding-bottom:1.2rem;}
.faqa p{font-size:.82rem;color:var(--tx2);line-height:1.8;}

/* ══ OWNER CTA ══ */
#octa{padding:5rem 3rem;background:linear-gradient(135deg,rgba(26,140,255,.05) 0%,var(--bg2) 50%,rgba(26,140,255,.03) 100%);border-top:1px solid var(--br);border-bottom:1px solid var(--br);}
.ocg{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;}
.of{display:flex;flex-direction:column;gap:1rem;margin-top:1.4rem;}
.ofi{display:flex;align-items:flex-start;gap:.9rem;}
.ofic{width:38px;height:38px;background:rgba(26,140,255,.1);border:1px solid rgba(26,140,255,.18);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.88rem;color:var(--bl);flex-shrink:0;margin-top:.1rem;}
.ofit{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--wh);}
.ofid{font-size:.74rem;color:var(--tx2);margin-top:.14rem;line-height:1.5;}
.ofc{background:var(--cd);border:1px solid rgba(255,255,255,.07);padding:2.4rem;}
.ofct{font-size:1.2rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--wh);margin-bottom:.3rem;}
.ofcs{font-size:.78rem;color:var(--tx2);margin-bottom:1.6rem;}

/* ══ NEWSLETTER ══ */
#nl{padding:4rem 3rem;background:var(--bg);}
.nli{max-width:560px;margin:0 auto;text-align:center;}
.nlf{display:flex;gap:.6rem;margin-top:1.6rem;}
.nlfin{flex:1;background:var(--bg3);border:1px solid rgba(255,255,255,.07);color:var(--tx);font-size:.84rem;padding:.78rem 1.2rem;outline:none;transition:border-color .3s;}
.nlfin:focus{border-color:var(--bl);}
.nlfb{display:inline-flex;align-items:center;gap:.45rem;padding:.78rem 1.7rem;background:var(--bl);color:var(--bk);font-family:inherit;font-size:.74rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;border:none;cursor:pointer;transition:all .3s;white-space:nowrap;}
.nlfb:hover{background:#3AB0FF;}

/* Footer */
.footer { padding: 5rem 0 2rem; background: var(--bk); border-top: 1px solid rgba(255,255,255,0.06); }
.footer .wrap { max-width:1200px; margin:0 auto; padding:0 3rem; }
.f-top { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 4rem; margin-bottom: 5rem; }
.f-col h4 { color: var(--wh); margin-bottom: 2rem; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.12em; font-weight: 800; }
.f-col ul { list-style: none; display: flex; flex-direction: column; gap: 1rem; }
.f-col a { color: var(--tx2); font-size: 0.95rem; transition: 0.3s; font-weight: 600; }
.f-col a:hover { color: var(--bl); padding-left: 5px; }
.f-btm { border-top: 1px solid rgba(255,255,255,0.06); padding-top: 2.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--tx2); gap: 1rem; }
.fcp { flex: 1; text-align: center; }
.f-socials { display: flex; gap: 1.8rem; }
.f-socials i { font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
.f-socials i:hover { color: var(--bl); transform: scale(1.1); }

/* ══ MODAL ══ */
.mov{position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:2000;display:none;align-items:center;justify-content:center;padding:1.5rem;}
.mov.open{display:flex;}
.mo{background:var(--cd);border:1px solid rgba(255,255,255,.07);max-width:700px;width:100%;max-height:90vh;overflow-y:auto;position:relative;}
.mcl{position:absolute;top:1rem;right:1rem;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.07);border:none;color:var(--tx);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem;z-index:2;transition:all .3s;}
.mcl:hover{background:var(--rd);color:#fff;}
.moim{height:225px;overflow:hidden;background:var(--bg3);}
.moim img{width:100%;height:100%;object-fit:cover;display:block;}
.mob{padding:2rem;}
.msg{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin:1.1rem 0 1.4rem;}
.msp{padding:.9rem;background:var(--bg3);border:1px solid rgba(255,255,255,.04);}
.mspl{font-size:.54rem;letter-spacing:.18em;text-transform:uppercase;color:var(--tx2);font-weight:700;margin-bottom:.22rem;}
.mspv{font-size:.98rem;color:var(--bl);font-weight:700;}

/* ══ FORMS ══ */
.fg2{display:flex;flex-direction:column;gap:.38rem;margin-bottom:1.1rem;}
.flb{font-size:.58rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--tx2);}
.fin{background:var(--bg3);border:1px solid rgba(255,255,255,.07);color:var(--tx);font-family:inherit;font-size:.85rem;padding:.68rem 1rem;outline:none;transition:border-color .3s;width:100%;}
.fin:focus{border-color:var(--bl);}
.fin::placeholder{color:var(--tx2);}
select.fin option{background:var(--bg3);}
.frw{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.8rem;font-family:inherit;font-size:.78rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;border:none;cursor:pointer;transition:all .3s;border-radius:2px;}
.btnp{background:var(--bl);color:var(--bk);box-shadow:0 0 18px var(--blg);}
.btnp:hover{background:#3AB0FF;transform:translateY(-2px);}
.btns{background:transparent;border:1.5px solid rgba(26,140,255,.28);color:var(--tx2);}
.btns:hover{border-color:var(--bl);color:var(--bl);}

/* REVEAL */
.rv{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease;}
.rv.show{opacity:1;transform:none;}

@media(max-width:1000px){.hwg{grid-template-columns:1fr 1fr;}.teg{grid-template-columns:1fr 1fr;}.payg{grid-template-columns:1fr;}.ocg{grid-template-columns:1fr;}.fgi{grid-template-columns:1fr 1fr;}.hstats{display:none;}.paym{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){nav{padding:.9rem 1.5rem;}.nav-links{display:none;}.hl{padding:0 1.5rem;padding-bottom:220px;max-width:100%;}.hcar{width:100%;}.hbot{padding:1rem 1.5rem;}.sec{padding:4rem 1.5rem;}.teg{grid-template-columns:1fr;}.hwg{grid-template-columns:1fr;}.fgi{grid-template-columns:1fr;}.msg{grid-template-columns:1fr 1fr;}.sbi{padding:1.2rem 1.5rem;}}

/* ================= Enhancements: Neon, Pop, Hero sync */
.hero-vehicle{
  margin-top:1.1rem;display:flex;flex-direction:column;gap:.35rem;align-items:flex-start;
  background:linear-gradient(180deg, rgba(26,140,255,0.06), rgba(10,13,23,0.28));
  padding:.9rem 1rem;border-radius:10px;border:1px solid rgba(26,140,255,.12);box-shadow:0 8px 30px rgba(26,140,255,.06);
}
.hero-vehicle .hv-tag{font-size:.62rem;font-weight:800;color:var(--bl);letter-spacing:.18em;text-transform:uppercase;background:rgba(26,140,255,.06);padding:.18rem .5rem;border-radius:6px;border:1px solid rgba(26,140,255,.08)}
.hero-vehicle .hv-name{font-size:1.05rem;font-weight:800;color:var(--wh);}
.btn-neon{position:relative;overflow:visible;border-radius:8px;transition:transform .22s ease,box-shadow .22s ease;}
.btn-neon::after{content:'';position:absolute;inset:-2px;border-radius:10px;background:linear-gradient(90deg,rgba(58,176,255,.14),rgba(58,255,184,.08),rgba(168,85,247,.06));filter:blur(8px);opacity:0;transition:opacity .28s;z-index:-1}
.btn-neon:hover{transform:translateY(-3px)}
.btn-neon:hover::after{opacity:1}
.neon-glow{animation:neonPulse 2.6s ease-in-out infinite;color:var(--bl)}
@keyframes neonPulse{0%,100%{text-shadow:0 0 0 rgba(26,140,255,0);}50%{text-shadow:0 0 18px rgba(26,140,255,.45);}}
.pop{animation:popAnim .48s cubic-bezier(.2,.9,.3,1)}
@keyframes popAnim{0%{transform:scale(.96);opacity:.6}60%{transform:scale(1.06);opacity:1}100%{transform:scale(1);opacity:1}}

/* Hero name letter animation */
.hero-title .char{display:inline-block;opacity:0;transform:translateY(18px) scale(.98);transition:transform .46s cubic-bezier(.2,.9,.3,1),opacity .46s;}
.hero-title .char.show{opacity:1;transform:none;}
.hero-title .char.neon{color:var(--bl);text-shadow:0 0 18px rgba(26,140,255,.42)}

/* buttons used in hero: Book and List */
.btn-book{
  background:linear-gradient(90deg,#1A8CFF 0%,#3AB0FF 60%);
  color:#02121a;padding:.58rem 1rem;border-radius:10px;font-weight:800;display:inline-flex;align-items:center;gap:.6rem;border:none;box-shadow:0 10px 32px rgba(26,140,255,.14);transition:transform .18s ease,box-shadow .18s ease;font-size:.92rem;
}
.btn-book:hover{transform:translateY(-4px);box-shadow:0 18px 42px rgba(26,140,255,.18)}
.btn-book i{font-size:0.98rem;color:#02121a}

.btn-list{
  background:transparent;border:1px solid rgba(58,176,255,.18);color:var(--wh);padding:.52rem .95rem;border-radius:10px;font-weight:800;display:inline-flex;align-items:center;gap:.45rem;transition:all .18s;font-size:.9rem;
}
.btn-list:hover{background:rgba(26,140,255,.06);border-color:rgba(58,176,255,.32);transform:translateY(-2px)}

.hero-gmail{
  margin: .4rem 0 .55rem;
  font-size: .95rem; font-weight: 600;
  color: rgba(200,212,240,.82);
  display: flex; align-items: center; gap: .5rem;
  flex-wrap: wrap;
}
.hero-gmail .g-ic{ color:#4285F4;font-size:1.05rem; }

</style>
</head>
<body>
<?php $flash = getFlash(); if ($flash): ?>
<div class="flash-banner flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<!-- NAV -->
<nav id="mn">
  <a href="index.php" class="nl"><img src="img/lo.png" alt="VRide" class="logo-img" fetchpriority="high"><span class="logo-text">Ride</span></a>
  <ul class="nav-links">
    <li><a href="index.php" class="on">Home</a></li>
    <li><a href="vehicles.php">Fleet</a></li>
    <li><a href="#how">How It Works</a></li>
    <li><a href="contact.php">Contact Us</a></li>
    <?php if(!empty($_SESSION['user_id'])): ?>
    <li><a href="dashboard.php">Dashboard</a></li>
    <?php if(!empty($_SESSION['role']) && $_SESSION['role']==='admin'): ?>
    <li><a href="admin.php" class="hi">Admin</a></li>
    <?php endif; ?>
    <?php endif; ?>
  </ul>
  <div class="nb">
    <?php if(!empty($_SESSION['user_id'])): ?>
      <span class="nav-user"><i class="fa-regular fa-circle-user"></i> <?= htmlspecialchars($_SESSION['name']??'') ?></span>
      <a href="logout.php" class="nbo">Logout</a>
    <?php else: ?>
      <a href="login.php" class="nbo">Login</a>
      <a href="register.php" class="nbf">Register</a>
    <?php endif; ?>
  </div>
</nav>

<section id="hero">
  <!-- Background slideshow (full-bleed, blurred) -->
  <div class="hero-bg" id="heroBg">
    <?php foreach($heroSlides as $idx => $sl): ?>
      <div class="hbslide <?= $idx===0?'active':'' ?>" data-index="<?= $idx ?>">
        <img src="<?= $sl[0] ?>" alt="<?= htmlspecialchars($sl[1]) ?>">
      </div>
    <?php endforeach; ?>
  </div>
  <!-- HERO SLIDESHOW — full-bleed, equal-size slides -->
  <div class="hcar" id="heroWrap">
    <?php foreach($heroSlides as $idx => $sl): ?>
    <div class="hslide <?= $idx===0?'active':'' ?>" data-index="<?= $idx ?>" data-title="<?= htmlspecialchars($sl[1]) ?>">
      <img src="<?= $sl[0] ?>" alt="<?= $sl[1] ?>">
    </div>
    <?php endforeach; ?>
    <div class="hcar-shadow"></div>
    <div class="hcar-ctrl">
      <button id="pr" class="harr" type="button" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
      <div class="hdots">
        <?php foreach($heroSlides as $idx => $sl): ?>
          <button class="hd <?= $idx===0 ? 'on' : '' ?>" type="button" aria-label="Go to slide <?= $idx + 1 ?>"></button>
        <?php endforeach; ?>
      </div>
      <button id="nx" class="harr" type="button" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
  </div>

  <div class="hero-overlay"></div>

  <div class="hero-center">
    <div class="hero-kicker">Good to see you</div>
    <h1 class="hero-title">Welcome, <span><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></span></h1>
    <?php if (!empty($_SESSION['email']) && ($_SESSION['auth_via'] ?? '') === 'google'): ?>
    <p class="hero-gmail" title="Signed in with Google"><i class="fa-brands fa-google g-ic" aria-hidden="true"></i><?= htmlspecialchars($_SESSION['email']) ?></p>
    <?php endif; ?>
    <p class="hero-sub">Discover curated vehicles, effortless bookings, and journeys crafted just for you — start exploring your next ride.</p>
    <div class="hero-actions">
      <a href="#fleet" class="hbn btn-book" id="bookNow"><i class="fa-solid fa-calendar-days"></i> BOOK NOW</a>
      <a href="list_vehicle.php" class="hbo btn-list" id="listNow"><i class="fa-solid fa-plus"></i> LIST YOUR VEHICLE</a>
    </div>
    <!-- hero vehicle card removed as requested -->
  </div>

  
</section>

<!-- SEARCH BAR -->
<div id="sb">
  <div class="sbi">
    <div class="sbg">
      <div class="sbl">Vehicle Type</div>
      <select class="sbin" id="sty">
        <option value="">All Types</option>
        <option value="2wheeler">2-Wheeler</option>
        <option value="4wheeler">4-Wheeler</option>
      </select>
    </div>
    <div class="sbg"><div class="sbl">City</div><input class="sbin" id="sct" type="text" placeholder="Mumbai, Delhi, Goa..."></div>
    <div class="sbg"><div class="sbl">Pick-up Date</div><input class="sbin" id="sdt" type="date"></div>
    <div class="sbg">
      <div class="sbl">Max Budget</div>
      <select class="sbin">
        <option value="">Any Budget</option>
        <option>Under ₹500</option>
        <option>₹500–₹2,000</option>
        <option>₹2,000–₹5,000</option>
        <option>₹5,000+</option>
      </select>
    </div>
    <button class="sbbtn" onclick="goSearch()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
  </div>
</div>

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

<!-- FLEET -->
<section class="sec sec-alt" id="fleet">
  <div class="si">
    <div class="st">
      <div>
        <div class="stag">Real Vehicles — Verified Owners</div>
        <div class="sh">FEATURED <span class="dim">FLEET</span></div>
      </div>
      <a href="vehicles.php" class="btn btns" style="border-radius:30px">View All <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="fstrip">
      <button class="ftab on" onclick="fltr('all',this)"><i class="fa-solid fa-flag-checkered"></i> All</button>
      <button class="ftab" onclick="fltr('2wheeler',this)"><i class="fa-solid fa-motorcycle"></i> Bikes &amp; Scooters</button>
      <button class="ftab" onclick="fltr('4wheeler',this)"><i class="fa-solid "></i> Cars &amp; SUVs</button>
    </div>
    <div class="vg" id="vg">
      <?php
      $tagCls=['HOT'=>'bh','POPULAR'=>'bp','ELITE'=>'be','NEW'=>'bn','VIP'=>'bv','BUDGET'=>'bb','ADVENTURE'=>'ba','SPORTY'=>'bs',''=>'bp'];
      /* Reliable fallback images per type */
      $fallback2w = "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=70";
      $fallback4w = "https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=600&q=70";

      foreach($displayVehicles as $i=>$v):
        $tc  = $tagCls[$v['tag']??''] ?? 'bp';
        $is2w = ($v['type']==='2wheeler');
        $isBookedNow = !empty($v['is_booked_now']);
        $rawImg = $v['img'] ?? $v['image'] ?? '';
        /* Replace broken local paths with a reliable online fallback */
        if(empty($rawImg) || str_starts_with($rawImg,'/img/')){
          $rawImg = $is2w ? $fallback2w : $fallback4w;
        }
      ?>
      <div class="vc rv" data-type="<?= $v['type'] ?>" style="transition-delay:<?= ($i%4)*.08 ?>s">
        <?php if(!empty($v['tag'])): ?>
        <div class="vcb <?= $tc ?>"><?= $v['tag'] ?></div>
        <?php endif; ?>
        <?php if($isBookedNow): ?>
        <div class="vcb vcb-booked">Booked<?= !empty($v['booked_until']) ? ' till '.htmlspecialchars(date('d M', strtotime($v['booked_until']))) : '' ?></div>
        <?php endif; ?>
        <div class="vct"><i class="fa-solid <?= $is2w ? 'fa-motorcycle' : 'fa-car' ?>"></i></div>

        <div class="vcim" data-id="<?= $v['id']??0 ?>">
          <div class="vcim-wrap">
            <?php foreach($v['imgs'] as $jx => $imgUrl): ?>
              <div class="vcim-slide <?= $jx===0?'on':'' ?>" data-idx="<?= $jx ?>">
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($v['name']??'') ?>" loading="lazy" onload="this.classList.add('loaded')">
              </div>
            <?php endforeach; ?>
          </div>
          
          <?php if(count($v['imgs']) > 1): ?>
            <div class="vcar-arr vcar-arr-l" onclick="moveCar(this,-1,event)"><i class="fa-solid fa-chevron-left"></i></div>
            <div class="vcar-arr vcar-arr-r" onclick="moveCar(this,1,event)"><i class="fa-solid fa-chevron-right"></i></div>
            <div class="vcar-dots">
              <?php foreach($v['imgs'] as $jx => $imgUrl): ?>
                <div class="vcar-dot <?= $jx===0?'on':'' ?>" onclick="jumpCar(this,<?= $jx ?>,event)"></div>
              <?php endforeach; ?>
            </div>
            <div class="vcar-count"><i class="fa-solid fa-camera"></i> <span class="vcar-cur">1</span>/<?= count($v['imgs']) ?></div>
          <?php endif; ?>

          <div class="img-placeholder">
            <i class="fa-solid <?= $is2w ? 'fa-motorcycle' : 'fa-car' ?>"></i>
            <span><?= htmlspecialchars($v['cat']??'Vehicle') ?></span>
          </div>
          <div class="vcov"></div>
        </div>


        <div class="vcbd">
          <div class="vcc"><?= htmlspecialchars($v['cat']??$v['category']??'Vehicle') ?></div>
          <div class="vcn"><?= htmlspecialchars($v['name']??$v['title']??'Vehicle') ?></div>
          <div class="vcsp">
            <?php if(!empty($v['speed']) && $v['speed']!=='N/A'): ?>
            <div class="vcs"><i class="fa-solid fa-gauge-high"></i> <?= $v['speed'] ?></div>
            <?php endif; ?>
            <?php if(!empty($v['seats']) && $v['seats']!=='N/A'): ?>
            <div class="vcs"><i class="fa-solid fa-user-group"></i> <?= $v['seats'] ?> seats</div>
            <?php endif; ?>
            <?php if(!empty($v['fuel']) && $v['fuel']!=='N/A'): ?>
            <div class="vcs"><i class="fa-solid fa-gas-pump"></i> <?= $v['fuel'] ?></div>
            <?php endif; ?>
          </div>
          <div class="vcf">
            <div>
              <div class="vcpl">From</div>
              <div class="vcp"><?= $v['price']??('₹'.number_format($v['final_price']??$v['price_per_hour']??0)) ?><small>/hour</small></div>
              <div class="vclo"><i class="fa-solid fa-location-dot"></i> <?= $v['city']??'India' ?></div>
            </div>
            <div class="vcb2">
              <button class="bsm bdt" onclick='openModal(<?= htmlspecialchars(json_encode(array_merge($v,["img"=>$rawImg])),ENT_QUOTES) ?>)'>Details</button>
              <?php if($isBookedNow): ?>
                <span class="bsm bsm-disabled" title="Unavailable right now">Unavailable</span>
              <?php else: ?>
                <a href="book_vehicle.php?id=<?= $v['id']??1 ?>&t=<?= urlencode($v['name'] ?? $v['title'] ?? 'Vehicle') ?>&type=<?= urlencode($v['type'] ?? '2wheeler') ?><?= !empty($v['imgs'][0]) ? '&img='.rawurlencode((string)$v['imgs'][0]) : '' ?>" class="bsm brt" style="display:inline-flex;align-items:center;">Book</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PAYMENT OPTIONS -->
<section id="pay">
  <div class="payg">
    <div>
      <div class="stag">Safe &amp; Flexible</div>
      <div class="sh">PAYMENT <span class="dim">OPTIONS</span></div>
      <p style="font-size:.86rem;color:var(--tx2);line-height:1.87;margin-top:.9rem;max-width:460px;">All methods are 100% secure. Admin confirms your booking and final price — you pay only after approval. No hidden charges ever.</p>
      <div class="paym rv">
        <?php foreach([
          ['fa-solid fa-money-bill-wave','Cash on Delivery','Pay at pickup. Zero advance.'],
          ['fa-solid fa-mobile-screen',  'UPI / GPay',      'PhonePe, Paytm, BHIM.'],
          ['fa-solid fa-credit-card',    'Cards',           'Visa, MC, RuPay, Amex.'],
          ['fa-solid fa-building-columns','Net Banking',    'All major banks.'],
          ['fa-solid fa-calendar-days',  'EMI Options',     '3–12 months, 0% fee.'],
          ['fa-solid fa-gift',           'Reward Points',   'Earn & redeem credits.'],
        ] as $p): ?>
        <div class="pmc">
          <div class="pmi"><i class="<?= $p[0] ?>"></i></div>
          <div class="pmn"><?= $p[1] ?></div>
          <div class="pmd"><?= $p[2] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="payr rv">
      <div style="font-size:.56rem;font-weight:700;letter-spacing:.3em;text-transform:uppercase;color:var(--bl);margin-bottom:.4rem;">Security Guarantee</div>
      <div style="font-size:1.5rem;font-weight:700;color:var(--wh);line-height:1.12;margin-bottom:.4rem;">Your Money is<br><span style="color:var(--bl)">Always Protected</span></div>
      <p style="font-size:.81rem;color:var(--tx2);line-height:1.75;margin-bottom:1.4rem;">Pay only after admin confirms. Full refund for cancellations within 24hrs.</p>
      <div class="paysec">
        <?php foreach([
          ['fa-solid fa-lock',         '256-bit SSL Encryption',  'Every payment encrypted end-to-end'],
          ['fa-solid fa-circle-check', 'Admin Price Guarantee',   'Pay only the confirmed final amount'],
          ['fa-solid fa-rotate-left',  '24hr Free Cancellation',  'Full refund, no questions asked'],
          ['fa-solid fa-receipt',      'Instant Receipt',         'SMS + email confirmation immediately'],
        ] as $ps): ?>
        <div class="psi">
          <div class="psic"><i class="<?= $ps[0] ?>"></i></div>
          <div><div class="pst"><?= $ps[1] ?></div><div class="psd"><?= $ps[2] ?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:1.5rem;display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
        <span style="font-size:.6rem;color:var(--tx2);font-weight:700;letter-spacing:.1em;text-transform:uppercase;">Accepted:</span>
        <?php foreach(['UPI','VISA','MC','PAYTM','RUPAY','AMEX'] as $pb): ?>
        <span style="padding:.2rem .6rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:var(--tx2);font-size:.6rem;font-weight:700;letter-spacing:.1em;border-radius:2px;"><?= $pb ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="sec sec-alt" id="how">
  <div class="si">
    <div class="st" style="justify-content:center;text-align:center;display:block;margin-bottom:3rem;">
      <div class="stag" style="justify-content:center;">Simple 4-Step Process</div>
      <div class="sh" style="text-align:center;">HOW IT <span class="dim">WORKS</span></div>
    </div>
    <div class="hwg">
      <?php foreach([
        ['01','fa-solid fa-user-shield',    'Register & Verify', 'Create account, verify driving license. Takes under 2 minutes.'],
        ['02','fa-solid fa-magnifying-glass','Browse & Select',   'Real photos, specs, reviews. Filter by type, city, price.'],
        ['03','fa-solid fa-clipboard-check','Admin Confirms',     'Admin reviews, verifies vehicle, sets the fair final price.'],
        ['04','fa-solid fa-credit-card',    'Pay & Ride',         'Pay your way — cash, UPI, card. Vehicle delivered to you!'],
      ] as $s): ?>
      <div class="hws rv">
        <div class="hwn"><?= $s[0] ?></div>
        <div class="hwi"><i class="<?= $s[1] ?>"></i></div>
        <div class="hwt"><?= $s[2] ?></div>
        <div class="hwd"><?= $s[3] ?></div>
        <?php if($s[0]!=='04'): ?><div class="hwl"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- OWNER CTA -->
<section id="octa">
  <div class="ocg">
    <div>
      <div class="stag">For Vehicle Owners</div>
      <div class="sh">EARN FROM YOUR <span class="dim">VEHICLE</span></div>
      <p style="font-size:.86rem;color:var(--tx2);margin-top:.9rem;line-height:1.82;max-width:420px;">Idle bike or car? List on VRide, earn ₹5,000–₹30,000/month. AI pricing, admin management, safe and insured rentals.</p>
      <div class="of">
        <?php foreach([
          ['fa-solid fa-indian-rupee-sign','₹5K–₹30K Monthly Earnings',  'Based on vehicle type & availability hours'],
          ['fa-solid fa-robot',            'AI Admin Price Suggestion',   'Get a fair market rate automatically'],
          ['fa-solid fa-shield-halved',    'Insurance Covered',           'Vehicles insured during every rental'],
          ['fa-solid fa-location-crosshairs','Location-Based Matching',   'Connect with renters near you'],
        ] as $o): ?>
        <div class="ofi">
          <div class="ofic"><i class="<?= $o[0] ?>"></i></div>
          <div><div class="ofit"><?= $o[1] ?></div><div class="ofid"><?= $o[2] ?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:1.8rem;"><a href="list_vehicle.php" class="btn btnp" style="border-radius:30px">List My Vehicle <i class="fa-solid fa-arrow-right"></i></a></div>
    </div>
    <div class="ofc rv">
      <div class="ofct">Quick Interest Form</div>
      <div class="ofcs">Tell us about your vehicle — we'll call you within 2 hours</div>
      <form onsubmit="event.preventDefault();this.innerHTML='<div style=\'color:var(--ok);font-size:.9rem;font-weight:700;text-align:center;padding:2rem;display:flex;align-items:center;justify-content:center;gap:.6rem\'><i class=\'fa-solid fa-circle-check\'></i> Submitted! We\'ll call you shortly.</div>'">
        <div class="fg2"><label class="flb">Your Name</label><input class="fin" type="text" placeholder="Full name" required></div>
        <div class="frw">
          <div class="fg2"><label class="flb">Phone</label><input class="fin" type="tel" placeholder="+91" required></div>
          <div class="fg2"><label class="flb">Vehicle Type</label>
            <select class="fin"><option>2-Wheeler (Bike)</option><option>4-Wheeler (Car/SUV)</option></select>
          </div>
        </div>
        <div class="fg2"><label class="flb">Vehicle Model</label><input class="fin" type="text" placeholder="e.g. Royal Enfield, Innova"></div>
        <div class="fg2"><label class="flb">City</label><input class="fin" type="text" placeholder="Mumbai, Delhi, Bangalore..."></div>
        <button type="submit" class="btn btnp" style="width:100%;justify-content:center;margin-top:.4rem;">Submit Interest <i class="fa-solid fa-arrow-right"></i></button>
      </form>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="sec">
  <div class="si">
    <div class="st"><div><div class="stag">Real Reviews</div><div class="sh">CUSTOMER <span class="dim">STORIES</span></div></div></div>
    <div class="teg">
      <?php foreach([
        ['Booked Royal Enfield for Ladakh trip. Bike in perfect condition, delivered to hotel. Admin confirmed in 30 mins!','Arjun Kapoor','Biker, Delhi'],
        ['Rented Fortuner for family Coorg trip. Real photos, seamless booking, great price. Will use again!','Priya Mehta','Family Traveller, Bangalore'],
        ['Listed my BMW on VRide, earned ₹18,000 first month! AI pricing was spot on. Best owner platform.','Ravi Sharma','Owner, Mumbai'],
        ['UPI payment — so fast! Got OTP instantly. Mahindra Thar was adventure-ready from day one.','Sneha Rao','Adventure, Pune'],
        ['Admin review process set right expectations on price. Transparent, fair, and vehicle was spotless.','Mohammed Faiz','Renter, Hyderabad'],
        ['Listed Honda Activa during college off-days, made ₹4,500 extra. AI suggested perfect price!','Kavya Nair','Part-time Owner, Chennai'],
      ] as $t): ?>
      <div class="tec rv">
        <div class="tes"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p class="tet">"<?= $t[0] ?>"</p>
        <div class="ten"><?= $t[1] ?></div>
        <div class="ter"><?= $t[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="sec sec-alt">
  <div class="si" style="max-width:840px;margin:0 auto;">
    <div class="st" style="margin-bottom:2.5rem;"><div><div class="stag">Common Questions</div><div class="sh">FAQ <span class="dim">&amp; HELP</span></div></div></div>
    <div class="faqw">
      <?php foreach([
        ['How does the admin pricing system work?','Your booking request goes to admin who verifies the vehicle and sets a fair final price. You see the confirmed amount before paying — no hidden charges.'],
        ['What documents are needed to rent?','Valid Driving License + Government ID (Aadhaar/PAN). Upload during booking — verified within minutes.'],
        ['Can I cancel my booking?','Free cancellation up to 24 hours before pickup. After that a small fee may apply per vehicle terms.'],
        ['How do I list my vehicle?','Click "List Your Vehicle", fill details, set your expected price. AI admin instantly scores and suggests a fair market rate.'],
        ['Which payment methods are accepted?','Cash on Delivery, UPI (GPay/PhonePe/Paytm), Credit/Debit Cards, Net Banking, and EMI options.'],
        ['Is my vehicle insured during rental?','Yes. All rentals include basic insurance. Full coverage add-on available at booking.'],
        ['How does location matching work?','Enter your city during registration. VRide matches you with owners/renters in your area for local pickup/delivery.'],
      ] as $fq): ?>
      <div class="faqi">
        <div class="faqq" onclick="faqTg(this)"><?= $fq[0] ?><i class="fa-solid fa-plus faq-icon"></i></div>
        <div class="faqa"><p><?= $fq[1] ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section id="nl">
  <div class="nli">
    <div class="stag" style="justify-content:center;">Stay Updated</div>
    <div class="sh" style="text-align:center;font-size:1.85rem;">GET <span class="dim">NOTIFIED</span></div>
    <p style="font-size:.82rem;color:var(--tx2);line-height:1.8;margin-top:.6rem;">New vehicles in your city, weekend deals &amp; owner earning tips.</p>
    <div class="nlf">
      <input class="nlfin" type="email" placeholder="Your email address..." id="nlEmail">
      <button class="nlfb" id="nlBtn"><i class="fa-solid fa-paper-plane"></i> Subscribe</button>
    </div>
    <p style="font-size:.68rem;color:var(--tx2);margin-top:.6rem;opacity:.55;display:flex;align-items:center;justify-content:center;gap:.4rem;"><i class="fa-solid fa-lock" style="font-size:.6rem;color:var(--bl);"></i> No spam · Unsubscribe anytime · Privacy protected</p>
  </div>
</section>

<!-- FOOTER -->
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
      <p class="fcp">&copy; 2026 VRide Anti. All rights reserved💕</p>
      <div class="f-socials">
        <i class="fa-brands fa-instagram"></i>
        <i class="fa-brands fa-facebook-f"></i>
        <i class="fa-brands fa-x-twitter"></i>
      </div>
    </div>
  </div>
</footer>

<!-- MODAL -->
<div class="mov" id="dm">
  <div class="mo">
    <button class="mcl" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    <div class="moim"><img id="mI" src="" alt="" style="display:block;"></div>
    <div class="mob">
      <div style="font-size:.58rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--bl);margin-bottom:.3rem;" id="mCat"></div>
      <div style="font-size:1.7rem;font-weight:700;color:var(--wh);margin-bottom:.5rem;" id="mNm"></div>
      <div class="msg">
        <div class="msp"><div class="mspl">Daily Rate</div><div class="mspv" id="mPr"></div></div>
        <div class="msp"><div class="mspl">Top Speed</div><div class="mspv" id="mSp"></div></div>
        <div class="msp"><div class="mspl">Seats / Fuel</div><div class="mspv" id="mSe"></div></div>
      </div>
      <p id="mDs" style="font-size:.83rem;color:var(--tx2);line-height:1.8;margin-bottom:1.5rem;"></p>
      <div style="display:flex;gap:.7rem;">
        <a id="mBk" href="vehicles.php" class="btn btnp" style="flex:1;justify-content:center;border-radius:30px">Book Now <i class="fa-solid fa-arrow-right"></i></a>
        <button onclick="closeModal()" class="btn btns" style="border-radius:30px">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
// ── VEHICLE CARD CAROUSEL ─────────────────────────────
function goCar(card, idx) {
  const slides = card.querySelectorAll('.vcim-slide');
  const dots   = card.querySelectorAll('.vcar-dot');
  const cur    = card.querySelector('.vcar-cur');
  if(!slides.length) return;
  idx = (idx + slides.length) % slides.length;
  slides.forEach(s => s.classList.remove('on'));
  dots.forEach(d => d.classList.remove('on'));
  slides[idx].classList.add('on');
  dots[idx]?.classList.add('on');
  if(cur) cur.textContent = idx + 1;
  card.dataset.ci = idx;
}
function moveCar(btn, dir, e) {
  e.stopPropagation();
  const card = btn.closest('.vcim');
  const ci = parseInt(card.dataset.ci || 0);
  goCar(card, ci + dir);
}
function jumpCar(dot, idx, e) {
  e.stopPropagation();
  goCar(dot.closest('.vcim'), idx);
}
// Mobile Swipe Support
document.querySelectorAll('.vcim').forEach(c => {
  let x = 0;
  c.addEventListener('touchstart', e => x = e.touches[0].clientX, {passive:true});
  c.addEventListener('touchend', e => {
    let dx = e.changedTouches[0].clientX - x;
    if(Math.abs(dx) > 40) goCar(c, parseInt(c.dataset.ci||0) + (dx > 0 ? -1 : 1));
  }, {passive:true});
});

// NAV scroll
const mn = document.getElementById('mn');
window.addEventListener('scroll', () => mn.classList.toggle('sc', window.scrollY > 55));

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

// ── HERO SLIDESHOW (CSS-driven, synchronized background + right panel) ──
const rightSlides = document.querySelectorAll('.hslide');
const bgSlides = document.querySelectorAll('.hbslide');
const dots   = document.querySelectorAll('.hd');
let ci = 0, autoTimer;
const userName = <?= json_encode($_SESSION['name'] ?? 'Guest') ?>;
function setHeroName(name) {
  const htSpan = document.querySelector('.hero-title span');
  if(!htSpan) return;
  // clear existing content and create per-character spans
  htSpan.innerHTML = '';
  const chars = Array.from(String(name));
  chars.forEach(ch => {
    const s = document.createElement('span'); s.className = 'char'; s.textContent = ch === ' ' ? '\u00A0' : ch; htSpan.appendChild(s);
  });
  // stagger reveal
  htSpan.querySelectorAll('.char').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 45));
  // subtle neon after reveal
  setTimeout(() => htSpan.classList.add('neon-glow'), chars.length * 45 + 120);
  setTimeout(() => htSpan.classList.remove('neon-glow'), chars.length * 45 + 1400);
}

function updateHeroForSlide(index) {
  // Always show logged-in user's name in hero (do not replace with car names)
  const hv = document.getElementById('heroVehicle');
  if(hv) {
    const nameEl = hv.querySelector('.hv-name');
    if(nameEl) { nameEl.textContent = userName; nameEl.classList.remove('pop'); void nameEl.offsetWidth; nameEl.classList.add('pop'); }
  }
  setHeroName(userName);
}

function goSlide(i) {
  const len = rightSlides.length || 1;
  // remove current
  rightSlides[ci]?.classList.remove('active');
  bgSlides[ci]?.classList.remove('active');
  dots[ci]?.classList.remove('on');
  ci = (i + len) % len;
  // set new
  rightSlides[ci]?.classList.add('active');
  bgSlides[ci]?.classList.add('active');
  dots[ci]?.classList.add('on');
  updateHeroForSlide(ci);
}

function startAuto() { autoTimer = setInterval(() => goSlide(ci + 1), 5000); }
function resetAuto()  { clearInterval(autoTimer); startAuto(); }

document.getElementById('nx')?.addEventListener('click', () => { goSlide(ci + 1); resetAuto(); });
document.getElementById('pr')?.addEventListener('click', () => { goSlide(ci - 1); resetAuto(); });
dots.forEach((dot, idx) => {
  dot.addEventListener('click', () => {
    goSlide(idx);
    resetAuto();
  });
});
startAuto();
updateHeroForSlide(ci);

// SEARCH
function goSearch() {
  const type = document.getElementById('sty').value;
  const city = document.getElementById('sct').value;
  let url = 'vehicles.php?';
  if (type) url += 'type=' + type + '&';
  if (city) url += 'city=' + encodeURIComponent(city);
  window.location.href = url;
}
document.getElementById('sdt')?.setAttribute('min', new Date().toISOString().split('T')[0]);
document.getElementById('sct')?.addEventListener('keydown', e => e.key === 'Enter' && goSearch());

// FLEET FILTER
function fltr(type, btn) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  document.querySelectorAll('.vc').forEach(c => {
    c.style.display = (type === 'all' || c.dataset.type === type) ? '' : 'none';
  });
}

// MODAL
function openModal(v) {
  // Use currently active carousel image if available
  const card = document.querySelector(`.vcim[data-id="${v.id}"]`);
  let activeImg = v.img || v.image;
  if(card) {
    const activeSlide = card.querySelector('.vcim-slide.on img');
    if(activeSlide) activeImg = activeSlide.src;
  }
  
  document.getElementById('mI').src = activeImg || '';
  document.getElementById('mCat').textContent = (v.cat || v.category || '') + (v.type === '2wheeler' ? ' · 2 Wheeler' : ' · 4 Wheeler');
  document.getElementById('mNm').textContent  = v.name || v.title || 'Vehicle';
  document.getElementById('mPr').textContent  = v.price || ('₹' + (v.final_price || v.price_per_day || 'N/A'));
  document.getElementById('mSp').textContent  = v.speed || 'Contact Admin';
  document.getElementById('mSe').textContent  = (v.seats || 'N/A') + ' / ' + (v.fuel || 'N/A');
  document.getElementById('mDs').textContent  = v.description || 'A premium well-maintained ' + (v.cat || v.category || 'vehicle') + ' available for rent.';
  const bookBtn = document.getElementById('mBk');
  if (v.is_booked_now) {
    bookBtn.textContent = 'Unavailable';
    bookBtn.href = 'javascript:void(0)';
    bookBtn.classList.add('btn-disabled');
  } else {
    bookBtn.innerHTML = 'Book Now <i class="fa-solid fa-arrow-right"></i>';
    const t = encodeURIComponent(v.name || v.title || 'Vehicle');
    const ty = encodeURIComponent(v.type || '2wheeler');
    let bookUrl = 'book_vehicle.php?id=' + (v.id || 1) + '&t=' + t + '&type=' + ty;
    const thumb = (Array.isArray(v.imgs) && v.imgs[0]) ? v.imgs[0] : (v.img || '');
    if (thumb) bookUrl += '&img=' + encodeURIComponent(thumb);
    bookBtn.href = bookUrl;
    bookBtn.classList.remove('btn-disabled');
  }
  document.getElementById('dm').classList.add('open');
}
function closeModal() { document.getElementById('dm').classList.remove('open'); }
document.getElementById('dm')?.addEventListener('click', e => { if (e.target.id === 'dm') closeModal(); });


// FAQ
function faqTg(el) {
  const a = el.nextElementSibling, isO = a.classList.contains('op');
  document.querySelectorAll('.faqa').forEach(x => x.classList.remove('op'));
  document.querySelectorAll('.faqq').forEach(x => x.classList.remove('open'));
  if (!isO) { a.classList.add('op'); el.classList.add('open'); }
}

// SCROLL REVEAL
const obs = new IntersectionObserver(e => e.forEach(el => {
  if (el.isIntersecting) { el.target.classList.add('show'); obs.unobserve(el.target); }
}), { threshold: .08 });
document.querySelectorAll('.rv').forEach((el, i) => { el.style.transitionDelay = (i % 5) * .08 + 's'; obs.observe(el); });

// NEWSLETTER
document.getElementById('nlBtn')?.addEventListener('click', function () {
  const inp = document.getElementById('nlEmail');
  if (inp.value && inp.value.includes('@')) {
    inp.parentElement.innerHTML = '<div style="color:var(--ok);font-size:.9rem;font-weight:700;padding:.5rem 0;display:flex;align-items:center;gap:.5rem;"><i class="fa-solid fa-circle-check"></i> Subscribed! Welcome to VRide updates.</div>';
  } else {
    inp.style.borderColor = 'var(--rd)';
    setTimeout(() => inp.style.borderColor = '', 2000);
  }
});

</script>
<?php include __DIR__ . '/firebase_script.php'; ?>
</body>
</html>

