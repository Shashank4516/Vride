<?php
/**
 * EmailJS (https://www.emailjs.com/) — used from the admin panel when a vehicle listing is approved.
 *
 * 1. Create a service + email template in the EmailJS dashboard.
 * 2. Template suggested variables (all optional; match your template):
 *    {{to_email}}, {{owner_name}}, {{vehicle_title}}, {{vehicle_type}}, {{vehicle_city}},
 *    {{final_price}}, {{shop_name}}, {{shop_address}}, {{shop_phones}}, {{shop_emails}}, {{shop_hours}},
 *    {{plain_message}}
 * 3. In the template Content tab: set "To Email" to {{to_email}} (not a fixed address) so the vehicle owner receives mail.
 *    Subject example: Vehicle Approval Confirmation - {{vehicleNumber}}
 *    Body placeholders: {{name}}, {{time}}, {{message}}, {{adminEmail}} — plus {{shop_address}}, {{shop_phones}} if you add them in the template.
 *    Reply-To field: use only {{adminEmail}} (no extra "Reply To:" text in the field).
 *
 * server_mail_backup: when true (default), also send HTML mail via PHP mail() when a vehicle is approved,
 * even if EmailJS is configured — improves delivery when EmailJS fails or on localhost. Set false to avoid
 * duplicate emails if both paths succeed on production.
 *
 * Env vars override file values when set:
 * VRIDE_EMAILJS_PUBLIC_KEY, VRIDE_EMAILJS_SERVICE_ID, VRIDE_EMAILJS_TEMPLATE_VEHICLE_APPROVED
 */
return [
    'public_key' => getenv('VRIDE_EMAILJS_PUBLIC_KEY') ?: 'I08M-_Yllx7JgUTso',
    'service_id' => getenv('VRIDE_EMAILJS_SERVICE_ID') ?: 'service_h0wsuos',
    'template_id_vehicle_approved' => getenv('VRIDE_EMAILJS_TEMPLATE_VEHICLE_APPROVED') ?: 'template_4sleo1o',
    'server_mail_backup' => filter_var(getenv('VRIDE_EMAILJS_SERVER_MAIL_BACKUP') ?: '1', FILTER_VALIDATE_BOOLEAN),
];
