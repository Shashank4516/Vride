(function () {
  const shop = {
    name: 'VRide',
    address: 'VRide HQ, Punjab, Jalandhar 144411, India',
    phones: ['+91 98765 43210', '+91 80000 12345'],
    emails: ['hello@vride.in', 'support@vride.in'],
    hours: 'Mon-Sat: 8 am - 9 pm | Emergencies: 24 / 7',
  };

  function money(value) {
    const amount = Number(value || 0);
    return '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function esc(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function normalizeAddons(addons) {
    if (!addons) return [];
    if (Array.isArray(addons)) return addons.map((item) => String(item).trim()).filter(Boolean);
    if (typeof addons === 'string') {
      try {
        const parsed = JSON.parse(addons);
        if (Array.isArray(parsed)) return parsed.map((item) => String(item).trim()).filter(Boolean);
      } catch (err) {
        return [addons].filter(Boolean);
      }
    }
    return [];
  }

  function buildInvoiceHtml(payload) {
    const booking = payload.booking || {};
    const user = payload.user || {};
    const vehicle = payload.vehicle || {};
    const addons = normalizeAddons(booking.addons);
    const invoiceNo = 'VR-' + String(booking.id || 0).padStart(6, '0');
    const addonRows = addons.length
      ? addons.map((addon) => `<tr><td style="padding:8px 0;color:#d9e2ff;">${esc(addon)}</td><td style="padding:8px 0;text-align:right;color:#d9e2ff;">Included</td></tr>`).join('')
      : '<tr><td style="padding:8px 0;color:#7f8ba5;">No add-ons selected</td><td style="padding:8px 0;text-align:right;color:#7f8ba5;">-</td></tr>';

    return `
      <div style="max-width:720px;margin:0 auto;background:#08101f;color:#ecf2ff;font-family:Arial,Helvetica,sans-serif;">
        <div style="background:#0b1220;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;">
          <div style="padding:24px 28px;background:linear-gradient(135deg,#0f1a30,#0b1220);border-bottom:1px solid rgba(255,255,255,.08);">
            <div style="font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#6ea8ff;font-weight:700;">Booking Approved</div>
            <div style="font-size:28px;font-weight:800;margin-top:8px;">${esc(shop.name)} Invoice</div>
            <div style="margin-top:10px;color:#8fa0c2;font-size:14px;line-height:1.6;">Your booking has been approved by the admin team. Keep this email for your records and pickup reference.</div>
          </div>
          <div style="padding:28px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
              <tr>
                <td style="padding-bottom:20px;vertical-align:top;width:50%;">
                  <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6ea8ff;font-weight:700;margin-bottom:8px;">Customer</div>
                  <div style="font-size:18px;font-weight:700;color:#ffffff;">${esc(user.name || 'Customer')}</div>
                  <div style="margin-top:6px;color:#a8b6d6;font-size:14px;line-height:1.6;">${esc(user.email || '')}<br>${esc(user.phone || '')}</div>
                </td>
                <td style="padding-bottom:20px;vertical-align:top;width:50%;text-align:right;">
                  <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6ea8ff;font-weight:700;margin-bottom:8px;">Invoice No.</div>
                  <div style="font-size:18px;font-weight:700;color:#ffffff;">${esc(invoiceNo)}</div>
                  <div style="margin-top:6px;color:#a8b6d6;font-size:14px;line-height:1.6;">Approved booking reference<br>#${esc(booking.id || '')}</div>
                </td>
              </tr>
            </table>
            <div style="background:#0f1a30;border:1px solid rgba(110,168,255,.18);border-radius:14px;padding:18px 20px;margin:10px 0 22px;">
              <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.8;">
                <tr><td style="color:#8fa0c2;">Vehicle</td><td style="text-align:right;color:#ffffff;font-weight:700;">${esc(vehicle.title || 'Vehicle')}</td></tr>
                <tr><td style="color:#8fa0c2;">Pickup Date</td><td style="text-align:right;color:#ffffff;font-weight:700;">${esc(booking.pickup_date || '')}</td></tr>
                <tr><td style="color:#8fa0c2;">Return Date</td><td style="text-align:right;color:#ffffff;font-weight:700;">${esc(booking.return_date || '')}</td></tr>
                <tr><td style="color:#8fa0c2;">Duration</td><td style="text-align:right;color:#ffffff;font-weight:700;">${esc(booking.days || 1)} day(s)</td></tr>
                <tr><td style="color:#8fa0c2;">Daily Rate</td><td style="text-align:right;color:#ffffff;font-weight:700;">${esc(money(vehicle.final_price ?? vehicle.price_per_day))}</td></tr>
                <tr><td style="color:#8fa0c2;">Payment Method</td><td style="text-align:right;color:#ffffff;font-weight:700;">${esc((booking.payment_method || 'cash').replace(/_/g, ' '))}</td></tr>
              </table>
            </div>
            <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6ea8ff;font-weight:700;margin-bottom:10px;">Invoice Breakdown</div>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.6;margin-bottom:18px;">
              <tr><td style="padding:8px 0;color:#d9e2ff;">Rental total</td><td style="padding:8px 0;text-align:right;color:#d9e2ff;">${esc(money(booking.final_amount ?? booking.amount))}</td></tr>
              ${addonRows}
              <tr><td style="padding:10px 0 0;color:#ffffff;font-size:16px;font-weight:800;border-top:1px solid rgba(255,255,255,.08);">Amount due</td><td style="padding:10px 0 0;text-align:right;color:#6ea8ff;font-size:16px;font-weight:800;border-top:1px solid rgba(255,255,255,.08);">${esc(money(booking.final_amount ?? booking.amount))}</td></tr>
            </table>
            <div style="background:rgba(110,168,255,.08);border:1px solid rgba(110,168,255,.18);border-radius:12px;padding:16px 18px;margin-bottom:18px;color:#d9e2ff;line-height:1.7;">
              <strong style="color:#ffffff;">Admin note:</strong> ${esc(booking.admin_note || 'No additional notes were added.')}
            </div>
            <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:18px;font-size:14px;line-height:1.8;color:#a8b6d6;">
              <strong style="color:#ffffff;">Shop contact</strong><br>
              ${esc(shop.address)}<br>
              Phone: ${esc(shop.phones.join(' | '))}<br>
              Email: ${esc(shop.emails.join(' | '))}<br>
              Hours: ${esc(shop.hours)}
            </div>
          </div>
        </div>
      </div>`;
  }

  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  /**
   * Wait for CDN script (@emailjs/browser) — defer order can lag behind first user action.
   */
  async function waitForEmailJs(maxMs = 10000) {
    const started = Date.now();
    while (Date.now() - started < maxMs) {
      const ej = typeof emailjs !== 'undefined' ? emailjs : typeof window !== 'undefined' ? window.emailjs : undefined;
      if (ej && typeof ej.send === 'function') {
        return ej;
      }
      await sleep(40);
    }
    throw new Error(
      'EmailJS script did not load (check Network / ad blocker, or that admin.php includes email.min.js).'
    );
  }

  /**
   * Send “vehicle listing approved” via EmailJS. Uses publicKey on each send (avoids init race).
   * Template should map recipient to {{to_email}} or {{email}} / {{user_email}}.
   */
  async function sendVehicleApprovedEmailJS(flatParams) {
    const cfg = window.__VRIDE_EMAILJS__ || {};
    const pk = cfg.publicKey || cfg.public_key;
    const sid = cfg.serviceId || cfg.service_id;
    const tid = cfg.templateId || cfg.template_id_vehicle_approved || cfg.template_id;
    if (!pk || !sid || !tid) {
      throw new Error('EmailJS config incomplete in emailjs_config.php (public key, service, template).');
    }
    if (!flatParams || !String(flatParams.to_email || '').trim()) {
      throw new Error('Owner has no email in the database (check vehicle owner / users.email).');
    }
    const params = {};
    Object.keys(flatParams).forEach((k) => {
      const v = flatParams[k];
      params[k] = v == null ? '' : String(v);
    });
    const ej = await waitForEmailJs();
    await ej.send(sid, tid, params, { publicKey: pk });
    return { ok: true };
  }

  window.VRideEmail = {
    shop,
    money,
    buildInvoiceHtml,
    sendVehicleApprovedEmailJS,
  };
})();
