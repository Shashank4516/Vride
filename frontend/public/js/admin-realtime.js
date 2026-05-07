/**
 * Admin panel: AJAX actions + polled snapshot refresh (near real-time without WebSockets).
 */
(function () {
  const root = document.getElementById('adm-root');
  const bodyEl = document.getElementById('adm-tab-body');
  const toast = document.getElementById('adm-toast');
  const liveDot = document.getElementById('adm-live-indicator');
  if (!root || !bodyEl) return;

  const POLL_MS = 8000;

  function esc(s) {
    if (s == null || s === '') return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/"/g, '&quot;');
  }

  function showToast(msg, type) {
    if (!toast || !msg) return;
    toast.textContent = msg;
    toast.className = 'adm-toast show adm-toast-' + (type === 'error' ? 'error' : 'ok');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => {
      toast.classList.remove('show');
    }, 4200);
  }

  function setLiveBusy(on) {
    if (!liveDot) return;
    liveDot.dataset.syncing = on ? '1' : '0';
  }

  async function fetchSnapshot(signal) {
    const tab = root.dataset.tab || 'dashboard';
    const url = new URL('admin_api.php', window.location.href);
    url.searchParams.set('action', 'snapshot');
    url.searchParams.set('tab', tab);
    const r = await fetch(url.toString(), { credentials: 'same-origin', signal });
    const data = await r.json().catch(() => ({}));
    if (!r.ok || !data.ok) throw new Error(data.error || 'Snapshot failed');
    return data;
  }

  async function postAction(form) {
    const fd = new FormData(form);
    fd.set('tab', root.dataset.tab || 'dashboard');
    const r = await fetch(new URL('admin_api.php', window.location.href).toString(), {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'VRideAdmin' },
    });
    const data = await r.json().catch(() => ({}));
    if (!r.ok || !data.ok)
      throw new Error(data.message || data.error || `Request failed (${r.status})`);
    return data;
  }

  function updateStats(stats) {
    if (!stats) return;
    document.querySelectorAll('[data-adm-stat]').forEach((el) => {
      const key = el.getAttribute('data-adm-stat');
      if (stats[key] == null) return;
      el.textContent = stats[key];
    });
    const pv = stats.pending_v ?? 0;
    const pb = stats.pending_b ?? 0;
    document.querySelectorAll('[data-adm-badge="pending_v"]').forEach((x) => {
      x.style.display = pv > 0 ? 'inline-block' : 'none';
      x.textContent = pv;
    });
    document.querySelectorAll('[data-adm-badge="pending_b"]').forEach((x) => {
      x.style.display = pb > 0 ? 'inline-block' : 'none';
      x.textContent = pb;
    });
  }

  function renderDashboard(s) {
    updateStats(s.stats);
    return (
      `<div class="stats-grid">
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-car"></i></div><div class="stat-n" data-adm-stat="total_vehicles">${esc(s.stats.total_vehicles)}</div><div class="stat-l">Total Vehicles</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-hourglass-end"></i></div><div class="stat-n" style="color:var(--warn)" data-adm-stat="pending_v">${esc(s.stats.pending_v)}</div><div class="stat-l">Pending Vehicles</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-list"></i></div><div class="stat-n" data-adm-stat="total_bookings">${esc(s.stats.total_bookings)}</div><div class="stat-l">Total Bookings</div></div>
        <div class="stat-card"><div class="stat-icon">🔔</div><div class="stat-n" style="color:var(--warn)" data-adm-stat="pending_b">${esc(s.stats.pending_b)}</div><div class="stat-l">Pending Bookings</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-n" data-adm-stat="users">${esc(s.stats.users)}</div><div class="stat-l">Users</div></div>
      </div>
      <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
        <div style="font-size:0.8rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#3B82F6; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
          <i class="fas fa-shield-check"></i> System Oversight Dashboard
        </div>
        <div style="font-size:.85rem;line-height:1.8;color:var(--txt2);">
          <strong style="color:var(--white);">Live data</strong> — stats refresh automatically every few seconds.<br>
          When an owner submits a vehicle, the AI scoring engine (0–100) reviews completeness and pricing.<br>
          <span style="color:var(--success);">Score ≥ 60 → suggested auto-approve path</span> &nbsp;|&nbsp;
          <span style="color:var(--warn);">Score &lt; 60 → manual review</span><br>
          Override pricing anytime under <strong style="color:var(--blue);">Pending Vehicles</strong>.
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <div><div style="font-family:inherit;font-size:.75rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--blue);margin-bottom:.8rem;">Quick Actions</div>
          <div style="display:flex;flex-direction:column;gap:.5rem;">
            <a href="admin.php?tab=pending_v" class="btn btn-primary adm-nav-soft" data-tab-link="pending_v">Review Pending Vehicles</a>
            <a href="admin.php?tab=pending_b" class="btn btn-secondary adm-nav-soft" data-tab-link="pending_b">Review Pending Bookings</a>
          </div>
        </div>
        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:12px; padding:1.2rem;">
          <div style="font-family:inherit;font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--txt2);margin-bottom:.5rem;">Synced</div>
          <div style="font-size:.78rem;color:var(--txt2);">Last update: <span data-adm-clock>${new Date((s.server_time || 0) * 1000).toLocaleTimeString()}</span></div>
        </div>
      </div>`
    );
  }

  function pendingVehicleHtml(v) {
    const ai = v.ai || { score: 0, decision: '?', suggested_price: 0, note: '' };
    const ic = `<div class="system-tag"><i class="fas fa-microchip" style="font-size:0.7rem;"></i> Score: ${esc(ai.score)}/100 — ${esc(String(ai.decision).toUpperCase())}</div>`;
    const img =
      v.image
        ? `<div style="width:120px; height:80px; flex-shrink:0; border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,0.1);"><img src="${esc(v.image)}" alt="" style="width:100%; height:100%; object-fit:cover;"></div>`
        : '';

    const vtype =
      v.type === '2wheeler'
        ? '<i class="fas fa-motorcycle"></i> 2-Wheeler'
        : '<i class="fas fa-car"></i> 4-Wheeler';
    const sugg = ai.suggested_price != null ? Number(ai.suggested_price) : '';

    return (
      `<div class="pending-card" data-entity-id="v-${esc(v.id)}">` +
      ic +
      `<div class="pc-top">
        <div style="display:flex; gap:1.5rem; align-items:flex-start;">
          ${img}
          <div>
            <div class="pc-title">${esc(v.title)}</div>
            <div class="pc-sub">Owner: ${esc(v.owner_name || 'Unknown')} | ${esc(v.owner_phone || '')} | Listed: ${esc(v.created_at)}</div>
          </div>
        </div>
        <div class="badge badge-pending">PENDING</div>
      </div>
      <div class="pc-specs">
        <div class="pc-spec">Type: <span>${vtype}</span></div>
        <div class="pc-spec">Category: <span>${esc(v.category || '')}</span></div>
        <div class="pc-spec">City: <span><i class="fas fa-map-pin"></i> ${esc(v.city || '')}</span></div>
        <div class="pc-spec">Owner Price: <span>₹${esc(Number(v.price_per_day || 0).toLocaleString('en-IN'))}/day</span></div>
        <div class="pc-spec">AI Suggested: <span style="color:var(--blue)">₹${esc(sugg)}/day</span></div>
        <div class="pc-spec">Damage: <span>₹${esc(Number(v.damage_charge || 0).toLocaleString('en-IN'))}</span></div>
      </div>
      <div style="font-size:.82rem; color:rgba(226,232,240,0.5); margin-bottom:1.2rem; padding:1rem; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:8px;">
        <i class="fas fa-info-circle" style="color:#3B82F6; margin-right:0.4rem;"></i> ${esc(ai.note)}
      </div>
      <div class="pc-actions">
        <form method="POST" style="display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;" class="adm-ajax-form">
          <input type="hidden" name="action" value="approve_vehicle">
          <input type="hidden" name="id" value="${esc(v.id)}">
          <div class="pc-price-input"><label>Final Price (₹/day)</label>
            <input type="number" name="final_price" value="${esc(sugg || v.price_per_day)}" min="1"></div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
        </form>
        <form method="POST" class="adm-ajax-form">
          <input type="hidden" name="action" value="reject_vehicle">
          <input type="hidden" name="id" value="${esc(v.id)}">
          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</button>
        </form>
      </div></div>`
    );
  }

  function renderPendingBookings(bs) {
    if (!bs || !bs.length) {
      return `<div style="text-align:center;padding:4rem;color:var(--txt2);">
        <div style="font-size:3rem;margin-bottom:1rem;"><i class="fas fa-check" style="color:var(--success);"></i></div>
        <div style="font-family:inherit;font-size:1rem;font-weight:700;text-transform:uppercase;">No pending bookings!</div></div>`;
    }
    return bs.map(pendingBookingHtml).join('');
  }

  function pendingBookingHtml(b) {
    const wt = (b.v_type || '') === '2wheeler' ? '<i class="fas fa-motorcycle"></i> 2W' : '<i class="fas fa-car"></i> 4W';
    return `<div class="pending-card" data-entity-id="b-${esc(b.id)}">
      <div class="pc-top">
        <div>
          <div class="pc-title">${esc(b.v_title || 'Vehicle')}</div>
          <div class="pc-sub">User: ${esc(b.user_name || 'Unknown')} | ${esc(b.user_phone || '')}</div>
        </div>
        <div class="badge badge-pending">PENDING</div>
      </div>
      <div class="pc-specs">
        <div class="pc-spec">Type: <span>${wt}</span></div>
        <div class="pc-spec">Dates: <span>${esc(b.pickup_date)} → ${esc(b.return_date)}</span></div>
        <div class="pc-spec">Days: <span>${esc(b.days || 1)}</span></div>
        <div class="pc-spec">User Amount: <span>₹${esc(Number(b.amount || 0).toLocaleString('en-IN'))}</span></div>
        <div class="pc-spec">Daily Rate: <span>₹${esc(Number(b.final_price || 0).toLocaleString('en-IN'))}</span></div>
      </div>
      <div class="pc-actions">
        <form method="POST" style="display:flex;gap:.7rem;align-items:flex-end;flex-wrap:wrap;" class="adm-ajax-form">
          <input type="hidden" name="action" value="approve_booking">
          <input type="hidden" name="id" value="${esc(b.id)}">
          <div class="pc-price-input"><label>Final Amount (₹)</label>
            <input type="number" name="final_amount" value="${esc(b.amount ?? 0)}" min="0"></div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
        </form>
        <form method="POST" class="adm-ajax-form">
          <input type="hidden" name="action" value="reject_booking">
          <input type="hidden" name="id" value="${esc(b.id)}">
          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</button>
        </form>
      </div></div>`;
  }

  function renderAllVehicles(vs, db) {
    if (!db) return '<p style="color:var(--txt2);padding:2rem 0;">Connect your database to see data.</p>';
    if (!vs || !vs.length) return '<p style="color:var(--txt2);padding:2rem 0;">No vehicles yet.</p>';
    const rows = vs.map((v) => vehicleRow(v)).join('');
    return `<div style="overflow-x:auto;"><table class="tbl">
      <thead><tr><th>Vehicle</th><th>Owner</th><th>Type</th><th>City</th><th>Price/day</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
  }

  function vehicleRow(v) {
    const ic = v.type === '2wheeler' ? '<i class="fas fa-motorcycle"></i> 2W' : '<i class="fas fa-car"></i> 4W';
    const price = v.final_price ?? v.price_per_day;
    return `<tr data-entity-id="av-${esc(v.id)}">
      <td><strong>${esc(v.title)}</strong><br><small style="color:var(--txt2)">${esc(v.category || '')}</small></td>
      <td>${esc(v.owner_name || 'N/A')}</td>
      <td>${ic}</td>
      <td><i class="fas fa-map-pin"></i> ${esc(v.city || '')}</td>
      <td>₹${esc(Number(price || 0).toLocaleString('en-IN'))}</td>
      <td><span class="badge badge-${esc(v.status)}">${esc(String(v.status).toUpperCase())}</span></td>
      <td>
        <form method="POST" style="display:inline-flex;gap:.5rem;align-items:center;flex-wrap:wrap;" class="adm-ajax-form">
          <input type="hidden" name="action" value="approve_vehicle">
          <input type="hidden" name="id" value="${esc(v.id)}">
          <input type="number" name="final_price" value="${esc(price)}" min="1" style="width:95px;">
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
        </form>
        <form method="POST" style="display:inline;" class="adm-ajax-form">
          <input type="hidden" name="action" value="reject_vehicle">
          <input type="hidden" name="id" value="${esc(v.id)}">
          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</button>
        </form>
      </td></tr>`;
  }

  function renderAllBookings(bs, db) {
    if (!db) return '<p style="color:var(--txt2);padding:2rem 0;">Connect database for bookings.</p>';
    if (!bs || !bs.length) return '<p style="color:var(--txt2);padding:2rem 0;">No bookings yet.</p>';
    const rows = bs.map(bookingRow).join('');
    return `<div style="overflow-x:auto;"><table class="tbl">
      <thead><tr><th>Vehicle</th><th>User</th><th>Dates</th><th>Days</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
  }

  function bookingRow(b) {
    const amt = Number(b.final_amount ?? b.amount ?? 0).toLocaleString('en-IN');
    let actions = '';
    if (String(b.status) === 'approved') {
      actions = `<form method="POST" style="display:inline;" class="adm-ajax-form">
        <input type="hidden" name="action" value="complete_booking">
        <input type="hidden" name="id" value="${esc(b.id)}">
        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i> Complete</button>
      </form>`;
    }
    return `<tr data-entity-id="ab-${esc(b.id)}">
      <td>${esc(b.v_title || 'Vehicle')}</td>
      <td>${esc(b.user_name || 'N/A')}</td>
      <td style="font-size:.76rem;">${esc(b.pickup_date)} → ${esc(b.return_date)}</td>
      <td>${esc(b.days || 1)}</td>
      <td>₹${esc(amt)}</td>
      <td><span class="badge badge-${esc(b.status)}">${esc(String(b.status).toUpperCase())}</span></td>
      <td>${actions}</td>
    </tr>`;
  }

  function paintTab(snapshot) {
    const tab = root.dataset.tab || 'dashboard';

    document.querySelectorAll('[data-adm-clock]').forEach((el) => {
      el.textContent = new Date((snapshot.server_time || 0) * 1000).toLocaleTimeString();
    });

    if (tab === 'dashboard') bodyEl.innerHTML = renderDashboard(snapshot);
    else if (tab === 'pending_v') {
      const list = snapshot.pendingVehicles || [];
      if (!list.length) {
        bodyEl.innerHTML = `<div style="text-align:center;padding:4rem;color:var(--txt2);">
          <div style="font-size:3rem;margin-bottom:1rem;"><i class="fas fa-check" style="color:var(--success);"></i></div>
          <div style="font-family:inherit;font-size:1rem;font-weight:700;text-transform:uppercase;">No pending vehicles</div>
          <p style="font-size:.8rem;margin-top:.5rem;">Updates appear here automatically.</p></div>`;
      } else bodyEl.innerHTML = list.map(pendingVehicleHtml).join('');
    } else if (tab === 'pending_b') bodyEl.innerHTML = renderPendingBookings(snapshot.pendingBookings);
    else if (tab === 'vehicles') bodyEl.innerHTML = renderAllVehicles(snapshot.allVehicles, snapshot.db);
    else if (tab === 'bookings') bodyEl.innerHTML = renderAllBookings(snapshot.allBookings, snapshot.db);

    updateStats(snapshot.stats);
  }

  let abortCtl = null;

  async function poll() {
    if (document.hidden) return;
    abortCtl?.abort();
    abortCtl = new AbortController();
    try {
      setLiveBusy(true);
      const snap = await fetchSnapshot(abortCtl.signal);
      paintTab(snap);
    } catch (e) {
      if (e.name !== 'AbortError') console.warn('Admin snapshot:', e.message);
    } finally {
      setLiveBusy(false);
    }
  }

  bodyEl.addEventListener('submit', async (ev) => {
    const form = ev.target;
    if (!(form instanceof HTMLFormElement) || !form.classList.contains('adm-ajax-form')) return;
    if (!form.closest('#adm-tab-body')) return;

    /* confirm reject / complete flows */
    const act = (form.querySelector('input[name="action"]') || {}).value;
    if (act === 'reject_vehicle' || act === 'reject_booking') {
      if (!window.confirm('Reject this item?')) {
        ev.preventDefault();
        return;
      }
    }
    if (act === 'complete_booking') {
      if (!window.confirm('Mark booking as completed?')) {
        ev.preventDefault();
        return;
      }
    }

    ev.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.dataset.lbl = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    try {
      const data = await postAction(form);
      root.dataset.tab = data.tab || root.dataset.tab;

      const ej = data.emailjs_vehicle_approved;
      const actVehicle = act === 'approve_vehicle';
      const runEmailJs =
        actVehicle &&
        ej &&
        window.VRideEmail &&
        typeof window.VRideEmail.sendVehicleApprovedEmailJS === 'function';

      if (runEmailJs) {
        try {
          await window.VRideEmail.sendVehicleApprovedEmailJS(ej);
          showToast(data.message || 'Vehicle approved. Owner notified.', data.messageType || 'success');
        } catch (err) {
          console.warn('EmailJS vehicle approved:', err);
          showToast(
            (err && err.message) ||
              'Vehicle saved, but EmailJS failed. Use {{to_email}} for recipient, check EmailJS History / Security (allowed origins), and Console.',
            'error'
          );
        }
      } else {
        showToast(data.message || 'Done', data.messageType || 'success');
      }

      paintTab(data);
    } catch (err) {
      showToast(err.message || 'Failed', 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        if (btn.dataset.lbl != null) btn.innerHTML = btn.dataset.lbl;
      }
    }
  });

  /** SPA-style tab switches (stay on admin, refresh panel via snapshot API) */
  document.querySelectorAll('a.adm-live-tab, a[data-tab-link]').forEach((a) => {
    a.addEventListener('click', (ev) => {
      const tabKey =
        a.getAttribute('data-tab-link') ||
        a.getAttribute('data-tab') ||
        a.href.match(/[?&]tab=([a-z_]+)/)?.[1];

      try {
        const u = new URL(a.href);
        if (/logout\.php$/i.test(u.pathname)) return;
        if (!tabKey) return;
        if (!u.pathname.includes('admin.php')) return;
      } catch (_) {
        return;
      }

      if (ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey || a.target === '_blank') return;
      ev.preventDefault();
      const t = tabKey;
      history.pushState({}, '', 'admin.php?tab=' + encodeURIComponent(t));
      root.dataset.tab = t;
      document.querySelectorAll('.adm-live-tab').forEach((x) => x.classList.remove('on'));
      document.querySelector(`.adm-live-tab[data-tab="${t}"]`)?.classList.add('on');
      poll();

      const titles = {
        dashboard: '<i class="fas fa-chart-line"></i> Dashboard',
        vehicles: '<i class="fas fa-car"></i> All Vehicles',
        bookings: '<i class="fas fa-list"></i> All Bookings',
        pending_v: '<i class="fas fa-hourglass-end"></i> Pending Vehicles',
        pending_b: '<i class="fas fa-hourglass-end"></i> Pending Bookings',
      };
      const hEl = document.querySelector('.adm-h');
      if (hEl && titles[t]) hEl.innerHTML = titles[t];
    });
  });

  window.addEventListener('popstate', () => {
    const p = new URLSearchParams(location.search).get('tab') || 'dashboard';
    root.dataset.tab = p;
    document.querySelectorAll('.adm-live-tab').forEach((x) => x.classList.remove('on'));
    document.querySelector(`.adm-live-tab[data-tab="${p}"]`)?.classList.add('on');
    poll();
  });

  setInterval(poll, POLL_MS);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) poll();
  });

  poll();
})();
