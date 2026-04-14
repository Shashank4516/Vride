<?php
require_once 'db.php';
$pageTitle = 'Book Vehicle — VRide';
if (!isLoggedIn()) { flash('Please login to book.','error'); redirect('login.php'); }

$vid = intval($_GET['id'] ?? 1);
$pdo = getDB();
$vehicle = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT v.*, u.name as owner_name FROM vehicles v LEFT JOIN users u ON v.owner_id=u.id WHERE v.id=? AND v.status='approved'");
    $stmt->execute([$vid]);
    $vehicle = $stmt->fetch();
}
// Demo fallback
if (!$vehicle) {
    $vehicle = ["id"=>$vid,"title"=>"Royal Enfield Classic 350","type"=>"2wheeler","category"=>"Bike","city"=>"Mumbai","final_price"=>350,"price_per_day"=>350,"model"=>"RE Classic","image"=>"https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80","description"=>"Classic cruiser with timeless design.","damage_charge"=>500,"extra_hour_charge"=>50,"terms"=>"Fuel not included. Return clean.","owner_name"=>"VRide Fleet"];
}

$success = false;
$bookingRef = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = max(1, intval($_POST['days'] ?? 1));
    $amount = ($vehicle['final_price'] ?? $vehicle['price_per_day']) * $days;
    $bookingRef = 'VR-' . strtoupper(substr(md5(uniqid()), 0, 8));
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id,vehicle_id,pickup_date,return_date,days,amount,final_amount,addons,payment_method,status) VALUES (?,?,?,?,?,?,?,?,?,'pending')");
        $stmt->execute([$_SESSION['user_id'],$vid,$_POST['pickup_date'],$_POST['return_date'],$days,$amount,$amount,json_encode($_POST['addons']??[]),$_POST['payment']??'cash']);
    }
    $success = true;
}
?>
<?php include 'header.php'; ?>
<style>
.bv-wrap{padding-top:54px;padding-left:48px;min-height:100vh;}
.bv-inner{max-width:1100px;margin:0 auto;padding:3.5rem 2rem 6rem;display:grid;grid-template-columns:1fr 360px;gap:2.5rem;align-items:start;}
.sticky-summary{position:sticky;top:90px;}
.sum-card{background:var(--card);border:1px solid rgba(255,255,255,.07);overflow:hidden;}
.sum-img{height:185px;overflow:hidden;position:relative;}
.sum-img img{width:100%;height:100%;object-fit:cover;}
.sum-img-ov{position:absolute;inset:0;background:linear-gradient(to top,var(--card),transparent 60%);}
.sum-body{padding:1.4rem;}
.sum-vcat{font-family:inherit;font-size:.6rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--blue);margin-bottom:.3rem;}
.sum-vname{font-family:inherit;font-size:1.35rem;font-weight:700;color:var(--white);margin-bottom:1.1rem;}
.sum-rows{display:flex;flex-direction:column;gap:.55rem;margin-bottom:1.2rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,.05);}
.sum-row{display:flex;justify-content:space-between;align-items:center;font-size:.8rem;}
.sum-row-l{color:var(--txt2);}
.sum-row-v{font-weight:600;}
.sum-total{display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;}
.sum-total-l{font-family:inherit;font-size:.65rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--txt2);}
.sum-total-v{font-family:inherit;font-size:1.6rem;font-weight:900;color:var(--blue);}
.trust-list{padding:.9rem 1.2rem;background:var(--bg3);border-top:1px solid rgba(255,255,255,.04);}
.trust-item{display:flex;align-items:center;gap:.6rem;font-size:.75rem;color:var(--txt2);margin-bottom:.5rem;}
.trust-item:last-child{margin-bottom:0;}
/* Success */
.success-box{max-width:680px;margin:0 auto;padding:3.5rem 2rem;text-align:center;}
.sb-icon{font-size:4rem;animation:pop .5s ease;}
@keyframes pop{0%{transform:scale(0);}80%{transform:scale(1.1);}100%{transform:scale(1);}}
.sb-h{font-family:inherit;font-size:2.5rem;font-weight:700;text-transform:uppercase;color:var(--white);margin:.8rem 0;}
.sb-h span{color:var(--blue);}
.sb-ref{display:inline-flex;align-items:center;gap:1rem;padding:1rem 2rem;background:var(--card);border:1px solid rgba(26,140,255,.2);margin:1rem 0 2rem;}
.sb-ref-l{font-family:inherit;font-size:.6rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--txt2);}
.sb-ref-v{font-family:inherit;font-size:1.2rem;font-weight:900;color:var(--blue);}
.flow-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;text-align:left;}
.fstep{padding:1.2rem;background:var(--card);border:1px solid rgba(255,255,255,.05);}
.fstep-n{font-family:inherit;font-size:1.6rem;font-weight:900;color:rgba(26,140,255,.18);margin-bottom:.4rem;}
.fstep-t{font-family:inherit;font-size:.7rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--white);margin-bottom:.3rem;}
.fstep-d{font-size:.76rem;color:var(--txt2);line-height:1.5;}
@media(max-width:850px){.bv-inner{grid-template-columns:1fr;}.sticky-summary{position:static;}.flow-steps{grid-template-columns:1fr;}}
</style>
<div class="bv-wrap">
  <?php if ($success): ?>
  <div class="success-box">
    <div class="sb-icon"><i class="fas fa-check" style="font-size:3rem;color:var(--success);"></i></div>
    <h1 class="sb-h">BOOKING <span>SUBMITTED!</span></h1>
    <p style="color:var(--txt2);font-size:.9rem;margin-bottom:1rem;">Your request has been sent to admin for approval. You'll be notified once confirmed.</p>
    <div class="sb-ref"><div><div class="sb-ref-l">Booking Ref</div><div class="sb-ref-v"><?= $bookingRef ?></div></div></div>
    <div class="flow-steps">
      <div class="fstep"><div class="fstep-n">01</div><div class="fstep-t">Admin Review</div><div class="fstep-d">Admin reviews your request and verifies details within 30 minutes.</div></div>
      <div class="fstep"><div class="fstep-n">02</div><div class="fstep-t">Price Confirmed</div><div class="fstep-d">Final price is confirmed and you'll receive a notification to pay.</div></div>
      <div class="fstep"><div class="fstep-n">03</div><div class="fstep-t">Vehicle Delivery</div><div class="fstep-d">Vehicle is delivered to your location on the pickup date.</div></div>
    </div>
    <a href="dashboard.php" class="btn btn-primary">View My Bookings →</a>
  </div>
  <?php else: ?>
  <div class="bv-inner" style="padding-top:3.5rem;padding-bottom:6rem;">
    <!-- FORM -->
    <div>
      <div class="sec-label">Reserve Your Ride</div>
      <div class="sec-h" style="margin-bottom:2rem;">BOOKING <span class="dim">FORM</span></div>
      <div class="form-card">
        <form method="POST" action="book_vehicle.php?id=<?= $vid ?>">
          <div class="form-section-title"><i class="fas fa-user"></i> Your Details</div>
          <div class="form-row">
            <div class="form-group"><label>Full Name *</label><input type="text" name="name" value="<?= htmlspecialchars($_SESSION['name']??'') ?>" required></div>
            <div class="form-group"><label>Phone *</label><input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" required></div>
          </div>
          <div class="form-group"><label>Email Address</label><input type="email" name="email" value="<?= htmlspecialchars($_SESSION['email']??'') ?>"></div>
          <div class="form-group"><label>Driving License Number *</label><input type="text" name="license" placeholder="DL-1234567890" required></div>

          <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-calendar"></i> Rental Schedule</div>
          <div class="form-row">
            <div class="form-group"><label>Pick-up Date *</label><input type="date" name="pickup_date" id="pdate" required></div>
            <div class="form-group"><label>Return Date *</label><input type="date" name="return_date" id="rdate" required></div>
          </div>
          <div class="form-group"><label>Pick-up Location *</label>
            <select name="pickup_location"><option>Same as vehicle city (<?= htmlspecialchars($vehicle['city']??'') ?>)</option><option>Home / Hotel Delivery</option><option>Airport</option><option>Custom Location</option></select>
          </div>
          <div class="form-group"><label>Delivery Address (if applicable)</label><input type="text" name="delivery_address" placeholder="Hotel name or full address..."></div>
          <input type="hidden" name="days" id="daysInput" value="1">

          <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-sparkles"></i> Optional Add-ons</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
            <?php foreach(["GPS Navigation (+₹100/day)","Helmet (+₹50/day)","Roadside Assistance (+₹150/day)","Extra Driver (+₹200/day)","Child Seat (+₹80/day)","Fuel Package (+₹250/day)"] as $a): ?>
            <label style="display:flex;align-items:center;gap:.6rem;padding:.75rem;background:var(--bg3);border:1px solid rgba(255,255,255,.06);cursor:pointer;font-size:.8rem;transition:border-color .3s;" onmouseover="this.style.borderColor='rgba(26,140,255,.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,.06)'">
              <input type="checkbox" name="addons[]" value="<?= $a ?>" style="width:15px;height:15px;accent-color:var(--blue);flex-shrink:0;">
              <?= $a ?>
            </label>
            <?php endforeach; ?>
          </div>

          <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-credit-card"></i> Payment Method</div>
          <div style="display:flex;gap:.8rem;flex-wrap:wrap;">
            <?php foreach(["cash"=>"<i class=\"fas fa-money-bill\"></i> Cash on Delivery","upi"=>"<i class=\"fas fa-smartphone\"></i> UPI / QR","card"=>"<i class=\"fas fa-credit-card\"></i> Card / Netbanking"] as $val=>$label): ?>
            <label style="flex:1;min-width:130px;display:flex;align-items:center;gap:.6rem;padding:.85rem 1rem;background:var(--bg3);border:1px solid rgba(255,255,255,.06);cursor:pointer;font-size:.8rem;transition:all .3s;border-radius:2px;">
              <input type="radio" name="payment" value="<?= $val ?>" <?= $val==='cash'?'checked':'' ?> style="accent-color:var(--blue);">
              <?= $label ?>
            </label>
            <?php endforeach; ?>
          </div>

          <div class="form-group" style="margin-top:1.5rem;"><label>Special Requests</label><textarea name="notes" placeholder="Any special requirements for our team..."></textarea></div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
            Submit Booking Request
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>
      </div>
    </div>

    <!-- SUMMARY -->
    <div class="sticky-summary">
      <div class="sum-card">
        <div class="sum-img">
          <img src="<?= htmlspecialchars($vehicle['image']??'') ?>" alt="Vehicle">
          <div class="sum-img-ov"></div>
        </div>
        <div class="sum-body">
          <div class="sum-vcat"><?= htmlspecialchars($vehicle['category']??'') ?> · <?= $vehicle['type']==='2wheeler'?'2 Wheeler':'4 Wheeler' ?></div>
          <div class="sum-vname"><?= htmlspecialchars($vehicle['title']) ?></div>
          <div class="sum-rows">
            <div class="sum-row"><span class="sum-row-l">Daily Rate</span><span class="sum-row-v">₹<?= number_format($vehicle['final_price']??$vehicle['price_per_day']) ?></span></div>
            <div class="sum-row"><span class="sum-row-l">Duration</span><span class="sum-row-v" id="durLabel">—</span></div>
            <div class="sum-row"><span class="sum-row-l">Owner</span><span class="sum-row-v"><?= htmlspecialchars($vehicle['owner_name']??'VRide') ?></span></div>
            <div class="sum-row"><span class="sum-row-l">Location</span><span class="sum-row-v"><i class="fas fa-map-pin"></i> <?= htmlspecialchars($vehicle['city']??'') ?></span></div>
            <div class="sum-row"><span class="sum-row-l">Damage Deposit</span><span class="sum-row-v">₹<?= number_format($vehicle['damage_charge']??0) ?></span></div>
          </div>
          <div class="sum-total">
            <div class="sum-total-l">Estimated Total</div>
            <div class="sum-total-v" id="totalAmt">₹<?= number_format($vehicle['final_price']??$vehicle['price_per_day']) ?></div>
          </div>
        </div>
        <div class="trust-list">
          <div class="trust-item"><i class="fas fa-shield"></i> Admin-verified pricing</div>
          <div class="trust-item"><i class="fas fa-check"></i> Instant booking confirmation</div>
          <div class="trust-item"><i class="fas fa-lock"></i> Secure payment</div>
          <div class="trust-item"><i class="fas fa-undo"></i> Free cancellation (24h before)</div>
          <div class="trust-item"><i class="fas fa-phone"></i> 24/7 support</div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
const today=new Date().toISOString().split('T')[0];
const pd=document.getElementById('pdate');
const rd=document.getElementById('rdate');
if(pd){pd.min=today;pd.value=today;}
if(rd){const t=new Date();t.setDate(t.getDate()+1);rd.min=t.toISOString().split('T')[0];rd.value=t.toISOString().split('T')[0];}
const dailyRate=<?= $vehicle['final_price']??$vehicle['price_per_day']??0 ?>;
function updateTotal(){
  if(!pd||!rd||!pd.value||!rd.value)return;
  const days=Math.max(1,Math.round((new Date(rd.value)-new Date(pd.value))/86400000));
  document.getElementById('daysInput').value=days;
  document.getElementById('durLabel').textContent=days+' day'+(days>1?'s':'');
  document.getElementById('totalAmt').textContent='₹'+new Intl.NumberFormat('en-IN').format(days*dailyRate);
}
pd?.addEventListener('change',updateTotal);
rd?.addEventListener('change',updateTotal);
updateTotal();
</script>
</body>
</html>
