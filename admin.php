<?php
require_once 'db.php';
$pageTitle = 'Admin Panel — VRide';
if (!isAdmin()) { flash('Admin access required.','error'); redirect('login.php'); }

$pdo = getDB();
$tab = $_GET['tab'] ?? 'dashboard';
//Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'approve_vehicle' && $pdo){
        $id = intval($_POST['id']);
        $price = floatval($_POST['final_price']);   
        $note  = trim($_POST['note'] ?? '');
        $pdo->prepare("UPDATE vehicles SET status='approved', final_price=? WHERE id=?")->execute([$price,$id]);
        flash("Vehicle approved with price ₹$price/day!");
    } elseif ($action === 'reject_vehicle' && $pdo) {
        $id = intval($_POST['id']);
        $pdo->prepare("UPDATE vehicles SET status='rejected' WHERE id=?")->execute([$id]);
        flash("Vehicle rejected.",'error');
    } elseif ($action === 'approve_booking' && $pdo) {
        $id = intval($_POST['id']);
        $final = floatval($_POST['final_amount']);
        $note  = trim($_POST['note'] ?? '');
        $pdo->prepare("UPDATE bookings SET status='approved', final_amount=?, admin_note=? WHERE id=?")->execute([$final,$note,$id]);
        flash("Booking approved! Final amount: ₹$final");
    } elseif ($action === 'reject_booking' && $pdo) {
        $id = intval($_POST['id']);
        $pdo->prepare("UPDATE bookings SET status='rejected' WHERE id=?")->execute([$id]);
        flash("Booking rejected.",'error');
    } elseif ($action === 'complete_booking' && $pdo) {
        $id = intval($_POST['id']);
        $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$id]);
        flash("Booking marked as completed!");
    }
    redirect("admin.php?tab=$tab");
}

// Fetch data
$stats = ['total_vehicles'=>0,'pending_v'=>0,'total_bookings'=>0,'pending_b'=>0,'users'=>0];
$pendingVehicles = $pendingBookings = $allVehicles = $allBookings = [];

if ($pdo) {
    $stats['total_vehicles'] = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    $stats['pending_v']      = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='pending'")->fetchColumn();
    $stats['total_bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $stats['pending_b']      = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
    $stats['users']          = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
    $pendingVehicles = $pdo->query("SELECT v.*,u.name as owner_name,u.phone as owner_phone FROM vehicles v LEFT JOIN users u ON v.owner_id=u.id WHERE v.status='pending' ORDER BY v.created_at DESC")->fetchAll();
    $pendingBookings = $pdo->query("SELECT b.*,u.name as user_name,u.phone as user_phone,v.title as v_title,v.type as v_type,v.final_price FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN vehicles v ON b.vehicle_id=v.id WHERE b.status='pending' ORDER BY b.created_at DESC")->fetchAll();
    $allVehicles     = $pdo->query("SELECT v.*,u.name as owner_name FROM vehicles v LEFT JOIN users u ON v.owner_id=u.id ORDER BY v.created_at DESC LIMIT 30")->fetchAll();
    $allBookings     = $pdo->query("SELECT b.*,u.name as user_name,v.title as v_title FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN vehicles v ON b.vehicle_id=v.id ORDER BY b.created_at DESC LIMIT 30")->fetchAll();
} else {
    // Demo stats
    $stats = ['total_vehicles'=>12,'pending_v'=>3,'total_bookings'=>28,'pending_b'=>5,'users'=>45];
}
?>
<?php include 'header.php'; ?>
<style>
.adm-wrap{padding-top:var(--nav-h);padding-left:var(--sidebar-w);min-height:100vh;display:flex;}
.adm-sidebar{width:220px;background:var(--card);border-right:1px solid var(--border);padding:2rem 0;flex-shrink:0;position:sticky;top:var(--nav-h);height:calc(100vh - var(--nav-h));overflow-y:auto;}
.adm-nav a{display:flex;align-items:center;gap:.8rem;padding:.75rem 1.5rem;color:var(--txt2);font-family:inherit;font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;transition:all .3s;border-right:2px solid transparent;}
.adm-nav a:hover,.adm-nav a.on{background:rgba(26,140,255,.07);color:var(--blue);border-right-color:var(--blue);}
.adm-nav-title{padding:.5rem 1.5rem;font-family:inherit;font-size:.58rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:var(--txt2);opacity:.5;margin-top:.5rem;}
.adm-main{flex:1;padding:2.5rem 2rem;min-width:0;}
.adm-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem;}
.adm-h{font-family:inherit;font-size:1.6rem;font-weight:700;text-transform:uppercase;color:var(--white);letter-spacing:.08em;}

/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:2.5rem;}
.stat-card {
  background: #0A0D17;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-card:hover { transform: translateY(-4px); border-color: rgba(59,130,246,0.25); }
.stat-icon { font-size: 1.6rem; margin-bottom: 0.6rem; color: #3B82F6; }
.stat-n { font-size: 1.8rem; font-weight: 800; color: #fff; line-height: 1; }
.stat-l { font-size: .65rem; letter-spacing: .18em; text-transform: uppercase; color: rgba(226,232,240,0.4); margin-top: .4rem; font-weight: 600; }

/* Pending cards */
.pending-card {
  background: #0A0D17;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.2rem;
}
.pc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;gap:1rem;}
.pc-title{font-family:inherit;font-size:1.1rem;font-weight:700;color:var(--white);}
.pc-sub{font-size:.78rem;color:var(--txt2);margin-top:.2rem;}
.pc-specs{display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.pc-spec{font-size:.78rem;color:var(--txt2);}
.pc-spec span{color:var(--white);font-weight:600;}
.pc-actions{display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;}
.pc-price-input{display:flex;flex-direction:column;gap:.3rem;}
.pc-price-input label{font-size:.58rem;letter-spacing:.18em;text-transform:uppercase;color:var(--txt2);font-family:inherit;font-weight:700;}
.pc-price-input input{background:var(--bg3);border:1px solid rgba(26,140,255,.3);color:var(--txt);padding:.5rem .8rem;font-family:inherit;font-size:.88rem;width:130px;outline:none;border-radius:2px;}

/* AI Badge */
.system-tag {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .2rem .7rem;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.1);
  color: rgba(226,232,240,0.6);
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: .12em;
  text-transform: uppercase;
  border-radius: 4px;
  margin-bottom: .5rem;
}

@media(max-width:900px){.stats-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:600px){.adm-sidebar{display:none;}.adm-wrap{padding-left:0;}.stats-grid{grid-template-columns:1fr 1fr;}}
</style>

<div class="adm-wrap">
  <!-- ADMIN SIDEBAR NAV -->
  <div class="adm-sidebar">
    <div style="padding:0 1.5rem 1.5rem;border-bottom:1px solid var(--border);margin-bottom:1rem;">
      <div style="font-family:inherit;font-size:.6rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--txt2);">Logged in as</div>
      <div style="font-family:inherit;font-size:.95rem;font-weight:700;color:var(--white);margin-top:.2rem;"><?= htmlspecialchars($_SESSION['name']??'Admin') ?></div>
      <div class="badge badge-approved" style="margin-top:.3rem;">ADMIN</div>
    </div>
    <nav class="adm-nav">
      <div class="adm-nav-title">Overview</div>
      <a href="admin.php?tab=dashboard" class="<?= $tab==='dashboard'?'on':'' ?>"><i class="fas fa-chart-line"></i> Dashboard</a>
      <div class="adm-nav-title">Manage</div>
      <a href="admin.php?tab=vehicles" class="<?= $tab==='vehicles'?'on':'' ?>"><i class="fas fa-car"></i> All Vehicles <?php if($stats['pending_v']>0): ?><span style="background:var(--blue);color:#000;padding:.1rem .4rem;border-radius:10px;font-size:.6rem;"><?= $stats['pending_v'] ?></span><?php endif; ?></a>
      <a href="admin.php?tab=bookings" class="<?= $tab==='bookings'?'on':'' ?>"><i class="fas fa-list"></i> All Bookings <?php if($stats['pending_b']>0): ?><span style="background:var(--blue);color:#000;padding:.1rem .4rem;border-radius:10px;font-size:.6rem;"><?= $stats['pending_b'] ?></span><?php endif; ?></a>
      <a href="admin.php?tab=pending_v" class="<?= $tab==='pending_v'?'on':'' ?>"><i class="fas fa-hourglass-end"></i> Pending Vehicles</a>
      <a href="admin.php?tab=pending_b" class="<?= $tab==='pending_b'?'on':'' ?>"><i class="fas fa-hourglass-end"></i> Pending Bookings</a>
      <div class="adm-nav-title">Site</div>
      <a href="index.php">🏠 View Site</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </div>

  <!-- ADMIN MAIN -->
  <div class="adm-main">
    <div class="adm-top">
      <div class="adm-h">
        <?= ['dashboard'=>'<i class="fas fa-chart-line"></i> Dashboard','vehicles'=>'<i class="fas fa-car"></i> All Vehicles','bookings'=>'<i class="fas fa-list"></i> All Bookings','pending_v'=>'<i class="fas fa-hourglass-end"></i> Pending Vehicles','pending_b'=>'<i class="fas fa-hourglass-end"></i> Pending Bookings'][$tab] ?? 'Admin' ?>
      </div>
      <div style="display:flex;gap:.6rem;">
        <a href="index.php" class="btn btn-secondary btn-sm">View Site</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>

    <!-- DASHBOARD -->
    <?php if ($tab === 'dashboard'): ?>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-car"></i></div><div class="stat-n"><?= $stats['total_vehicles'] ?></div><div class="stat-l">Total Vehicles</div></div>
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-hourglass-end"></i></div><div class="stat-n" style="color:var(--warn)"><?= $stats['pending_v'] ?></div><div class="stat-l">Pending Vehicles</div></div>
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-list"></i></div><div class="stat-n"><?= $stats['total_bookings'] ?></div><div class="stat-l">Total Bookings</div></div>
      <div class="stat-card"><div class="stat-icon">🔔</div><div class="stat-n" style="color:var(--warn)"><?= $stats['pending_b'] ?></div><div class="stat-l">Pending Bookings</div></div>
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-n"><?= $stats['users'] ?></div><div class="stat-l">Users</div></div>
    </div>
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
      <div style="font-size:0.8rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#3B82F6; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
        <i class="fas fa-shield-check"></i> System Oversight Dashboard
      </div>
      <div style="font-size:.85rem;line-height:1.8;color:var(--txt2);">
        <strong style="color:var(--white);">How the AI Admin Works:</strong><br>
        When an owner submits a vehicle, the AI scoring engine (score 0–100) immediately reviews: title completeness, model details, price validity, city presence, terms & damage charges.<br>
        <span style="color:var(--success);">Score ≥ 60 → Auto-Approved</span> &nbsp;|&nbsp; <span style="color:var(--warn);">Score &lt; 60 → Flagged for manual review</span><br>
        The AI also suggests a fair market price based on vehicle type. You as admin can override the price anytime from the <strong style="color:var(--blue);">Pending Vehicles</strong> tab.
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
      <div><div style="font-family:inherit;font-size:.75rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--blue);margin-bottom:.8rem;">Quick Actions</div>
        <div style="display:flex;flex-direction:column;gap:.5rem;">
          <a href="admin.php?tab=pending_v" class="btn btn-primary">Review Pending Vehicles</a>
          <a href="admin.php?tab=pending_b" class="btn btn-secondary">Review Pending Bookings</a>
        </div>
      </div>
      <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:1.2rem;">
        <div style="font-family:inherit;font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--txt2);margin-bottom:.5rem;">Admin Test Credentials</div>
        <div style="font-size:.8rem;color:var(--txt2);">Email: admin@vrental.com<br>Password: admin123</div>
      </div>
    </div>

    <!-- PENDING VEHICLES (dashboard) -->
    <?php elseif ($tab === 'pending_v'): ?>
    <?php if (empty($pendingVehicles)): ?>
    <div style="text-align:center;padding:4rem;color:var(--txt2);">
      <div style="font-size:3rem;margin-bottom:1rem;"><i class="fas fa-check" style="color:var(--success);"></i></div>
      <div style="font-family:inherit;font-size:1rem;font-weight:700;text-transform:uppercase;">No pending vehicles — all clear!</div>
      <p style="font-size:.8rem;margin-top:.5rem;">New vehicle listings from owners will appear here for your review.</p>
    </div>
    <?php else: ?>
    <?php foreach($pendingVehicles as $v):
        $ai = aiAdminDecision($v);
    ?>
    <div class="pending-card">
      <div class="system-tag"><i class="fas fa-microchip" style="font-size:0.7rem;"></i> Analysis Score: <?= $ai['score'] ?>/100 — <?= strtoupper($ai['decision']) ?></div>
      <div class="pc-top">
        <div style="display:flex; gap:1.5rem; align-items:flex-start;">
          <?php if(!empty($v['image'])): ?>
            <div style="width:120px; height:80px; flex-shrink:0; border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,0.1);">
              <img src="<?= htmlspecialchars($v['image']) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
          <?php endif; ?>
          <div>
            <div class="pc-title"><?= htmlspecialchars($v['title']) ?></div>
            <div class="pc-sub">Owner: <?= htmlspecialchars($v['owner_name']??'Unknown') ?> | <?= htmlspecialchars($v['owner_phone']??'') ?> | Listed: <?= $v['created_at'] ?></div>
          </div>
        </div>
        <div class="badge badge-pending">PENDING</div>
      </div>
      <div class="pc-specs">
        <div class="pc-spec">Type: <span><i class="fas <?= $v['type']==='2wheeler'?'fa-motorcycle':'fa-car' ?>"></i> <?= $v['type']==='2wheeler'?'2-Wheeler':'4-Wheeler' ?></span></div>
        <div class="pc-spec">Category: <span><?= htmlspecialchars($v['category']??'') ?></span></div>
        <div class="pc-spec">City: <span><i class="fas fa-map-pin"></i> <?= htmlspecialchars($v['city']??'') ?></span></div>
        <div class="pc-spec">Owner Price: <span>₹<?= number_format($v['price_per_day']) ?>/day</span></div>
        <div class="pc-spec">AI Suggested: <span style="color:var(--blue)">₹<?= $ai['suggested_price'] ?>/day</span></div>
        <div class="pc-spec">Damage: <span>₹<?= number_format($v['damage_charge']??0) ?></span></div>
      </div>
      <div style="font-size:.82rem; color:rgba(226,232,240,0.5); margin-bottom:1.2rem; padding:1rem; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:8px;">
        <i class="fas fa-info-circle" style="color:#3B82F6; margin-right:0.4rem;"></i> <?= htmlspecialchars($ai['note']) ?>
      </div>
      <div class="pc-actions">
        <form method="POST" style="display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;">
          <input type="hidden" name="action" value="approve_vehicle">
          <input type="hidden" name="id" value="<?= $v['id'] ?>">
          <div class="pc-price-input">
            <label>Final Price (₹/day)</label>
            <input type="number" name="final_price" value="<?= $ai['suggested_price'] ?>" min="1">
          </div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve & Set Price</button>
        </form>
        <form method="POST">
          <input type="hidden" name="action" value="reject_vehicle">
          <input type="hidden" name="id" value="<?= $v['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this vehicle?')"><i class="fas fa-xmark"></i> Reject</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- PENDING BOOKINGS -->
    <?php elseif ($tab === 'pending_b'): ?>
    <?php if (empty($pendingBookings)): ?>
    <div style="text-align:center;padding:4rem;color:var(--txt2);">
      <div style="font-size:3rem;margin-bottom:1rem;"><i class="fas fa-check" style="color:var(--success);"></i></div>
      <div style="font-family:inherit;font-size:1rem;font-weight:700;text-transform:uppercase;">No pending bookings!</div>
    </div>
    <?php else: ?>
    <?php foreach($pendingBookings as $b): ?>
    <div class="pending-card">
      <div class="pc-top">
        <div>
          <div class="pc-title"><?= htmlspecialchars($b['v_title']??'Vehicle') ?></div>
          <div class="pc-sub">User: <?= htmlspecialchars($b['user_name']??'Unknown') ?> | <?= htmlspecialchars($b['user_phone']??'') ?></div>
        </div>
        <div class="badge badge-pending">PENDING</div>
      </div>
      <div class="pc-specs">
        <div class="pc-spec">Type: <span><i class="fas <?= ($b['v_type']??'')==='2wheeler'?'fa-motorcycle':'fa-car' ?>"></i> <?= ($b['v_type']??'')==='2wheeler'?'2W':'4W' ?></span></div>
        <div class="pc-spec">Dates: <span><?= $b['pickup_date']??'' ?> → <?= $b['return_date']??'' ?></span></div>
        <div class="pc-spec">Days: <span><?= $b['days']??1 ?></span></div>
        <div class="pc-spec">User Amount: <span>₹<?= number_format($b['amount']??0) ?></span></div>
        <div class="pc-spec">Daily Rate: <span>₹<?= number_format($b['final_price']??0) ?></span></div>
      </div>
      <div class="pc-actions">
        <form method="POST" style="display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;">
          <input type="hidden" name="action" value="approve_booking">
          <input type="hidden" name="id" value="<?= $b['id'] ?>">
          <div class="pc-price-input">
            <label>Final Amount (₹)</label>
            <input type="number" name="final_amount" value="<?= $b['amount']??0 ?>" min="0">
          </div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
        </form>
        <form method="POST">
          <input type="hidden" name="action" value="reject_booking">
          <input type="hidden" name="id" value="<?= $b['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this booking?')"><i class="fas fa-xmark"></i> Reject</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- ALL VEHICLES -->
    <?php elseif ($tab === 'vehicles'): ?>
    <?php if (empty($allVehicles)): ?>
    <p style="color:var(--txt2);padding:2rem 0;">No vehicles yet. Connect your database to see data.</p>
    <?php else: ?>
    <div style="overflow-x:auto;"><table class="tbl">
      <thead><tr><th>Vehicle</th><th>Owner</th><th>Type</th><th>City</th><th>Price/day</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($allVehicles as $v): ?>
      <tr>
        <td><strong><?= htmlspecialchars($v['title']) ?></strong><br><small style="color:var(--txt2)"><?= htmlspecialchars($v['category']??'') ?></small></td>
        <td><?= htmlspecialchars($v['owner_name']??'N/A') ?></td>
        <td><i class="fas <?= $v['type']==='2wheeler'?'fa-motorcycle':'fa-car' ?>"></i> <?= $v['type']==='2wheeler'?'2W':'4W' ?></td>
        <td><i class="fas fa-map-pin"></i> <?= htmlspecialchars($v['city']??'') ?></td>
        <td>₹<?= number_format($v['final_price']??$v['price_per_day']) ?></td>
        <td><span class="badge badge-<?= $v['status'] ?>"><?= strtoupper($v['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>

    <!-- ALL BOOKINGS -->
    <?php elseif ($tab === 'bookings'): ?>
    <?php if (empty($allBookings)): ?>
    <p style="color:var(--txt2);padding:2rem 0;">No bookings yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto;"><table class="tbl">
      <thead><tr><th>Vehicle</th><th>User</th><th>Dates</th><th>Days</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($allBookings as $b): ?>
      <tr>
        <td><?= htmlspecialchars($b['v_title']??'Vehicle') ?></td>
        <td><?= htmlspecialchars($b['user_name']??'N/A') ?></td>
        <td style="font-size:.76rem;"><?= $b['pickup_date']??'' ?> → <?= $b['return_date']??'' ?></td>
        <td><?= $b['days']??1 ?></td>
        <td>₹<?= number_format($b['final_amount']??$b['amount']??0) ?></td>
        <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
        <td>
          <?php if($b['status'] === 'approved'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="complete_booking">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark as completed?')"><i class="fas fa-check-double"></i> Complete</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
