<?php
/**
 * SendGrid (by Twilio) — server-side email delivery.
 *
 * Recommended for production instead of PHP mail() / client-side EmailJS.
 *
 * Env vars override file values when set:
 * - VRIDE_SENDGRID_API_KEY (required)
 * - VRIDE_SENDGRID_FROM_EMAIL (recommended)
 * - VRIDE_SENDGRID_FROM_NAME (optional)
 */
return [
    'api_key' => getenv('VRIDE_SENDGRID_API_KEY') ?: '',
    'from_email' => getenv('VRIDE_SENDGRID_FROM_EMAIL') ?: '',
    'from_name' => getenv('VRIDE_SENDGRID_FROM_NAME') ?: 'VRide',
    'enabled' => filter_var(getenv('VRIDE_SENDGRID_ENABLED') ?: '1', FILTER_VALIDATE_BOOLEAN),
];

