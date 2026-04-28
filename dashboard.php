<?php
// dashboard.php
require_once 'db.php';
$pageTitle = 'My Dashboard — VRide';
if (!isLoggedIn()) { flash('Please login first.','error'); redirect('login.php'); }

$pdo = getDB();
$myBookings = $myListings = [];
if ($pdo && $_SESSION['user_id'] != 99) {
    $uid = $_SESSION['user_id'];
    $myBookings = $pdo->prepare("SELECT b.*,v.title as v_title,v.type as v_type FROM bookings b LEFT JOIN vehicles v ON b.vehicle_id=v.id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 10");
    $myBookings->execute([$uid]); $myBookings=$myBookings->fetchAll();
    $myListings = $pdo->prepare("SELECT * FROM vehicles WHERE owner_id=? ORDER BY created_at DESC LIMIT 10");
    $myListings->execute([$uid]); $myListings=$myListings->fetchAll();
}
?>
<?php include 'header.php'; ?>
<style>
.dash-wrap{padding-top:var(--nav-h);padding-left:var(--sidebar-w);}
.dash-inner{padding:3rem 2.5rem 6rem;max-width:1100px;margin:0 auto;}
.dash-hello{font-family:inherit;font-size:1.8rem;font-weight:700;color:var(--white);margin-bottom:2rem;}
.dash-hello span{color:var(--blue);}
.dash-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:3rem;}
.dash-card{
  background: #0A0D17;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.dash-card:hover { transform: translateY(-4px); border-color: rgba(59,130,246,0.25); }
.dash-card-n{ font-size: 2rem; font-weight: 800; color: #fff; line-height: 1; }
.dash-card-l{ font-size: .65rem; letter-spacing: .2em; text-transform: uppercase; color: rgba(226,232,240,0.4); margin-top: .6rem; font-weight: 600; }
.quick-actions{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2.5rem;}
.sec-sep{font-family:inherit;font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:var(--blue);display:flex;align-items:center;gap:.6rem;margin-bottom:1.2rem;}
.sec-sep::before{content:'';width:16px;height:1.5px;background:var(--blue);}
</style>
<div class="dash-wrap">
  <div class="dash-inner">
    <div class="dash-hello">Welcome back, <span><?= htmlspecialchars($_SESSION['name']??'User') ?></span>!</div>
    <div class="dash-grid">
      <div class="dash-card"><div class="dash-card-n"><?= count($myBookings) ?></div><div class="dash-card-l">My Bookings</div></div>
      <div class="dash-card"><div class="dash-card-n"><?= count($myListings) ?></div><div class="dash-card-l">My Listings</div></div>
      <div class="dash-card"><div class="dash-card-n"><?= count(array_filter($myBookings,fn($b)=>$b['status']==='approved')) ?></div><div class="dash-card-l">Approved</div></div>
    </div>
    <div class="quick-actions">
      <a href="vehicles.php" class="btn btn-primary"><i class="fas fa-car"></i> Browse & Book Vehicle</a>
      <a href="list_vehicle.php" class="btn btn-secondary"><i class="fas fa-key"></i> List My Vehicle</a>
    </div>
    <div class="sec-sep">My Bookings</div>
    <?php if (empty($myBookings)): ?>
    <div style="background:var(--card);border:1px solid rgba(255,255,255,.05);padding:2rem;text-align:center;color:var(--txt2);margin-bottom:2rem;">
      No bookings yet. <a href="vehicles.php" style="color:var(--blue);">Browse vehicles →</a>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;margin-bottom:2rem;"><table class="tbl">
      <thead><tr><th>Vehicle</th><th>Dates</th><th>Days</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($myBookings as $b): ?>
      <tr>
        <td><?= htmlspecialchars($b['v_title']??'Vehicle') ?> <i class="fas <?= ($b['v_type']??'')==='2wheeler'?'fa-motorcycle':'fa-car' ?>"></i></td>
        <td style="font-size:.76rem;"><?= $b['pickup_date']??'' ?> → <?= $b['return_date']??'' ?></td>
        <td><?= $b['days']??1 ?></td>
        <td>₹<?= number_format($b['final_amount']??$b['amount']??0) ?></td>
        <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
    <div class="sec-sep">My Vehicle Listings</div>
    <?php if (empty($myListings)): ?>
    <div style="background:var(--card);border:1px solid rgba(255,255,255,.05);padding:2rem;text-align:center;color:var(--txt2);">
      No listings yet. <a href="list_vehicle.php" style="color:var(--blue);">List your vehicle →</a>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;"><table class="tbl">
      <thead><tr><th>Vehicle</th><th>Type</th><th>City</th><th>Your Price</th><th>Final Price</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($myListings as $v): ?>
      <tr>
        <td><?= htmlspecialchars($v['title']) ?></td>
        <td><i class="fas <?= $v['type']==='2wheeler'?'fa-motorcycle':'fa-car' ?>"></i> <?= $v['type']==='2wheeler'?'2W':'4W' ?></td>
        <td><i class="fas fa-map-pin"></i> <?= htmlspecialchars($v['city']??'') ?></td>
        <td>₹<?= number_format($v['price_per_day']) ?></td>
        <td>₹<?= number_format($v['final_price']??$v['price_per_day']) ?></td>
        <td><span class="badge badge-<?= $v['status'] ?>"><?= strtoupper($v['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
