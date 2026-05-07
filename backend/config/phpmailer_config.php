<?php
/**
 * PHPMailer — Gmail SMTP configuration for VRide.
 *
 * Uses a Gmail App Password (not your Google account password).
 * To generate one: https://myaccount.google.com/apppasswords
 *   1. Enable 2-Step Verification on the Google account.
 *   2. Create an App Password → choose "Mail" + "Other (VRide)".
 *   3. Paste the 16-character password below.
 *
 * Env vars override file values when set:
 *   VRIDE_SMTP_HOST, VRIDE_SMTP_PORT, VRIDE_SMTP_USER, VRIDE_SMTP_PASS,
 *   VRIDE_SMTP_FROM_EMAIL, VRIDE_SMTP_FROM_NAME, VRIDE_SMTP_ENABLED
 */
return [
    'enabled'    => filter_var(getenv('VRIDE_SMTP_ENABLED') ?: '1', FILTER_VALIDATE_BOOLEAN),
    'host'       => getenv('VRIDE_SMTP_HOST')       ?: 'smtp.gmail.com',
    'port'       => (int) (getenv('VRIDE_SMTP_PORT') ?: 587),
    'username'   => getenv('VRIDE_SMTP_USER')       ?: 'pranavsinha499@gmail.com',
    'password'   => getenv('VRIDE_SMTP_PASS')       ?: 'tbeoowfgjcsuxnpy',
    'from_email' => getenv('VRIDE_SMTP_FROM_EMAIL') ?: 'pranavsinha499@gmail.com',
    'from_name'  => getenv('VRIDE_SMTP_FROM_NAME')  ?: 'VRide',
];
