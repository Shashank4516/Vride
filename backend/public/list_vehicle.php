<?php
require_once dirname(__DIR__) . '/lib/db.php';
$pageTitle = 'List Your Vehicle — VRide';
if (!isLoggedIn()) { flash('Please login to list a vehicle.','error'); redirect('login.php'); }

$success = false;
$aiResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'owner_id'         => $_SESSION['user_id'],
        'title'            => trim($_POST['title'] ?? ''),
        'type'             => $_POST['type'] ?? '2wheeler',
        'category'         => trim($_POST['category'] ?? ''),
        'model'            => trim($_POST['model'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'price_per_day'    => floatval($_POST['price_per_day'] ?? 0),
        'damage_charge'    => floatval($_POST['damage_charge'] ?? 0),
        'extra_hour_charge'=> floatval($_POST['extra_hour_charge'] ?? 0),
        'terms'            => trim($_POST['terms'] ?? ''),
        'availability_from'=> $_POST['availability_from'] ?? '',
        'availability_to'  => $_POST['availability_to'] ?? '',
        'city'             => trim($_POST['city'] ?? ''),
        'image'            => trim($_POST['image'] ?? ''),
    ];

    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }
        $fileName = time() . '_' . basename($_FILES['image_file']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
            $data['image'] = $targetPath;
        }
    }

    // Run AI admin decision
    $aiResult = aiAdminDecision($data);
    // Force status to 'pending' for manual approval as requested
    $data['status'] = 'pending';
    $data['final_price'] = $aiResult['suggested_price'];

    $pdo = getDB();
    if ($pdo) {
        $stmt = $pdo->prepare("INSERT INTO vehicles (owner_id,title,type,category,model,description,price_per_day,final_price,damage_charge,extra_hour_charge,terms,availability_from,availability_to,city,image,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute(array_values($data));
    }
    $success = true;
}
?>
<?php include 'header.php'; ?>
<style>
.list-wrap { padding-top:var(--nav-h); padding-left:var(--sidebar-w); min-height:100vh;}
.lv-inner{max-width:900px;margin:0 auto;padding:3.5rem 2rem 6rem;}
.ai-result-box{padding:1.5rem;border:1px solid;margin-top:2rem;}
.ai-approved{background:rgba(0,214,143,.06);border-color:rgba(0,214,143,.25);color:var(--success);}
.ai-pending{background:rgba(255,209,102,.06);border-color:rgba(255,209,102,.25);color:var(--warn);}
.ai-title{font-family:inherit;font-size:.75rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;margin-bottom:.5rem;}
.ai-score-bar{height:6px;background:rgba(255,255,255,.07);border-radius:3px;margin:.5rem 0 .3rem;overflow:hidden;}
.ai-score-fill{height:100%;border-radius:3px;background:var(--blue);transition:width 1s ease;}
</style>
<div class="lv-wrap">
  <div class="lv-inner">
    <div class="sec-label">Earn with VRide</div>
    <div class="sec-h" style="margin-bottom:2.5rem;">LIST YOUR <span class="dim">VEHICLE</span></div>

    <?php if ($success && $aiResult): ?>
    <div class="ai-result-box <?= $aiResult['decision']==='approved'?'ai-approved':'ai-pending' ?>">
      <div class="ai-title"><i class="fas fa-robot"></i> AI Admin Review — <?= strtoupper($aiResult['decision']) ?></div>
      <p style="font-size:.88rem;margin-bottom:.5rem;"><?= htmlspecialchars($aiResult['note']) ?></p>
      <div class="ai-score-bar"><div class="ai-score-fill" style="width:<?= $aiResult['score'] ?>%"></div></div>
      <small style="font-size:.72rem;opacity:.7;">Listing score: <?= $aiResult['score'] ?>/100 &nbsp;|&nbsp; Suggested price: ₹<?= $aiResult['suggested_price'] ?>/day</small>
      <div style="margin-top:1rem;">
        <a href="dashboard.php" class="btn btn-primary btn-sm">View My Listings →</a>
      </div>
    </div>
    <?php else: ?>
    <div class="form-card">
      <div class="form-section-title"><i class="fas fa-car"></i> Basic Information</div>
      <form method="POST" action="list_vehicle.php" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-group">
            <label>Vehicle Title *</label>
            <input type="text" name="title" placeholder="e.g. Royal Enfield Classic 350" required>
          </div>
          <div class="form-group">
            <label>Vehicle Model *</label>
            <input type="text" name="model" placeholder="e.g. Classic 350, Innova Crysta" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Vehicle Type *</label>
            <select name="type" id="typeSelect" onchange="updateCategories()">
              <option value="2wheeler">2-Wheeler</option>
              <option value="4wheeler">4-Wheeler</option>
            </select>
          </div>
          <div class="form-group">
            <label>Category *</label>
            <select name="category" id="catSelect">
              <option>Bike</option><option>Scooter</option><option>Electric Scooter</option><option>Sports Bike</option><option>Cruiser</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" placeholder="Describe your vehicle — condition, features, what makes it special..."></textarea>
        </div>

        <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-indian-rupee-sign"></i> Pricing & Charges</div>
        <div class="form-row">
          <div class="form-group">
            <label>Your Expected Price (₹/day) *</label>
            <input type="number" name="price_per_day" placeholder="e.g. 500" min="50" required>
            <small style="font-size:.72rem;color:var(--txt2);margin-top:.3rem;">Admin will review and finalize the actual price</small>
          </div>
          <div class="form-group">
            <label>Damage Charges (₹) *</label>
            <input type="number" name="damage_charge" placeholder="e.g. 1000" min="0" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Extra Hour Charge (₹/hr)</label>
            <input type="number" name="extra_hour_charge" placeholder="e.g. 100" min="0">
          </div>
          <div class="form-group">
            <label>Your City *</label>
            <input type="text" name="city" placeholder="e.g. Mumbai, Delhi, Pune" required>
          </div>
        </div>

        <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-calendar"></i> Availability</div>
        <div class="form-row">
          <div class="form-group">
            <label>Available From *</label>
            <input type="date" name="availability_from" required>
          </div>
          <div class="form-group">
            <label>Available Until</label>
            <input type="date" name="availability_to">
          </div>
        </div>

        <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-list"></i> Terms & Conditions</div>
        <div class="form-group">
          <label>Terms & Conditions for Renters</label>
          <textarea name="terms" rows="4" placeholder="e.g. Fuel not included. Vehicle must be returned clean. No smoking inside vehicle..."></textarea>
        </div>

        <div class="form-section-title" style="margin-top:1.5rem;"><i class="fas fa-image"></i> Vehicle Image</div>
        <div class="form-group">
          <label>Upload Image from Local PC</label>
          <input type="file" name="image_file" accept="image/*" style="margin-bottom: 0.5rem; color: var(--txt);">
          <div style="font-size: 0.75rem; color: var(--txt2); margin: 0.5rem 0;">— OR —</div>
          <label>Image URL (optional)</label>
          <input type="url" name="image" placeholder="https://... (paste a direct image link)">
          <small style="font-size:.72rem;color:var(--txt2);margin-top:.3rem;">You can use any public image URL from Google or Unsplash</small>
        </div>

        <div style="margin-top:1.5rem;padding:1rem;background:rgba(26,140,255,.06);border:1px solid rgba(26,140,255,.15);">
          <div style="font-family:inherit;font-size:.7rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--blue);margin-bottom:.4rem;"><i class="fas fa-robot"></i> AI Admin Review</div>
          <p style="font-size:.8rem;color:var(--txt2);">After submission, our AI Admin instantly reviews your listing — checks completeness, suggests a fair final price, and either approves or flags it for manual review. You'll see the result immediately.</p>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
          Submit for AI Review
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>
<script>
const cats = {
  '2wheeler': ['Bike','Scooter','Electric Scooter','Sports Bike','Cruiser','Adventure Bike'],
  '4wheeler': ['Hatchback','Sedan','SUV','MUV','Luxury Sedan','Off-Road','Electric Car','Van']
};
function updateCategories(){
  const t=document.getElementById('typeSelect').value;
  const sel=document.getElementById('catSelect');
  sel.innerHTML=cats[t].map(c=>`<option>${c}</option>`).join('');
}
// Set today's date as min
const today=new Date().toISOString().split('T')[0];
document.querySelectorAll('input[type="date"]').forEach(i=>i.min=today);
</script>
</body>
</html>
