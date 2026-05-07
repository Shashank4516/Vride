(function(){
  const SERVICE_ID = 'YOUR_EMAILJS_SERVICE_ID';
  const TEMPLATE_ID = 'YOUR_EMAILJS_TEMPLATE_ID';

  function q(sel){return document.querySelector(sel);} 
  function el(tag, props){ const e = document.createElement(tag); for(const k in props) e[k]=props[k]; return e; }

  function showBanner(msg){
    const existing = document.querySelector('.success-banner');
    if(existing) existing.remove();
    const b = el('div', { className: 'success-banner' });
    b.innerHTML = '<i class="fa-solid fa-circle-check"></i>' + '<div style="margin-left:.5rem;">' + msg + '</div>';
    const formCard = q('.form-card');
    if (formCard) formCard.prepend(b);
    window.scrollTo({top: (formCard?.offsetTop||0)-80, behavior: 'smooth'});
  }

  function showError(msg){
    alert(msg || 'Failed to send message.');
  }

  document.addEventListener('DOMContentLoaded', function(){
    const form = document.querySelector('form[action][method="POST"]') || document.querySelector('form');
    if (!form) return;
    form.id = form.id || 'contactForm';

    form.addEventListener('submit', function(e){
      // Let server fallback work if EmailJS not configured.
      if (!window.emailjs || !emailjs.send) return; // allow normal POST

      e.preventDefault();
      const fd = new FormData(form);
      const payload = Object.fromEntries(fd.entries());

      const templateParams = {
        from_name: payload.name || '',
        from_email: payload.email || '',
        phone: payload.phone || '',
        enquiry_type: payload.type || '',
        message: payload.message || '',
        shop_name: 'VRide',
        shop_address: 'VRide HQ, Punjab, Jalandhar 144411, India',
        shop_phones: '+91 98765 43210 | +91 80000 12345',
        shop_emails: 'hello@vride.in | support@vride.in',
      };

      const submitBtn = form.querySelector('[type=submit]');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.lbl = submitBtn.innerHTML; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending'; }

      emailjs.send(SERVICE_ID, TEMPLATE_ID, templateParams)
      .then(function(){
        if (submitBtn) { submitBtn.disabled = false; if (submitBtn.dataset.lbl) submitBtn.innerHTML = submitBtn.dataset.lbl; }
        showBanner("Message sent! We'll reply within 2 hours.");
        form.reset();
      }, function(err){
        if (submitBtn) { submitBtn.disabled = false; if (submitBtn.dataset.lbl) submitBtn.innerHTML = submitBtn.dataset.lbl; }
        console.error('EmailJS error', err);
        showError('Could not send message via EmailJS. The form will be submitted to the server as fallback.');
        form.submit();
      });
    });
  });
})();
