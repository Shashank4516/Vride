<?php
require_once 'db.php';
$pageTitle = 'Contact — VRide';
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $sent = true; }
?>
<?php include 'header.php'; ?>
<style>
/* ── Contact page layout ─────────────────────────── */
.ct-wrap {
  padding-top:var(--nav-h);
  padding-left:var(--sidebar-w);
}
.ct-hero {
  max-width:1000px;
  margin:0 auto;
  padding:3rem 2.5rem 1.5rem;
}
.ct-inner {
  max-width:1000px;
  margin:0 auto;
  padding:0 2.5rem 6rem;
  display:grid;
  grid-template-columns:1fr 340px;
  gap:2.5rem;
  align-items:start;
}

/* ── Info cards ──────────────────────────────────── */
.info-card {
  background:var(--card);
  border:1px solid rgba(255,255,255,.06);
  border-radius:8px;
  padding:1.6rem 1.8rem;
  margin-bottom:1rem;
}
.info-card:last-child { margin-bottom:0; }
.info-title {
  font-size:.65rem;
  font-weight:700;
  letter-spacing:.22em;
  text-transform:uppercase;
  color:var(--blue);
  margin-bottom:1.2rem;
  display:flex;
  align-items:center;
  gap:.5rem;
}
.info-item {
  display:flex;
  gap:.9rem;
  margin-bottom:1.1rem;
  align-items:flex-start;
}
.info-item:last-child { margin-bottom:0; }
.info-icon {
  width:32px;
  height:32px;
  border-radius:6px;
  background:rgba(26,140,255,.08);
  border:1px solid rgba(26,140,255,.15);
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--blue);
  font-size:.82rem;
  flex-shrink:0;
}
.info-lbl {
  font-size:.6rem;
  font-weight:700;
  letter-spacing:.15em;
  text-transform:uppercase;
  color:var(--txt2);
  margin-bottom:.2rem;
}
.info-val {
  font-size:.84rem;
  color:var(--txt);
  line-height:1.6;
}

/* ── Social pills ────────────────────────────────── */
.social-pills {
  display:flex;
  gap:.5rem;
  flex-wrap:wrap;
  margin-top:.5rem;
}
.social-pill {
  display:inline-flex;
  align-items:center;
  gap:.4rem;
  padding:.4rem .85rem;
  border:1px solid rgba(255,255,255,.08);
  border-radius:30px;
  color:var(--txt2);
  font-size:.72rem;
  font-weight:600;
  letter-spacing:.04em;
  transition:all .25s;
}
.social-pill:hover {
  border-color:var(--blue);
  color:var(--blue);
  background:rgba(26,140,255,.06);
}
.social-pill i { font-size:.75rem; }

/* ── Success banner ──────────────────────────────── */
.success-banner {
  display:flex;
  align-items:center;
  gap:.75rem;
  padding:1rem 1.2rem;
  background:rgba(0,199,122,.07);
  border:1px solid rgba(0,199,122,.22);
  border-radius:6px;
  color:var(--success);
  font-size:.88rem;
  font-weight:600;
  margin-bottom:1.5rem;
}
.success-banner i { font-size:1rem; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width:800px) {
  .ct-inner { grid-template-columns:1fr; }
}
@media (max-width:768px) {
  .ct-wrap  { padding-left:0; }
  .ct-hero  { padding-left:1.5rem; padding-right:1.5rem; }
  .ct-inner { padding-left:1.5rem; padding-right:1.5rem; }
}
</style>

<div class="ct-wrap">

  <!-- Page heading -->
  <div class="ct-hero">
    <div class="sec-label">Get In Touch</div>
    <div class="sec-h">CONTACT <span class="dim">US</span></div>
  </div>

  <div class="ct-inner">

    <!-- ── LEFT: Contact form ── -->
    <div class="form-card">
      <?php if ($sent): ?>
      <div class="success-banner">
        <i class="fa-solid fa-circle-check"></i>
        Message sent! We'll reply within 2 hours.
      </div>
      <?php endif; ?>

      <div class="form-section-title">
        <i class="fa-regular fa-comment-dots"></i>
        Send a Message
      </div>

      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" placeholder="Your full name" required>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="you@email.com" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX">
          </div>
          <div class="form-group">
            <label>Enquiry Type</label>
            <select name="type">
              <option>General</option>
              <option>Booking Help</option>
              <option>List My Vehicle</option>
              <option>Technical Issue</option>
              <option>Partnership</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Message *</label>
          <textarea name="message" rows="5" placeholder="How can we help you?" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
          Send Message
          <i class="fa-solid fa-paper-plane" style="font-size:.78rem;"></i>
        </button>
      </form>
    </div>

    <!-- ── RIGHT: Info sidebar ── -->
    <div>
      <div class="info-card">
        <div class="info-title">
          <i class="fa-solid fa-address-card"></i>
          Contact Details
        </div>

        <div class="info-item">
          <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div>
            <div class="info-lbl">Address</div>
            <div class="info-val">VRide HQ, Bandra West<br>Mumbai 400050, India</div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
          <div>
            <div class="info-lbl">Phone</div>
            <div class="info-val">+91 98765 43210<br>+91 80000 12345</div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
          <div>
            <div class="info-lbl">Email</div>
            <div class="info-val">hello@vride.in<br>support@vride.in</div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon"><i class="fa-regular fa-clock"></i></div>
          <div>
            <div class="info-lbl">Hours</div>
            <div class="info-val">Mon–Sat: 8 am – 9 pm<br>Emergencies: 24 / 7</div>
          </div>
        </div>
      </div>

      <div class="info-card">
        <div class="info-title">
          <i class="fa-solid fa-share-nodes"></i>
          Follow Us
        </div>
        <div class="social-pills">
          <a href="#" class="social-pill"><i class="fa-brands fa-instagram"></i> Instagram</a>
          <a href="#" class="social-pill"><i class="fa-brands fa-x-twitter"></i> Twitter</a>
          <a href="#" class="social-pill"><i class="fa-brands fa-linkedin-in"></i> LinkedIn</a>
          <a href="#" class="social-pill"><i class="fa-brands fa-facebook-f"></i> Facebook</a>
          <a href="#" class="social-pill"><i class="fa-brands fa-youtube"></i> YouTube</a>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>
