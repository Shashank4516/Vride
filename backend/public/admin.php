<?php
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/admin_lib.php';

$pageTitle = 'Admin Panel — VRide';
if (!isAdmin()) {
    flash('Admin access required.', 'error');
    redirect('login.php');
}

$pdo = getDB();
$tab = preg_replace('/[^a-z_]/', '', $_GET['tab'] ?? 'dashboard') ?: 'dashboard';

/** Classic POST (no JS) — same mutations as admin_api.php */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '', $qsMerge);
    if (empty($_POST['tab']) && !empty($qsMerge['tab'])) {
        $_POST['tab'] = $qsMerge['tab'];
    }
    $res = admin_run_action($pdo, $_POST);
    if ($res['ok']) {
        flash($res['message'], $res['type'] === 'error' ? 'error' : 'success');
    } else {
        flash($res['message'], 'error');
    }
    redirect('admin.php?tab=' . rawurlencode($res['tab']));
}

$d = admin_fetch_data($pdo);
$stats = $d['stats'];
$pendingVehicles = $d['pendingVehicles'];
$pendingBookings = $d['pendingBookings'];
$allVehicles = $d['allVehicles'];
$allBookings = $d['allBookings'];
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

.adm-live-dot{display:inline-flex;align-items:center;gap:.35rem;font-size:.68rem;color:var(--success);font-weight:700;letter-spacing:.06em;}
.adm-live-dot[data-syncing="1"]{color:var(--warn);}
.adm-toast{
  position:fixed;bottom:1.25rem;right:1.25rem;max-width:min(380px,calc(100vw - 2rem));
  padding:.78rem 1.05rem;border-radius:11px;font-size:.82rem;font-weight:600;line-height:1.45;
  box-shadow:0 14px 44px rgba(0,0,0,.48);z-index:50000;
  opacity:0;pointer-events:none;transform:translateY(14px);transition:opacity .22s ease,transform .22s ease;
}
.adm-toast.show{opacity:1;transform:none;pointer-events:auto;}
.adm-toast-ok{border:1px solid rgba(0,199,122,.38);background:rgba(0,199,122,.12);color:var(--success);}
.adm-toast-error{border:1px solid rgba(232,54,93,.38);background:rgba(232,54,93,.1);color:var(--danger);}
</style>

<div id="adm-root" data-tab="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>">
<div id="adm-toast" class="adm-toast" role="status" aria-live="polite"></div>
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
      <a href="admin.php?tab=dashboard" class="adm-live-tab <?= $tab==='dashboard'?'on':'' ?>" data-tab="dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
      <div class="adm-nav-title">Manage</div>
      <?php $pv = (int)$stats['pending_v']; $pb = (int)$stats['pending_b']; ?>
      <a href="admin.php?tab=vehicles" class="adm-live-tab <?= $tab==='vehicles'?'on':'' ?>" data-tab="vehicles"><i class="fas fa-car"></i> All Vehicles
        <span data-adm-badge="pending_v" style="<?= $pv>0 ? '' : 'display:none;' ?>background:var(--blue);color:#000;padding:.1rem .4rem;border-radius:10px;font-size:.6rem;margin-left:.35rem;"><?= $pv ?></span></a>
      <a href="admin.php?tab=bookings" class="adm-live-tab <?= $tab==='bookings'?'on':'' ?>" data-tab="bookings"><i class="fas fa-list"></i> All Bookings
        <span data-adm-badge="pending_b" style="<?= $pb>0 ? '' : 'display:none;' ?>background:var(--blue);color:#000;padding:.1rem .4rem;border-radius:10px;font-size:.6rem;margin-left:.35rem;"><?= $pb ?></span></a>
      <a href="admin.php?tab=pending_v" class="adm-live-tab <?= $tab==='pending_v'?'on':'' ?>" data-tab="pending_v"><i class="fas fa-hourglass-end"></i> Pending Vehicles</a>
      <a href="admin.php?tab=pending_b" class="adm-live-tab <?= $tab==='pending_b'?'on':'' ?>" data-tab="pending_b"><i class="fas fa-hourglass-end"></i> Pending Bookings</a>
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
      <div style="display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;">
        <span id="adm-live-indicator" class="adm-live-dot"><i class="fas fa-circle" style="font-size:.38rem;"></i> LIVE SYNC</span>
        <a href="index.php" class="btn btn-secondary btn-sm">View Site</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>

<div id="adm-tab-body">
    <!-- DASHBOARD -->
    <?php if ($tab === 'dashboard'): ?>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-car"></i></div><div class="stat-n" data-adm-stat="total_vehicles"><?= (int)$stats['total_vehicles'] ?></div><div class="stat-l">Total Vehicles</div></div>
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-hourglass-end"></i></div><div class="stat-n" style="color:var(--warn)" data-adm-stat="pending_v"><?= (int)$stats['pending_v'] ?></div><div class="stat-l">Pending Vehicles</div></div>
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-list"></i></div><div class="stat-n" data-adm-stat="total_bookings"><?= (int)$stats['total_bookings'] ?></div><div class="stat-l">Total Bookings</div></div>
      <div class="stat-card"><div class="stat-icon">🔔</div><div class="stat-n" style="color:var(--warn)" data-adm-stat="pending_b"><?= (int)$stats['pending_b'] ?></div><div class="stat-l">Pending Bookings</div></div>
      <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-n" data-adm-stat="users"><?= (int)$stats['users'] ?></div><div class="stat-l">Users</div></div>
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
      <div>        <div style="font-family:inherit;font-size:.75rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--blue);margin-bottom:.8rem;">Quick Actions</div>
        <div style="display:flex;flex-direction:column;gap:.5rem;">
          <a href="admin.php?tab=pending_v" class="btn btn-primary adm-live-tab" data-tab-link="pending_v">Review Pending Vehicles</a>
          <a href="admin.php?tab=pending_b" class="btn btn-secondary adm-live-tab" data-tab-link="pending_b">Review Pending Bookings</a>
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
        <form method="POST" class="adm-ajax-form" style="display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;">
          <input type="hidden" name="action" value="approve_vehicle">
          <input type="hidden" name="id" value="<?= $v['id'] ?>">
          <div class="pc-price-input">
            <label>Final Price (₹/day)</label>
            <input type="number" name="final_price" value="<?= $ai['suggested_price'] ?>" min="1">
          </div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve & Set Price</button>
        </form>
        <form method="POST" class="adm-ajax-form">
          <input type="hidden" name="action" value="reject_vehicle">
          <input type="hidden" name="id" value="<?= $v['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</button>
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
        <form method="POST" class="adm-ajax-form" style="display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;">
          <input type="hidden" name="action" value="approve_booking">
          <input type="hidden" name="id" value="<?= $b['id'] ?>">
          <div class="pc-price-input">
            <label>Final Amount (₹)</label>
            <input type="number" name="final_amount" value="<?= $b['amount']??0 ?>" min="0">
          </div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
        </form>
        <form method="POST" class="adm-ajax-form">
          <input type="hidden" name="action" value="reject_booking">
          <input type="hidden" name="id" value="<?= $b['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</button>
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
      <thead><tr><th>Vehicle</th><th>Owner</th><th>Type</th><th>City</th><th>Price/day</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($allVehicles as $v): ?>
      <tr>
        <td><strong><?= htmlspecialchars($v['title']) ?></strong><br><small style="color:var(--txt2)"><?= htmlspecialchars($v['category']??'') ?></small></td>
        <td><?= htmlspecialchars($v['owner_name']??'N/A') ?></td>
        <td><i class="fas <?= $v['type']==='2wheeler'?'fa-motorcycle':'fa-car' ?>"></i> <?= $v['type']==='2wheeler'?'2W':'4W' ?></td>
        <td><i class="fas fa-map-pin"></i> <?= htmlspecialchars($v['city']??'') ?></td>
        <td>₹<?= number_format($v['final_price']??$v['price_per_day']) ?></td>
        <td><span class="badge badge-<?= $v['status'] ?>"><?= strtoupper($v['status']) ?></span></td>
        <td>
          <form method="POST" class="adm-ajax-form" style="display:inline-flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
            <input type="hidden" name="action" value="approve_vehicle">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <input type="number" name="final_price" value="<?= $v['final_price']??$v['price_per_day'] ?>" min="1" style="width:95px;">
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
          </form>
          <form method="POST" class="adm-ajax-form" style="display:inline;">
            <input type="hidden" name="action" value="reject_vehicle">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</button>
          </form>
        </td>
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
          <form method="POST" class="adm-ajax-form" style="display:inline;">
            <input type="hidden" name="action" value="complete_booking">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i> Complete</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
    <?php endif; ?>
</div><!-- #adm-tab-body -->
  </div>
</div>
<?php
$__ej = [];
$__ejPath = dirname(__DIR__) . '/config/emailjs_config.php';
if (is_file($__ejPath)) {
    $__ej = require $__ejPath;
}
if (!is_array($__ej)) {
    $__ej = [];
}
$__ej_boot = json_encode([
    'publicKey' => $__ej['public_key'] ?? '',
    'serviceId' => $__ej['service_id'] ?? '',
    'templateId' => $__ej['template_id_vehicle_approved'] ?? '',
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
if ($__ej_boot === false) {
    $__ej_boot = '{}';
}
?>
<script type="application/json" id="vride-emailjs-boot"><?php echo $__ej_boot; ?></script>
<script>
window.__VRIDE_EMAILJS__ = {};
try {
  window.__VRIDE_EMAILJS__ = JSON.parse(document.getElementById('vride-emailjs-boot').textContent || '{}');
} catch (_) {}
</script>
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js" defer></script>
<script src="js/email.js?v=3" defer></script>
<script src="js/admin-realtime.js?v=4" defer></script>
</div><!-- #adm-root -->
</body>
</html>
