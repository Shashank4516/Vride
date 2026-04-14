<?php
require_once 'db.php';
$pageTitle = 'Browse Fleet — VRide';

$pdo = getDB();
$vehicles = [];
$typeFilter = $_GET['type'] ?? 'all';
$cityFilter = $_GET['city'] ?? '';

if ($pdo) {
    $where = "WHERE status='approved'";
    $params = [];
    if ($typeFilter !== 'all') { $where .= " AND type=?"; $params[] = $typeFilter; }
    if ($cityFilter) { $where .= " AND city LIKE ?"; $params[] = "%$cityFilter%"; }
    $stmt = $pdo->prepare("SELECT v.*, u.name as owner_name, u.city as owner_city FROM vehicles v LEFT JOIN users u ON v.owner_id=u.id $where ORDER BY v.created_at DESC");
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
}

/* Reliable online fallback images */
$fallback2w = "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=700&q=80";
$fallback4w = "https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=700&q=80";

$demos = [
  ["id"=>1,"title"=>"Royal Enfield Classic 350","type"=>"2wheeler","category"=>"Cruiser","city"=>"Mumbai","final_price"=>350,"price_per_day"=>350,"model"=>"Classic 350","image"=>$fallback2w,"description"=>"Iconic cruiser, perfect for long highway rides. Smooth engine, comfortable seat.","damage_charge"=>500,"extra_hour_charge"=>50,"terms"=>"Fuel not included. Return clean.","owner_name"=>"Ravi Kumar","badge"=>"Popular"],
  ["id"=>2,"title"=>"Yamaha MT-15","type"=>"2wheeler","category"=>"Sport","city"=>"Bangalore","final_price"=>450,"price_per_day"=>450,"model"=>"MT-15","image"=>"https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=700&q=80","description"=>"Aggressive naked sport. Best for city thrill riders who want agility.","damage_charge"=>800,"extra_hour_charge"=>80,"terms"=>"Full gear required. No highway night riding.","owner_name"=>"Kiran R","badge"=>"New"],
  ["id"=>3,"title"=>"Honda Activa 6G","type"=>"2wheeler","category"=>"Scooter","city"=>"Pune","final_price"=>200,"price_per_day"=>200,"model"=>"Activa","image"=>"https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=700&q=80","description"=>"Reliable everyday scooter, easy to ride and very fuel efficient.","damage_charge"=>300,"extra_hour_charge"=>30,"terms"=>"Helmet provided. Return with same fuel level.","owner_name"=>"Ankit Patel","badge"=>"Budget"],
  ["id"=>4,"title"=>"KTM Duke 390","type"=>"2wheeler","category"=>"Sport","city"=>"Delhi","final_price"=>600,"price_per_day"=>600,"model"=>"Duke 390","image"=>"https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=700&q=80","description"=>"High-performance naked bike. Aggressive handling and strong brakes.","damage_charge"=>1200,"extra_hour_charge"=>100,"terms"=>"Valid license required. No pillion on highways.","owner_name"=>"Arjun Singh","badge"=>"Premium"],
  ["id"=>5,"title"=>"Toyota Innova Crysta","type"=>"4wheeler","category"=>"SUV","city"=>"Delhi","final_price"=>2500,"price_per_day"=>2500,"model"=>"Innova","image"=>$fallback4w,"description"=>"Spacious 7-seater, ideal for family trips and corporate travel.","damage_charge"=>2000,"extra_hour_charge"=>200,"terms"=>"Driver not included. AC works fine.","owner_name"=>"Priya Sharma","badge"=>"Popular"],
  ["id"=>6,"title"=>"Mahindra Thar","type"=>"4wheeler","category"=>"Off-Road","city"=>"Goa","final_price"=>3000,"price_per_day"=>3000,"model"=>"Thar 4x4","image"=>"https://images.unsplash.com/photo-1723306975792-f5a053a59dd3?q=80&w=869&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D","description"=>"Open-top 4x4 built for adventure. Beaches, trails, hills — it handles all.","damage_charge"=>3000,"extra_hour_charge"=>250,"terms"=>"4WD lock for off-road only. Return mud-free.","owner_name"=>"Deepak Goa","badge"=>"Adventure"],
  ["id"=>7,"title"=>"Mercedes E-Class","type"=>"4wheeler","category"=>"Luxury","city"=>"Mumbai","final_price"=>4500,"price_per_day"=>4500,"model"=>"E-Class 2023","image"=>"https://images.unsplash.com/photo-1563720223185-11003d516935?w=700&q=80","description"=>"Executive luxury sedan. Perfect for events, weddings, and business travel.","damage_charge"=>5000,"extra_hour_charge"=>400,"terms"=>"No smoking. Must return spotless.","owner_name"=>"Sanjay Mehta","badge"=>"Luxury"],
  ["id"=>8,"title"=>"Swift Dzire","type"=>"4wheeler","category"=>"Sedan","city"=>"Chennai","final_price"=>1200,"price_per_day"=>1200,"model"=>"Dzire 2022","image"=>"https://images.unsplash.com/photo-1541443131876-44b03de101c3?w=700&q=80","description"=>"Comfortable compact sedan. Great mileage, smooth drive for city and highway.","damage_charge"=>1000,"extra_hour_charge"=>100,"terms"=>"Fuel not included. Return with full tank.","owner_name"=>"Meena R","badge"=>"Value"],
];


if (empty($vehicles)) {
    $vehicles = $demos;
    if ($typeFilter !== 'all') $vehicles = array_values(array_filter($demos, fn($v) => $v['type'] === $typeFilter));
    if ($cityFilter) $vehicles = array_values(array_filter($vehicles, fn($v) => stripos($v['city'], $cityFilter) !== false));
}

/* Sanitize image URLs — replace local paths with online fallbacks */
foreach ($vehicles as &$v) {
    $img = $v['image'] ?? '';
    if (empty($img) || str_starts_with($img, '/img/') || str_starts_with($img, './')) {
        $v['image'] = ($v['type'] === '2wheeler') ? $fallback2w : $fallback4w;
    }
}
unset($v);
?>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
<style>
/* ── Layout ── */
.fleet-wrap { padding-top:54px; padding-left:48px; }
.fleet-inner { padding:2.5rem 2.5rem 5rem; max-width:1280px; margin:0 auto; }

.fleet-header { margin-bottom:2rem; }
.fleet-header h1 { font-size:1.6rem; font-weight:800; color:var(--white); margin-top:0.3rem; }

/* ── Filter bar ── */
.filter-bar {
  display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
  margin-bottom:2rem; padding-bottom:1.5rem;
  border-bottom:1px solid var(--border);
}
.filter-tabs { display:flex; gap:6px; }
.ftab {
  display:inline-flex; align-items:center; gap:5px;
  padding:0.42rem 1rem; border-radius:20px; font-size:0.78rem; font-weight:600;
  background:transparent; border:1px solid var(--border); color:var(--txt2);
  cursor:pointer; transition:all 0.2s; font-family:inherit;
}
.ftab i { font-size:.75rem; }
.ftab:hover { color:var(--white); border-color:rgba(255,255,255,0.2); }
.ftab.on { background:var(--blue); color:#fff; border-color:var(--blue); }

.search-wrap { display:flex; gap:8px; align-items:center; margin-left:auto; }
.search-input {
  background:var(--bg3); border:1px solid var(--border); color:var(--txt);
  font-family:inherit; font-size:0.82rem; padding:0.42rem 0.9rem;
  outline:none; width:170px; border-radius:20px; transition:border-color 0.2s;
}
.search-input:focus { border-color:rgba(26,140,255,0.4); }
.count-txt { font-size:0.76rem; color:var(--txt2); white-space:nowrap; }

/* ── Grid ── */
.v-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1.2rem; }

/* ── Card ── */
.vehicle-card {
  background:var(--card); border:1px solid var(--border); border-radius:10px;
  overflow:hidden; transition:transform 0.2s,border-color 0.2s,box-shadow 0.2s;
  position:relative;
}
.vehicle-card:hover {
  transform:translateY(-4px); border-color:rgba(26,140,255,0.3);
  box-shadow:0 12px 40px rgba(0,0,0,0.35);
}

/* ── Badge ── */
.card-badge {
  position:absolute; top:10px; left:10px; z-index:3;
  padding:3px 9px; border-radius:4px; font-size:10px; font-weight:700;
  text-transform:uppercase; letter-spacing:0.05em; backdrop-filter:blur(6px);
}
.badge-popular   { background:rgba(26,140,255,0.85);  color:#fff; }
.badge-new       { background:rgba(0,199,122,0.85);   color:#fff; }
.badge-budget    { background:rgba(245,166,35,0.85);  color:#000; }
.badge-premium   { background:rgba(180,100,255,0.85); color:#fff; }
.badge-luxury    { background:rgba(255,215,0,0.85);   color:#000; }
.badge-adventure { background:rgba(232,54,93,0.85);   color:#fff; }
.badge-value     { background:rgba(100,180,100,0.85); color:#fff; }

/* ── Type icon (top-right) ── */
.type-icon {
  position:absolute; top:10px; right:10px; z-index:3;
  width:28px; height:28px; border-radius:50%;
  background:rgba(0,0,0,0.55); backdrop-filter:blur(6px);
  border:1px solid rgba(255,255,255,.1);
  display:flex; align-items:center; justify-content:center;
  color:var(--blue); font-size:13px;
}

/* ── Card image — FIXED: uniform height, always fills, never blank ── */
.card-img {
  position:relative;
  height:200px;          /* fixed, equal on every card */
  overflow:hidden;
  background:var(--bg3);
  display:flex; align-items:center; justify-content:center;
}
.card-img img {
  position:absolute; inset:0;
  width:100%; height:100%;
  object-fit:cover;      /* crops to fill — no black bars */
  object-position:center;
  display:block;
  transition:transform 0.4s ease;
}
.vehicle-card:hover .card-img img { transform:scale(1.05); }

/* Placeholder icon — shown while img loads, hidden after */
.card-img .img-ph {
  position:relative; z-index:1;
  display:flex; flex-direction:column; align-items:center; gap:6px;
  color:var(--txt2); font-size:2.2rem; opacity:.45;
}
.card-img .img-ph span { font-size:.65rem; text-transform:uppercase; letter-spacing:.1em; }
/* hide placeholder once real image is loaded */
.card-img img.loaded ~ .img-ph { display:none; }

.card-img-overlay {
  position:absolute; inset:0; z-index:2;
  background:linear-gradient(to top,rgba(13,16,32,0.65) 0%,transparent 55%);
  pointer-events:none;
}

/* ── Card body ── */
.card-body { padding:1rem 1.1rem; }
.card-meta {
  display:flex; align-items:center; gap:4px;
  font-size:0.68rem; font-weight:600; color:var(--blue);
  text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;
}
.card-meta i { font-size:.65rem; }
.card-title { font-size:0.96rem; font-weight:700; color:var(--white); line-height:1.25; margin-bottom:0.35rem; }
.card-detail { font-size:0.75rem; color:var(--txt2); }

.card-footer {
  display:flex; align-items:center; justify-content:space-between;
  padding:0.8rem 1.1rem; border-top:1px solid var(--border);
}
.card-price-label { font-size:0.6rem; color:var(--txt2); text-transform:uppercase; letter-spacing:0.04em; }
.card-price { font-size:1.15rem; font-weight:700; color:var(--white); line-height:1.1; }
.card-price span { font-size:0.68rem; color:var(--txt2); font-weight:400; }
.card-actions { display:flex; gap:6px; }

/* ── Empty state ── */
.empty-state { text-align:center; padding:4rem 2rem; color:var(--txt2); }
.empty-state i { font-size:2.5rem; margin-bottom:1rem; opacity:0.4; display:block; }
.empty-state p { font-size:0.9rem; }

/* ── Modal ── */
.modal-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,0.8);
  z-index:1000; display:none; align-items:center; justify-content:center;
  padding:1.5rem; backdrop-filter:blur(4px);
}
.modal-overlay.open { display:flex; }
.modal {
  background:var(--card); border:1px solid var(--border); border-radius:12px;
  max-width:640px; width:100%; max-height:90vh; overflow-y:auto; position:relative;
}
.modal-close {
  position:absolute; top:0.8rem; right:0.8rem; width:30px; height:30px;
  border-radius:50%; background:rgba(255,255,255,0.07); border:none;
  color:var(--txt2); font-size:0.9rem; cursor:pointer;
  display:flex; align-items:center; justify-content:center; z-index:2; transition:background 0.2s;
}
.modal-close:hover { background:var(--danger); color:#fff; }

/* Modal image — fixed height, always fills */
.modal-img {
  height:230px; overflow:hidden;
  border-radius:12px 12px 0 0;
  position:relative; background:var(--bg3);
}
.modal-img img {
  width:100%; height:100%;
  object-fit:cover; object-position:center;
  display:block;
}
.modal-img-overlay {
  position:absolute; inset:0;
  background:linear-gradient(to top,rgba(13,16,32,0.65),transparent 55%);
}

.modal-body { padding:1.5rem; }
.modal-cat { font-size:0.68rem; font-weight:600; color:var(--blue); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem; }
.modal-title { font-size:1.4rem; font-weight:800; color:var(--white); margin-bottom:1rem; }
.modal-specs { display:grid; grid-template-columns:repeat(3,1fr); gap:0.7rem; margin-bottom:1.2rem; }
.mspec { padding:0.8rem; background:var(--bg3); border:1px solid var(--border); border-radius:6px; }
.mspec-l { font-size:0.62rem; color:var(--txt2); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem; font-weight:600; }
.mspec-v { font-size:1rem; color:var(--white); font-weight:700; }
.modal-desc { font-size:0.85rem; line-height:1.75; color:var(--txt2); margin-bottom:1.2rem; }
.terms-box { background:var(--bg3); border:1px solid var(--border); border-radius:6px; padding:0.9rem 1rem; margin-bottom:1.2rem; }
.terms-title { font-size:0.66rem; font-weight:700; color:var(--txt2); text-transform:uppercase; letter-spacing:0.07em; margin-bottom:0.5rem; }

/* ── Fade-in ── */
.fade-in { opacity:0; transform:translateY(12px); transition:opacity 0.35s,transform 0.35s; }
.fade-in.visible { opacity:1; transform:none; }

@media(max-width:900px){ .fleet-inner{padding:2rem 1.5rem 4rem;} .fleet-wrap{padding-left:0;} }
@media(max-width:640px){ .v-grid{grid-template-columns:1fr;} .modal-specs{grid-template-columns:1fr 1fr;} }
</style>

<div class="fleet-wrap">
  <div class="fleet-inner">

    <!-- Header -->
    <div class="fleet-header">
      <div class="sec-label">Available now</div>
      <h1>Browse the fleet</h1>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
      <div class="filter-tabs">
        <button class="ftab <?= $typeFilter==='all'?'on':'' ?>" onclick="filterType('all',this)">
          <i class="fa-solid fa-flag-checkered"></i> All
        </button>
        <button class="ftab <?= $typeFilter==='2wheeler'?'on':'' ?>" onclick="filterType('2wheeler',this)">
          <i class="fa-solid fa-motorcycle"></i> 2-Wheeler
        </button>
        <button class="ftab <?= $typeFilter==='4wheeler'?'on':'' ?>" onclick="filterType('4wheeler',this)">
          <i class="fa-solid fa-car"></i> 4-Wheeler
        </button>
      </div>
      <div class="search-wrap">
        <input type="text" class="search-input" id="citySearch" placeholder="Search city..." value="<?= htmlspecialchars($cityFilter) ?>">
        <button class="btn btn-primary btn-sm" onclick="doSearch()">
          <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
        <span class="count-txt" id="countLabel"><?= count($vehicles) ?> vehicles</span>
      </div>
    </div>

    <!-- Grid -->
    <?php if (empty($vehicles)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-car-side"></i>
      <p>No vehicles found. Try a different filter.</p>
    </div>
    <?php else: ?>
    <div class="v-grid" id="vGrid">
      <?php foreach($vehicles as $i => $v):
        $badge      = $v['badge'] ?? '';
        $badgeClass = 'badge-' . strtolower($badge);
        $is2w       = ($v['type'] === '2wheeler');
        $img        = $v['image'] ?? '';
        if (empty($img) || str_starts_with($img, '/img/')) {
            $img = $is2w ? $fallback2w : $fallback4w;
        }
      ?>
      <div class="vehicle-card fade-in" data-type="<?= $v['type'] ?>" style="transition-delay:<?= ($i%4)*0.06 ?>s">
        <?php if($badge): ?>
        <div class="card-badge <?= $badgeClass ?>"><?= htmlspecialchars($badge) ?></div>
        <?php endif; ?>
        <div class="type-icon">
          <i class="fa-solid <?= $is2w ? 'fa-motorcycle' : 'fa-car' ?>"></i>
        </div>

        <div class="card-img">
          <img
            src="<?= htmlspecialchars($img) ?>"
            alt="<?= htmlspecialchars($v['title']) ?>"
            loading="lazy"
            onload="this.classList.add('loaded')"
            onerror="this.src='<?= $is2w ? $fallback2w : $fallback4w ?>'; this.classList.add('loaded')"
          >
          <div class="img-ph">
            <i class="fa-solid <?= $is2w ? 'fa-motorcycle' : 'fa-car' ?>"></i>
            <span><?= htmlspecialchars($v['category'] ?? $v['type']) ?></span>
          </div>
          <div class="card-img-overlay"></div>
        </div>

        <div class="card-body">
          <div class="card-meta">
            <?= htmlspecialchars($v['category']??'') ?>
            &nbsp;&middot;&nbsp;
            <i class="fa-solid fa-location-dot"></i>
            <?= htmlspecialchars($v['city']??'') ?>
          </div>
          <div class="card-title"><?= htmlspecialchars($v['title']) ?></div>
          <div class="card-detail">
            <i class="fa-solid fa-user" style="font-size:.65rem;color:var(--blue);margin-right:3px;"></i>
            <?= htmlspecialchars($v['owner_name']??'VRide Fleet') ?>
          </div>
        </div>

        <div class="card-footer">
          <div>
            <div class="card-price-label">from</div>
            <div class="card-price">
              ₹<?= number_format($v['final_price']??$v['price_per_day']) ?><span>/day</span>
            </div>
          </div>
          <div class="card-actions">
            <button class="btn btn-secondary btn-sm" onclick='showDetails(<?= htmlspecialchars(json_encode(array_merge($v,["image"=>$img])),ENT_QUOTES) ?>)'>
              Details
            </button>
            <a href="book_vehicle.php?id=<?= $v['id'] ?>" class="btn btn-primary btn-sm">Rent</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal-overlay" id="detailModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    <div class="modal-img">
      <img id="mImg" src="" alt="">
      <div class="modal-img-overlay"></div>
    </div>
    <div class="modal-body">
      <div class="modal-cat" id="mCat"></div>
      <div class="modal-title" id="mTitle"></div>
      <div class="modal-specs">
        <div class="mspec"><div class="mspec-l">Daily rate</div><div class="mspec-v" id="mPrice"></div></div>
        <div class="mspec"><div class="mspec-l">Damage deposit</div><div class="mspec-v" id="mDamage"></div></div>
        <div class="mspec"><div class="mspec-l">Location</div><div class="mspec-v" id="mCity" style="font-size:.88rem"></div></div>
      </div>
      <div class="modal-desc" id="mDesc"></div>
      <div class="terms-box">
        <div class="terms-title"><i class="fa-solid fa-file-contract" style="margin-right:4px;color:var(--blue);font-size:.7rem;"></i> Terms &amp; Conditions</div>
        <div id="mTerms" style="font-size:0.8rem;color:var(--txt2);line-height:1.7;"></div>
      </div>
      <a href="#" id="mBookBtn" class="btn btn-primary" style="width:100%;justify-content:center;">
        Book this vehicle <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </div>
</div>

<script>
function filterType(type, btn) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  let count = 0;
  document.querySelectorAll('.vehicle-card').forEach(c => {
    const show = type === 'all' || c.dataset.type === type;
    c.style.display = show ? '' : 'none';
    if (show) count++;
  });
  document.getElementById('countLabel').textContent = count + ' vehicles';
}

function doSearch() {
  const city = document.getElementById('citySearch').value.toLowerCase();
  let count = 0;
  document.querySelectorAll('.vehicle-card').forEach(c => {
    const show = !city || c.textContent.toLowerCase().includes(city);
    c.style.display = show ? '' : 'none';
    if (show) count++;
  });
  document.getElementById('countLabel').textContent = count + ' vehicles';
}
document.getElementById('citySearch')?.addEventListener('keyup', e => { if (e.key === 'Enter') doSearch(); });

function showDetails(v) {
  document.getElementById('mImg').src      = v.image || '';
  document.getElementById('mCat').textContent   = (v.category || '') + (v.type === '2wheeler' ? ' · 2-Wheeler' : ' · 4-Wheeler');
  document.getElementById('mTitle').textContent  = v.title || '';
  document.getElementById('mPrice').textContent  = '₹' + Number(v.final_price || v.price_per_day || 0).toLocaleString('en-IN');
  document.getElementById('mDamage').textContent = '₹' + Number(v.damage_charge || 0).toLocaleString('en-IN');
  document.getElementById('mCity').textContent   = v.city || 'N/A';
  document.getElementById('mDesc').textContent   = v.description || 'A well-maintained vehicle available for rent.';
  document.getElementById('mTerms').textContent  = v.terms || 'Vehicle must be returned in original condition. Fuel not included. Damage charges apply.';
  document.getElementById('mBookBtn').href = 'book_vehicle.php?id=' + (v.id || 1);
  document.getElementById('detailModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('detailModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('detailModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// Fade-in on scroll
const obs = new IntersectionObserver(entries => entries.forEach(el => {
  if (el.isIntersecting) { el.target.classList.add('visible'); obs.unobserve(el.target); }
}), { threshold: 0.08 });
document.querySelectorAll('.fade-in').forEach(el => obs.observe(el));
</script>
</body>
</html>
