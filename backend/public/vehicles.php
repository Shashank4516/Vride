<?php
require_once dirname(__DIR__) . '/lib/db.php';
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

$bookedNowIds = [];
if ($pdo) {
  $bookedNowIds = $pdo->query("SELECT DISTINCT vehicle_id FROM bookings WHERE status='approved' AND CURDATE() BETWEEN pickup_date AND return_date")->fetchAll(PDO::FETCH_COLUMN);
  $bookedNowIds = array_map('intval', $bookedNowIds);
}

/* Reliable online fallback images — Optimized for speed */
$fallback2w = "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=70";
$fallback4w = "https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=400&q=70";

$demos = [
  ["id"=>1,"title"=>"Royal Enfield Classic 350","type"=>"2wheeler","category"=>"Cruiser","city"=>"Mumbai","final_price"=>350,"price_per_day"=>350,"model"=>"Classic 350",
   "imgs"=>["img/re_classic/side.png", "img/re_classic/front.png", "img/re_classic/rear.png"],
   "description"=>"Iconic cruiser, perfect for long highway rides. Smooth engine, comfortable seat.","damage_charge"=>500,"extra_hour_charge"=>50,"terms"=>"Fuel not included. Return clean.","owner_name"=>"Ravi Kumar","badge"=>"Popular"],
  ["id"=>2,"title"=>"Yamaha MT-15","type"=>"2wheeler","category"=>"Sport","city"=>"Bangalore","final_price"=>450,"price_per_day"=>450,"model"=>"MT-15","image"=>"https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=400&q=70","description"=>"Aggressive naked sport. Best for city thrill riders who want agility.","damage_charge"=>800,"extra_hour_charge"=>80,"terms"=>"Full gear required. No highway night riding.","owner_name"=>"Kiran R","badge"=>"New"],
  ["id"=>3,"title"=>"Honda Activa 6G","type"=>"2wheeler","category"=>"Scooter","city"=>"Pune","final_price"=>200,"price_per_day"=>200,"model"=>"Activa","image"=>"https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=400&q=70","description"=>"Reliable everyday scooter, easy to ride and very fuel efficient.","damage_charge"=>300,"extra_hour_charge"=>30,"terms"=>"Helmet provided. Return with same fuel level.","owner_name"=>"Ankit Patel","badge"=>"Budget"],
  ["id"=>4,"title"=>"KTM Duke 390","type"=>"2wheeler","category"=>"Sport","city"=>"Delhi","final_price"=>600,"price_per_day"=>600,"model"=>"Duke 390","image"=>"https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=400&q=70","description"=>"High-performance naked bike. Aggressive handling and strong brakes.","damage_charge"=>1200,"extra_hour_charge"=>100,"terms"=>"Valid license required. No pillion on highways.","owner_name"=>"Arjun Singh","badge"=>"Premium"],
  ["id"=>5,"title"=>"Toyota Innova Crysta","type"=>"4wheeler","category"=>"SUV","city"=>"Delhi","final_price"=>2500,"price_per_day"=>2500,"model"=>"Innova","image"=>$fallback4w,"description"=>"Spacious 7-seater, ideal for family trips and corporate travel.","damage_charge"=>2000,"extra_hour_charge"=>200,"terms"=>"Driver not included. AC works fine.","owner_name"=>"Priya Sharma","badge"=>"Popular"],
  ["id"=>6,"title"=>"Mahindra Thar","type"=>"4wheeler","category"=>"Off-Road","city"=>"Goa","final_price"=>3000,"price_per_day"=>3000,"model"=>"Thar 4x4","image"=>"https://images.unsplash.com/photo-1723306975792-f5a053a59dd3?q=80&w=400&auto=format&fit=crop","description"=>"Open-top 4x4 built for adventure. Beaches, trails, hills — it handles all.","damage_charge"=>3000,"extra_hour_charge"=>250,"terms"=>"4WD lock for off-road only. Return mud-free.","owner_name"=>"Deepak Goa","badge"=>"Adventure"],
  ["id"=>7,"title"=>"Mercedes E-Class","type"=>"4wheeler","category"=>"Luxury","city"=>"Mumbai","final_price"=>4500,"price_per_day"=>4500,"model"=>"E-Class 2023","image"=>"https://images.unsplash.com/photo-1563720223185-11003d516935?w=400&q=70","description"=>"Executive luxury sedan. Perfect for events, weddings, and business travel.","damage_charge"=>5000,"extra_hour_charge"=>400,"terms"=>"No smoking. Must return spotless.","owner_name"=>"Sanjay Mehta","badge"=>"Luxury"],
  ["id"=>8,"title"=>"Swift Dzire","type"=>"4wheeler","category"=>"Sedan","city"=>"Chennai","final_price"=>1200,"price_per_day"=>1200,"model"=>"Dzire 2022","image"=>"https://images.unsplash.com/photo-1541443131876-44b03de101c3?w=400&q=70","description"=>"Comfortable compact sedan. Great mileage, smooth drive for city and highway.","damage_charge"=>1000,"extra_hour_charge"=>100,"terms"=>"Fuel not included. Return with full tank.","owner_name"=>"Meena R","badge"=>"Value"],
];


if (!empty($vehicles)) {
    // Append demos so the page never looks empty
    $vehicles = array_merge($vehicles, $demos);
} else {
    $vehicles = $demos;
}

foreach ($vehicles as &$v) {
  $v['is_booked_now'] = in_array((int)($v['id'] ?? 0), $bookedNowIds, true);
}
unset($v);

if ($typeFilter !== 'all') {
    $vehicles = array_values(array_filter($vehicles, fn($v) => $v['type'] === $typeFilter));
}
if ($cityFilter) {
    $vehicles = array_values(array_filter($vehicles, fn($v) => stripos($v['city'], $cityFilter) !== false));
}

/* Sanitize image URLs — replace local paths with online fallbacks */
foreach ($vehicles as &$v) {
    $img = $v['image'] ?? '';
    // Allow 'uploads/' directory and external URLs
    if (empty($img) || str_starts_with($img, '/img/') || str_starts_with($img, './')) {
        // Double check if it's an uploaded file
        if (!str_starts_with($img, 'uploads/')) {
            $v['image'] = ($v['type'] === '2wheeler') ? $fallback2w : $fallback4w;
        }
    }
}
unset($v);
?>
<?php include 'header.php'; ?>

<style>
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

/* ══ VEHICLE CARDS (From Index) ══ */
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
.vct{position:absolute;top:.8rem;right:.8rem;z-index:3;width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:var(--bl);font-size:.75rem;}
.vcb-booked{top:2.4rem;background:rgba(255,56,96,.12);border:1px solid rgba(255,56,96,.28);color:#FF3860;}

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

.vcar-arr {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.1);
  color: var(--wh);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  opacity: 0;
  transition: all .3s;
  font-size: .7rem;
}
.vcar-arr-l{left:.6rem;}
.vcar-arr-r{right:.6rem;}
.vc:hover .vcar-arr{opacity:1;}
.vcar-arr:hover{background:var(--bl);color:var(--bk);border-color:var(--bl);}

.vcar-dots {
  position: absolute;
  bottom: .8rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: .35rem;
  z-index: 10;
}
.vcar-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: rgba(255,255,255,.3);
  cursor: pointer;
  transition: all .3s;
}
.vcar-dot.on{background:var(--bl);width:14px;border-radius:4px;}
.vcar-count{position:absolute;top:.8rem;right:.6rem;z-index:10;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);padding:.25rem .5rem;border-radius:6px;font-size:.6rem;font-weight:700;color:var(--wh);border:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:.3rem;opacity:0;transition:opacity .3s;}
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

/* Custom buttons: Book and List actions */
.empty-state{text-align:center;padding:4rem 2rem;color:var(--tx2);}
.empty-state i{font-size:2.8rem;margin-bottom:1rem;opacity:0.3;display:block;color:var(--bl);}

/* ══ MODAL ══ */
.mov{position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:2000;display:none;align-items:center;justify-content:center;padding:1.5rem;}
.mov.open{display:flex;}
.mo{background:var(--cd);border:1px solid rgba(255,255,255,.07);max-width:700px;width:100%;max-height:90vh;overflow-y:auto;position:relative;}
.mcl{position:absolute;top:1rem;right:1rem;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.07);border:none;color:var(--tx);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem;z-index:2;transition:all .3s;}
.mcl:hover{background:var(--rd);color:#fff;}
.moim{height:250px;overflow:hidden;background:var(--bg3);}
.moim img{width:100%;height:100%;object-fit:cover;display:block;}
.mob{padding:2rem;}
.msg{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin:1.1rem 0 1.4rem;}
.msp{padding:.9rem;background:var(--bg3);border:1px solid rgba(255,255,255,.04);}
.mspl{font-size:.54rem;letter-spacing:.18em;text-transform:uppercase;color:var(--tx2);font-weight:700;margin-bottom:.22rem;}
.mspv{font-size:.98rem;color:var(--bl);font-weight:700;}
.rv{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease;}
.rv.show{opacity:1;transform:none;}
</style>
<section class="sec sec-alt" id="fleet">
  <div class="si">
    <div class="st">
      <div>
        <div class="stag">Real Vehicles — Verified Owners</div>
        <div class="sh">FEATURED <span class="dim">FLEET</span></div>
      </div>
      <div style="display:flex;gap:.6rem;align-items:center">
        <a href="vehicles.php" class="btn btns" style="border-radius:30px">View All <i class="fa-solid fa-arrow-right"></i></a>
        <a href="list_vehicle.php" class="btn btnp btn-list" style="border-radius:30px">List Vehicle <i class="fa-solid fa-plus"></i></a>
      </div>
    </div>
    <div class="fstrip">
      <button class="ftab <?php echo $typeFilter==='all'?'on':''; ?>" onclick="fltr('all',this)"><i class="fa-solid fa-flag-checkered"></i> All</button>
      <button class="ftab <?php echo $typeFilter==='2wheeler'?'on':''; ?>" onclick="fltr('2wheeler',this)"><i class="fa-solid fa-motorcycle"></i> Bikes &amp; Scooters</button>
      <button class="ftab <?php echo $typeFilter==='4wheeler'?'on':''; ?>" onclick="fltr('4wheeler',this)"><i class="fa-solid fa-car"></i> Cars &amp; SUVs</button>
    </div>

<?php if (empty($vehicles)): ?>
<div class="empty-state">
  <i class="fa-solid fa-car-side"></i>
  <p>No vehicles found matching your criteria. Try adjusting the search.</p>
</div>
<?php else: ?>
<div class="vg" id="vg">
  <?php
  $tagCls=['HOT'=>'bh','POPULAR'=>'bp','ELITE'=>'be','NEW'=>'bn','VIP'=>'bv','BUDGET'=>'bb','ADVENTURE'=>'ba','SPORTY'=>'bs',''=>'bp'];
  
  foreach($vehicles as $i=>$v):
    $tc  = $tagCls[strtoupper($v['badge']??'')] ?? 'bp';
    $is2w = ($v['type']==='2wheeler');
    $rawImgs = [];
    if (!empty($v['imgs']) && is_array($v['imgs'])) {
        $rawImgs = $v['imgs'];
    } else {
        $rawImgs = [$v['image'] ?? ($is2w ? $fallback2w : $fallback4w)];
    }
  ?>
  <div class="vc rv" data-type="<?php echo $v['type']; ?>" style="transition-delay:<?php echo ($i%4)*.08; ?>s">
    <?php if(!empty($v['badge'])): ?>
    <div class="vcb <?php echo $tc; ?>"><?php echo $v['badge']; ?></div>
    <?php endif; ?>
    <?php if(!empty($v['is_booked_now'])): ?>
    <div class="vcb vcb-booked">Booked</div>
    <?php endif; ?>
    <div class="vct"><i class="fa-solid <?php echo $is2w ? 'fa-motorcycle' : 'fa-car'; ?>"></i></div>

    <div class="vcim" data-id="<?php echo $v['id']??0; ?>">
      <div class="vcim-wrap">
        <?php foreach($rawImgs as $jx => $imgUrl): ?>
          <div class="vcim-slide <?php echo $jx===0?'on':''; ?>" data-idx="<?php echo $jx; ?>">
            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($v['title']??''); ?>" loading="lazy" onload="this.classList.add('loaded')">
          </div>
        <?php endforeach; ?>
      </div>
      
      <?php if(count($rawImgs) > 1): ?>
        <div class="vcar-arr vcar-arr-l" onclick="moveCar(this,-1,event)"><i class="fa-solid fa-chevron-left"></i></div>
        <div class="vcar-arr vcar-arr-r" onclick="moveCar(this,1,event)"><i class="fa-solid fa-chevron-right"></i></div>
        <div class="vcar-dots">
          <?php foreach($rawImgs as $jx => $imgUrl): ?>
            <div class="vcar-dot <?php echo $jx===0?'on':''; ?>" onclick="jumpCar(this,<?php echo $jx; ?>,event)"></div>
          <?php endforeach; ?>
        </div>
        <div class="vcar-count"><i class="fa-solid fa-camera"></i> <span class="vcar-cur">1</span>/<?php echo count($rawImgs); ?></div>
      <?php endif; ?>

      <div class="img-placeholder">
        <i class="fa-solid <?php echo $is2w ? 'fa-motorcycle' : 'fa-car'; ?>"></i>
        <span><?php echo htmlspecialchars($v['category']??'Vehicle'); ?></span>
      </div>
      <div class="vcov"></div>
    </div>

    <div class="vcbd">
      <div class="vcc"><?php echo htmlspecialchars($v['category']??'Vehicle'); ?></div>
      <div class="vcn vc-name"><?php echo htmlspecialchars($v['title']??''); ?></div>
      <div class="vcsp">
        <div class="vcs"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($v['owner_name']??'VRide Fleet'); ?></div>
        <?php if(!empty($v['speed']) && $v['speed']!=='N/A'): ?>
        <div class="vcs"><i class="fa-solid fa-gauge-high"></i> <?php echo htmlspecialchars($v['speed']); ?></div>
        <?php endif; ?>
        <?php if(!empty($v['seats']) && $v['seats']!=='N/A'): ?>
        <div class="vcs"><i class="fa-solid fa-user-group"></i> <?php echo htmlspecialchars($v['seats']); ?> seats</div>
        <?php endif; ?>
        <?php if(!empty($v['fuel']) && $v['fuel']!=='N/A'): ?>
        <div class="vcs"><i class="fa-solid fa-gas-pump"></i> <?php echo htmlspecialchars($v['fuel']); ?></div>
        <?php endif; ?>
      </div>
      <div class="vcf">
        <div>
          <div class="vcpl">From</div>
          <div class="vcp">₹<?php echo number_format($v['final_price']??$v['price_per_day']??0); ?> <small>/day</small></div>
          <div class="vclo vc-loc"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($v['city']??'India'); ?></div>
        </div>
        <div class="vcb2">
          <?php
          $dtParams = [
              "name" => $v['title'] ?? '',
              "cat" => $v['category'] ?? '',
              "type" => $v['type'] ?? '',
              "price" => "₹".number_format($v['final_price']??$v['price_per_day']??0),
              "imgs" => $rawImgs,
              "city" => $v['city'] ?? 'India',
              "id" => $v['id'] ?? 0,
              "desc" => $v['description'] ?? '',
              "terms" => $v['terms'] ?? '',
              "damage" => $v['damage_charge'] ?? 0,
              "speed" => $v['speed'] ?? 'N/A',
              "seats" => $v['seats'] ?? 'N/A',
              "fuel" => $v['fuel'] ?? 'N/A',
              "is_booked_now" => !empty($v['is_booked_now'])
          ];
          ?>
            <button class="bsm bdt" onclick='openModal(<?php echo htmlspecialchars(json_encode(array_merge($dtParams,["img" => $rawImgs[0] ?? ""]), JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP), ENT_QUOTES); ?>)'>Details</button>
          <?php if(!empty($v['is_booked_now'])): ?>
            <span class="bsm bsm-disabled" title="Unavailable right now">Unavailable</span>
          <?php else: ?>
            <a href="book_vehicle.php?id=<?php echo $v['id']; ?>&t=<?php echo urlencode($v['name'] ?? $v['title'] ?? 'Vehicle'); ?>&type=<?php echo urlencode($v['type'] ?? '2wheeler'); ?><?php echo !empty($rawImgs[0]) ? '&img='.rawurlencode((string)$rawImgs[0]) : ''; ?>" class="bsm brt" style="display:inline-flex;align-items:center;"><i class="fa-solid fa-calendar-days"></i> Book</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
  </div>
</section>

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
function moveCar(btn, dir, e){
  e.stopPropagation();
  let wrap=btn.closest('.vcim');
  let s=wrap.querySelectorAll('.vcim-slide'), d=wrap.querySelectorAll('.vcar-dot'), c=wrap.querySelector('.vcar-count span');
  let curr=0; s.forEach((x,i)=>{if(x.classList.contains('on'))curr=i;});
  let next=(curr+dir+s.length)%s.length;
  s[curr].classList.remove('on'); d[curr].classList.remove('on');
  s[next].classList.add('on'); d[next].classList.add('on');
  if(c) c.textContent = (next+1)+'/'+s.length;
}
function jumpCar(dot, idx, e){
  e.stopPropagation();
  let wrap=dot.closest('.vcim');
  let s=wrap.querySelectorAll('.vcim-slide'), d=wrap.querySelectorAll('.vcar-dot'), c=wrap.querySelector('.vcar-count span');
  let curr=0; s.forEach((x,i)=>{if(x.classList.contains('on'))curr=i;});
  s[curr].classList.remove('on'); d[curr].classList.remove('on');
  s[idx].classList.add('on'); d[idx].classList.add('on');
  if(c) c.textContent = (idx+1)+'/'+s.length;
}

function fltr(type, btn) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  document.querySelectorAll('.vc').forEach(c => {
    c.style.display = (type === 'all' || c.dataset.type === type) ? '' : 'none';
  });
}

function openModal(v) {
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
  document.getElementById('mDs').textContent  = v.desc || 'A premium well-maintained ' + (v.cat || v.category || 'vehicle') + ' available for rent.';

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

const obs = new IntersectionObserver(e => e.forEach(el => {
  if (el.isIntersecting) { el.target.classList.add('show'); obs.unobserve(el.target); }
}), { threshold: .08 });
document.querySelectorAll('.rv').forEach((el, i) => { el.style.transitionDelay = (i % 5) * .08 + 's'; obs.observe(el); });

document.querySelectorAll('.vcim-slide img').forEach(img => {
  if (img.cxaomplete) img.classList.add('loaded');
});
</script>
</body>
</html>