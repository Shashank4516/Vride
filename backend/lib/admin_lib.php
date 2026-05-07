<?php
/**
 * Shared admin data + mutations for admin.php (classic POST) and admin_api.php (JSON).
 */

function vride_shop_contact_details(): array
{
        return [
                'name' => 'VRide',
                'address' => 'VRide HQ, Punjab, Jalandhar 144411, India',
                'phones' => ['+91 98765 43210', '+91 80000 12345'],
                'emails' => ['hello@vride.in', 'support@vride.in'],
                'hours' => 'Mon-Sat: 8 am - 9 pm | Emergencies: 24 / 7',
        ];
}

function vride_normalize_addons($addons): array
{
        if (is_string($addons)) {
                $decoded = json_decode($addons, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $addons = $decoded;
                } elseif ($addons !== '') {
                        $addons = [$addons];
                } else {
                        $addons = [];
                }
        }

        if (!is_array($addons)) {
                return [];
        }

        return array_values(array_filter(array_map(static function ($item) {
                return trim((string) $item);
        }, $addons), static function ($item) {
                return $item !== '';
        }));
}

function vride_format_money($amount): string
{
        return '₹' . number_format((float) $amount, 2);
}

function vride_send_html_mail(string $to, string $subject, string $html, string $text = ''): bool
{
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
        }

        $support = vride_shop_contact_details();
        $from = $support['emails'][1] ?? 'no-reply@vride.in';
        $safeSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8') : $subject;

        $body = $html !== '' ? $html : nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
        $textBody = $text !== '' ? $text : strip_tags((string) $html);

        // 1. Try SendGrid when configured.
        if (vride_sendgrid_ready()) {
                $sentSg = vride_send_via_sendgrid($to, $subject, $body, $textBody, [
                        'from_email_fallback' => $from,
                        'from_name_fallback' => 'VRide',
                ]);
                if ($sentSg) {
                        return true;
                }
        }

        // 2. Try PHPMailer / Gmail SMTP when configured.
        if (vride_phpmailer_ready()) {
                $sentPm = vride_send_via_phpmailer($to, $subject, $body, $textBody);
                if ($sentPm) {
                        return true;
                }
        }

        // 3. Last resort: PHP mail() — unreliable on localhost/XAMPP.
        if (!function_exists('mail')) {
                return false;
        }

        $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: VRide <' . $from . '>',
                'Reply-To: ' . ($support['emails'][1] ?? $from),
                'X-Mailer: PHP/' . phpversion(),
        ];

        $sent = @mail($to, $safeSubject, $body, implode("\r\n", $headers));

        if (!$sent) {
                error_log('VRide mail send failed for ' . $to . ' with subject ' . $subject);
        }

        return $sent;
}

function vride_phpmailer_config(): array
{
        static $cache = null;
        if ($cache !== null) {
                return $cache;
        }
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'phpmailer_config.php';
        $cache = (is_file($path) && is_readable($path)) ? require $path : [];
        if (!is_array($cache)) {
                $cache = [];
        }
        return $cache;
}

function vride_phpmailer_ready(): bool
{
        $c = vride_phpmailer_config();
        if (!filter_var((string) ($c['enabled'] ?? '1'), FILTER_VALIDATE_BOOLEAN)) {
                return false;
        }
        return ($c['username'] ?? '') !== '' && ($c['password'] ?? '') !== '';
}

/**
 * Send an email via PHPMailer (Gmail SMTP / any SMTP).
 * Returns true on success.
 */
function vride_send_via_phpmailer(string $to, string $subject, string $html, string $text): bool
{
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
        }

        $autoload = dirname(__DIR__) . '/vendor/autoload.php';
        if (!is_file($autoload)) {
                error_log('VRide PHPMailer: vendor/autoload.php not found. Run composer install.');
                return false;
        }
        require_once $autoload;

        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                error_log('VRide PHPMailer: PHPMailer class not found.');
                return false;
        }

        $c = vride_phpmailer_config();

        try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = (string) ($c['host'] ?? 'smtp.gmail.com');
                $mail->SMTPAuth   = true;
                $mail->Username   = (string) ($c['username'] ?? '');
                $mail->Password   = (string) ($c['password'] ?? '');
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int) ($c['port'] ?? 587);

                $fromEmail = (string) ($c['from_email'] ?? $mail->Username);
                $fromName  = (string) ($c['from_name']  ?? 'VRide');
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($to);
                $mail->addReplyTo($fromEmail, $fromName);

                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = $subject;
                $mail->Body    = $html !== '' ? $html : nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
                $mail->AltBody = $text !== '' ? $text : strip_tags($html);

                $mail->send();
                return true;
        } catch (\Exception $e) {
                error_log('VRide PHPMailer error: ' . $e->getMessage());
                return false;
        }
}

function vride_sendgrid_config(): array
{
        static $cache = null;
        if ($cache !== null) {
                return $cache;
        }
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sendgrid_config.php';
        $cache = (is_file($path) && is_readable($path)) ? require $path : [];
        if (!is_array($cache)) {
                $cache = [];
        }
        return $cache;
}

function vride_sendgrid_ready(): bool
{
        $c = vride_sendgrid_config();
        if (!filter_var((string) ($c['enabled'] ?? '1'), FILTER_VALIDATE_BOOLEAN)) {
                return false;
        }
        return ($c['api_key'] ?? '') !== '';
}

/**
 * Send a transactional email via SendGrid Web API v3.
 * Returns true on 202 Accepted.
 */
function vride_send_via_sendgrid(string $to, string $subject, string $html, string $text, array $fallbacks = []): bool
{
        $c = vride_sendgrid_config();
        $apiKey = (string) ($c['api_key'] ?? '');
        if ($apiKey === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
        }

        $fromEmail = trim((string) ($c['from_email'] ?? ''));
        if ($fromEmail === '') {
                $fromEmail = trim((string) ($fallbacks['from_email_fallback'] ?? 'no-reply@vride.in'));
        }
        $fromName = trim((string) ($c['from_name'] ?? 'VRide'));
        if ($fromName === '') {
                $fromName = trim((string) ($fallbacks['from_name_fallback'] ?? 'VRide'));
        }

        $payload = [
                'personalizations' => [
                        [
                                'to' => [['email' => $to]],
                                'subject' => $subject,
                        ],
                ],
                'from' => [
                        'email' => $fromEmail,
                        'name' => $fromName,
                ],
                'content' => array_values(array_filter([
                        $html !== '' ? ['type' => 'text/html', 'value' => $html] : null,
                        $text !== '' ? ['type' => 'text/plain', 'value' => $text] : null,
                ])),
        ];

        $json = json_encode($payload);
        if ($json === false) {
                return false;
        }

        $url = 'https://api.sendgrid.com/v3/mail/send';
        $headers = [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
        ];

        $status = 0;
        $responseBody = '';

        if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 12);
                $responseBody = (string) curl_exec($ch);
                $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (curl_errno($ch)) {
                        error_log('VRide SendGrid curl error: ' . curl_error($ch));
                }
                curl_close($ch);
        } else {
                $ctx = stream_context_create([
                        'http' => [
                                'method' => 'POST',
                                'header' => implode("\r\n", $headers),
                                'content' => $json,
                                'timeout' => 12,
                        ],
                ]);
                $responseBody = (string) @file_get_contents($url, false, $ctx);
                $respHeaders = null;
                if (function_exists('http_get_last_response_headers')) {
                        $respHeaders = http_get_last_response_headers();
                } elseif (isset($http_response_header)) {
                        $respHeaders = $http_response_header;
                }

                if (is_array($respHeaders)) {
                        foreach ($respHeaders as $h) {
                                if (preg_match('/^HTTP\\/(?:1\\.1|2)\\s+(\\d+)/', $h, $m)) {
                                        $status = (int) $m[1];
                                        break;
                                }
                        }
                }
        }

        if ($status === 202) {
                return true;
        }

        if ($status > 0) {
                error_log('VRide SendGrid send failed (HTTP ' . $status . '): ' . $responseBody);
        } else {
                error_log('VRide SendGrid send failed (no HTTP status).');
        }
        return false;
}

function vride_build_booking_invoice_email(array $booking, array $user, array $vehicle): array
{
        $support = vride_shop_contact_details();
        $addons = vride_normalize_addons($booking['addons'] ?? []);
        $invoiceNo = 'VR-' . str_pad((string) (int) ($booking['id'] ?? 0), 6, '0', STR_PAD_LEFT);
        $pickup = (string) ($booking['pickup_date'] ?? '');
        $return = (string) ($booking['return_date'] ?? '');
        $days = max(1, (int) ($booking['days'] ?? 1));
        $amount = (float) ($booking['final_amount'] ?? $booking['amount'] ?? 0);
        $dailyRate = (float) ($vehicle['final_price'] ?? $vehicle['price_per_day'] ?? 0);
        $paymentMethod = ucfirst(str_replace('_', ' ', (string) ($booking['payment_method'] ?? 'cash')));
        $adminNote = trim((string) ($booking['admin_note'] ?? ''));

        $addonRows = '';
        if ($addons) {
                foreach ($addons as $addon) {
                        $addonRows .= '<tr><td style="padding:8px 0;color:#d9e2ff;">' . htmlspecialchars($addon, ENT_QUOTES, 'UTF-8') . '</td><td style="padding:8px 0;text-align:right;color:#d9e2ff;">Included</td></tr>';
                }
        } else {
                $addonRows = '<tr><td style="padding:8px 0;color:#7f8ba5;">No add-ons selected</td><td style="padding:8px 0;text-align:right;color:#7f8ba5;">-</td></tr>';
        }

        $phoneList = implode(' | ', array_map('htmlspecialchars', $support['phones']));
        $emailList = implode(' | ', array_map('htmlspecialchars', $support['emails']));
        $subject = 'VRide booking approved - ' . ($vehicle['title'] ?? 'Your ride');

        $html = '
<!doctype html>
<html>
<body style="margin:0;padding:0;background:#08101f;font-family:Arial,Helvetica,sans-serif;color:#ecf2ff;">
    <div style="max-width:720px;margin:0 auto;padding:24px;">
        <div style="background:#0b1220;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;">
            <div style="padding:24px 28px;background:linear-gradient(135deg,#0f1a30,#0b1220);border-bottom:1px solid rgba(255,255,255,.08);">
                <div style="font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#6ea8ff;font-weight:700;">Booking Approved</div>
                <div style="font-size:28px;font-weight:800;margin-top:8px;">VRide Invoice</div>
                <div style="margin-top:10px;color:#8fa0c2;font-size:14px;line-height:1.6;">Your booking has been approved by the admin team. Keep this email for your records and pickup reference.</div>
            </div>
            <div style="padding:28px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <td style="padding-bottom:20px;vertical-align:top;width:50%;">
                            <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6ea8ff;font-weight:700;margin-bottom:8px;">Customer</div>
                            <div style="font-size:18px;font-weight:700;color:#ffffff;">' . htmlspecialchars((string) ($user['name'] ?? 'Customer'), ENT_QUOTES, 'UTF-8') . '</div>
                            <div style="margin-top:6px;color:#a8b6d6;font-size:14px;line-height:1.6;">' . htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br>' . htmlspecialchars((string) ($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') . '</div>
                        </td>
                        <td style="padding-bottom:20px;vertical-align:top;width:50%;text-align:right;">
                            <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6ea8ff;font-weight:700;margin-bottom:8px;">Invoice No.</div>
                            <div style="font-size:18px;font-weight:700;color:#ffffff;">' . htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8') . '</div>
                            <div style="margin-top:6px;color:#a8b6d6;font-size:14px;line-height:1.6;">Approved booking reference<br>#' . htmlspecialchars((string) ($booking['id'] ?? ''), ENT_QUOTES, 'UTF-8') . '</div>
                        </td>
                    </tr>
                </table>

                <div style="background:#0f1a30;border:1px solid rgba(110,168,255,.18);border-radius:14px;padding:18px 20px;margin:10px 0 22px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.8;">
                        <tr><td style="color:#8fa0c2;">Vehicle</td><td style="text-align:right;color:#ffffff;font-weight:700;">' . htmlspecialchars((string) ($vehicle['title'] ?? 'Vehicle'), ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="color:#8fa0c2;">Pickup Date</td><td style="text-align:right;color:#ffffff;font-weight:700;">' . htmlspecialchars($pickup, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="color:#8fa0c2;">Return Date</td><td style="text-align:right;color:#ffffff;font-weight:700;">' . htmlspecialchars($return, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="color:#8fa0c2;">Duration</td><td style="text-align:right;color:#ffffff;font-weight:700;">' . htmlspecialchars((string) $days, ENT_QUOTES, 'UTF-8') . ' day(s)</td></tr>
                        <tr><td style="color:#8fa0c2;">Daily Rate</td><td style="text-align:right;color:#ffffff;font-weight:700;">' . htmlspecialchars(vride_format_money($dailyRate), ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="color:#8fa0c2;">Payment Method</td><td style="text-align:right;color:#ffffff;font-weight:700;">' . htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') . '</td></tr>
                    </table>
                </div>

                <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6ea8ff;font-weight:700;margin-bottom:10px;">Invoice Breakdown</div>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.6;margin-bottom:18px;">
                    <tr><td style="padding:8px 0;color:#d9e2ff;">Rental total</td><td style="padding:8px 0;text-align:right;color:#d9e2ff;">' . htmlspecialchars(vride_format_money($amount), ENT_QUOTES, 'UTF-8') . '</td></tr>
                    ' . $addonRows . '
                    <tr><td style="padding:10px 0 0;color:#ffffff;font-size:16px;font-weight:800;border-top:1px solid rgba(255,255,255,.08);">Amount due</td><td style="padding:10px 0 0;text-align:right;color:#6ea8ff;font-size:16px;font-weight:800;border-top:1px solid rgba(255,255,255,.08);">' . htmlspecialchars(vride_format_money($amount), ENT_QUOTES, 'UTF-8') . '</td></tr>
                </table>

                <div style="background:rgba(110,168,255,.08);border:1px solid rgba(110,168,255,.18);border-radius:12px;padding:16px 18px;margin-bottom:18px;color:#d9e2ff;line-height:1.7;">
                    <strong style="color:#ffffff;">Admin note:</strong> ' . htmlspecialchars($adminNote !== '' ? $adminNote : 'No additional notes were added.', ENT_QUOTES, 'UTF-8') . '
                </div>

                <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:18px;font-size:14px;line-height:1.8;color:#a8b6d6;">
                    <strong style="color:#ffffff;">Shop contact</strong><br>
                    ' . htmlspecialchars($support['address'], ENT_QUOTES, 'UTF-8') . '<br>
                    Phone: ' . $phoneList . '<br>
                    Email: ' . $emailList . '<br>
                    Hours: ' . htmlspecialchars($support['hours'], ENT_QUOTES, 'UTF-8') . '
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

        $text = implode("\n", [
                'VRide booking approved',
                'Invoice: ' . $invoiceNo,
                'Customer: ' . ($user['name'] ?? 'Customer'),
                'Email: ' . ($user['email'] ?? ''),
                'Phone: ' . ($user['phone'] ?? ''),
                'Vehicle: ' . ($vehicle['title'] ?? 'Vehicle'),
                'Pickup Date: ' . $pickup,
                'Return Date: ' . $return,
                'Duration: ' . $days . ' day(s)',
                'Daily Rate: ' . vride_format_money($dailyRate),
                'Rental Total: ' . vride_format_money($amount),
                'Admin Note: ' . ($adminNote !== '' ? $adminNote : 'No additional notes were added.'),
                'Shop Contact: ' . $support['address'],
                'Phones: ' . implode(', ', $support['phones']),
                'Emails: ' . implode(', ', $support['emails']),
        ]);

        return ['subject' => $subject, 'html' => $html, 'text' => $text];
}

function vride_emailjs_config(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'emailjs_config.php';
    $cache = (is_file($path) && is_readable($path)) ? require $path : [];
    if (!is_array($cache)) {
        $cache = [];
    }

    return $cache;
}

function vride_emailjs_ready(): bool
{
    $c = vride_emailjs_config();

    return ($c['public_key'] ?? '') !== ''
        && ($c['service_id'] ?? '') !== ''
        && ($c['template_id_vehicle_approved'] ?? '') !== '';
}

/**
 * Notify vehicle owner listing was approved — HTML for PHP mail() and flat params for EmailJS templates.
 *
 * @return array{subject:string,html:string,text:string,flat:array<string,string>}
 */
function vride_build_vehicle_approved_owner_email(array $vehicleOwnerRow, float $finalPriceDaily, array $support): array
{
    $ownerName = trim((string) ($vehicleOwnerRow['owner_name'] ?? 'Owner'));
    $title = trim((string) ($vehicleOwnerRow['title'] ?? 'Your vehicle'));
    $vehicleId = (int) ($vehicleOwnerRow['vehicle_id'] ?? 0);
    $model = trim((string) ($vehicleOwnerRow['model'] ?? ''));
    $typeRaw = (string) ($vehicleOwnerRow['type'] ?? '2wheeler');
    $typeLabel = $typeRaw === '4wheeler' ? '4-Wheeler (Car / SUV)' : '2-Wheeler (Bike / Scooter)';
    $city = trim((string) ($vehicleOwnerRow['city'] ?? ''));
    $category = trim((string) ($vehicleOwnerRow['category'] ?? ''));
    $priceStr = vride_format_money($finalPriceDaily);
    $phoneLine = implode(' | ', $support['phones'] ?? []);
    $emailLine = implode(' | ', $support['emails'] ?? []);
    $subject = 'VRide — Your vehicle listing is approved: ' . $title;

    $html = '
<!doctype html>
<html>
<body style="margin:0;padding:0;background:#08101f;font-family:Arial,Helvetica,sans-serif;color:#ecf2ff;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#0b1220;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;">
            <div style="padding:22px 26px;background:linear-gradient(135deg,#0f1a30,#0b1220);border-bottom:1px solid rgba(255,255,255,.08);">
                <div style="font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#6ea8ff;font-weight:700;">Listing approved</div>
                <div style="font-size:24px;font-weight:800;margin-top:8px;color:#fff;">Your vehicle is live on VRide</div>
                <div style="margin-top:10px;color:#8fa0c2;font-size:14px;line-height:1.6;">Hi ' . htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') . ', an admin has approved your listing. Renters can now book <strong style="color:#fff;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</strong> at the confirmed daily rate below.</div>
            </div>
            <div style="padding:24px 26px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.85;">
                    <tr><td style="color:#8fa0c2;">Vehicle</td><td style="text-align:right;color:#fff;font-weight:700;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</td></tr>
                    <tr><td style="color:#8fa0c2;">Type</td><td style="text-align:right;color:#fff;font-weight:700;">' . htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') . '</td></tr>
                    ' . ($category !== '' ? '<tr><td style="color:#8fa0c2;">Category</td><td style="text-align:right;color:#fff;font-weight:700;">' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '</td></tr>' : '') . '
                    ' . ($city !== '' ? '<tr><td style="color:#8fa0c2;">City</td><td style="text-align:right;color:#fff;font-weight:700;">' . htmlspecialchars($city, ENT_QUOTES, 'UTF-8') . '</td></tr>' : '') . '
                    <tr><td style="color:#8fa0c2;">Confirmed rate</td><td style="text-align:right;color:#6ea8ff;font-weight:800;font-size:16px;">' . htmlspecialchars($priceStr, ENT_QUOTES, 'UTF-8') . ' / day</td></tr>
                </table>
                <div style="margin-top:22px;padding-top:18px;border-top:1px solid rgba(255,255,255,.08);font-size:14px;line-height:1.85;color:#a8b6d6;">
                    <strong style="color:#fff;display:block;margin-bottom:6px;">Visit us — ' . htmlspecialchars($support['name'] ?? 'VRide', ENT_QUOTES, 'UTF-8') . '</strong>
                    Address: <span style="color:#ecf2ff;">' . htmlspecialchars($support['address'] ?? '', ENT_QUOTES, 'UTF-8') . '</span><br>
                    Phone: <span style="color:#ecf2ff;">' . htmlspecialchars($phoneLine, ENT_QUOTES, 'UTF-8') . '</span><br>
                    Email: <span style="color:#ecf2ff;">' . htmlspecialchars($emailLine, ENT_QUOTES, 'UTF-8') . '</span><br>
                    Hours: <span style="color:#ecf2ff;">' . htmlspecialchars($support['hours'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>
                </div>
                <p style="margin:18px 0 0;color:#7f8ba5;font-size:12px;line-height:1.6;">Questions? Reply to this email or call the shop number above.</p>
            </div>
        </div>
    </div>
</body>
</html>';

    $plainLines = [
        'Hi ' . $ownerName . ',',
        '',
        'Your VRide listing is approved:',
        '- Vehicle: ' . $title,
        '- Type: ' . $typeLabel,
        $category !== '' ? '- Category: ' . $category : null,
        $city !== '' ? '- City: ' . $city : null,
        '- Confirmed rate: ' . $priceStr . ' / day',
        '',
        ($support['name'] ?? 'VRide') . ' — shop contact',
        'Address: ' . ($support['address'] ?? ''),
        'Phone: ' . $phoneLine,
        'Email: ' . $emailLine,
        'Hours: ' . ($support['hours'] ?? ''),
    ];
    $text = implode("\n", array_filter($plainLines, static fn ($l) => $l !== null));

    $toEmail = trim((string) ($vehicleOwnerRow['owner_email'] ?? ''));
    $replyTo = trim((string) (($support['emails'][0] ?? '') ?: ($support['emails'][1] ?? '')));
    if ($replyTo === '' && $emailLine !== '') {
        $parts = preg_split('/\s*\|\s*/', $emailLine);
        $replyTo = trim((string) ($parts[0] ?? ''));
    }

    /* Shown in EmailJS subject: "Vehicle Approval Confirmation - {{vehicleNumber}}" */
    $vehicleNumber = $title . ($vehicleId > 0 ? ' — Ref. VR-' . $vehicleId : '');
    if ($model !== '') {
        $vehicleNumber = $title . ' · ' . $model . ($vehicleId > 0 ? ' (VR-' . $vehicleId . ')' : '');
    }

    try {
        $tz = new DateTimeZone('Asia/Kolkata');
        $now = new DateTime('now', $tz);
        $time = $now->format('d M Y, h:i A') . ' IST';
    } catch (Throwable $e) {
        $time = date('d M Y, H:i');
    }

    /* Body copy for templates using {{name}}, {{time}}, {{message}}, {{adminEmail}} */
    $templateMessage = 'Your vehicle listing "' . $title . "\" has been approved on VRide.\r\n\r\n"
        . 'Confirmed daily rate: ' . $priceStr . "\r\n"
        . 'Type: ' . $typeLabel
        . ($city !== '' ? "\r\nCity: " . $city : '') . "\r\n\r\n"
        . 'Shop: ' . ($support['name'] ?? 'VRide') . "\r\n"
        . 'Address: ' . ($support['address'] ?? '') . "\r\n"
        . 'Phone: ' . $phoneLine . "\r\n"
        . 'Email: ' . $emailLine . "\r\n"
        . 'Hours: ' . ($support['hours'] ?? '');

    $flat = [
        'to_email' => $toEmail,
        'email' => $toEmail,
        'user_email' => $toEmail,
        'from_name' => 'VRide',
        'reply_to' => $replyTo,
        'subject' => $subject,
        'owner_name' => $ownerName,
        'vehicle_title' => $title,
        'vehicle_type' => $typeLabel,
        'vehicle_city' => $city,
        'vehicle_category' => $category,
        'final_price' => $priceStr,
        'shop_name' => (string) ($support['name'] ?? 'VRide'),
        'shop_address' => (string) ($support['address'] ?? ''),
        'shop_phones' => $phoneLine,
        'shop_emails' => $emailLine,
        'shop_hours' => (string) ($support['hours'] ?? ''),
        'plain_message' => $text,
        'message' => $templateMessage,
        /* EmailJS dashboard template (Content / Subject) */
        'vehicleNumber' => $vehicleNumber,
        'name' => $ownerName,
        'time' => $time,
        'adminEmail' => $replyTo,
    ];

    return ['subject' => $subject, 'html' => $html, 'text' => $text, 'flat' => $flat];
}

function admin_fetch_data(?PDO $pdo): array
{
    $stats = [
        'total_vehicles' => 0,
        'pending_v' => 0,
        'total_bookings' => 0,
        'pending_b' => 0,
        'users' => 0,
    ];
    $pendingVehicles = [];
    $pendingBookings = [];
    $allVehicles = [];
    $allBookings = [];

    if ($pdo) {
        $stats['total_vehicles'] = (int) $pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
        $stats['pending_v'] = (int) $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='pending'")->fetchColumn();
        $stats['total_bookings'] = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
        $stats['pending_b'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
        $stats['users'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

        $pendingVehicles = $pdo->query(
            "SELECT v.*, u.name AS owner_name, u.phone AS owner_phone FROM vehicles v
             LEFT JOIN users u ON v.owner_id = u.id WHERE v.status='pending' ORDER BY v.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $pendingBookings = $pdo->query(
            "SELECT b.*, u.name AS user_name, u.phone AS user_phone, u.email AS user_email,
                    v.title AS v_title, v.type AS v_type, v.final_price
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN vehicles v ON b.vehicle_id = v.id
             WHERE b.status='pending' ORDER BY b.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $allVehicles = $pdo->query(
            "SELECT v.*, u.name AS owner_name FROM vehicles v
             LEFT JOIN users u ON v.owner_id = u.id ORDER BY v.created_at DESC LIMIT 30"
        )->fetchAll(PDO::FETCH_ASSOC);

        $allBookings = $pdo->query(
            "SELECT b.*, u.name AS user_name, v.title AS v_title FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN vehicles v ON b.vehicle_id = v.id
             ORDER BY b.created_at DESC LIMIT 30"
        )->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stats = [
            'total_vehicles' => 12,
            'pending_v' => 3,
            'total_bookings' => 28,
            'pending_b' => 5,
            'users' => 45,
        ];
    }

    return [
        'stats' => $stats,
        'pendingVehicles' => $pendingVehicles,
        'pendingBookings' => $pendingBookings,
        'allVehicles' => $allVehicles,
        'allBookings' => $allBookings,
    ];
}

/**
 * Run one admin action. Returns: ok, message, type (success|error), tab
 */
function admin_run_action(?PDO $pdo, array $post): array
{
    $tab = preg_replace('/[^a-z_]/', '', $post['tab'] ?? 'dashboard') ?: 'dashboard';
    $action = $post['action'] ?? '';

    if (!$pdo) {
        return [
            'ok' => false,
            'message' => 'Database connection unavailable.',
            'type' => 'error',
            'tab' => $tab,
        ];
    }

    if ($action === 'approve_vehicle') {
        $id = (int) ($post['id'] ?? 0);
        $price = (float) ($post['final_price'] ?? 0);
        if ($id < 1 || $price < 1) {
            return ['ok' => false, 'message' => 'Invalid vehicle or price.', 'type' => 'error', 'tab' => $tab];
        }
        $pdo->prepare("UPDATE vehicles SET status='approved', final_price=? WHERE id=?")->execute([$price, $id]);

        $detailStmt = $pdo->prepare(
            'SELECT v.id AS vehicle_id, v.title, v.model, v.type, v.city, v.category,
                    u.email AS owner_email, u.name AS owner_name
             FROM vehicles v
             LEFT JOIN users u ON v.owner_id = u.id
             WHERE v.id = ? LIMIT 1'
        );
        $detailStmt->execute([$id]);
        $ownerRow = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $successMsg = 'Vehicle approved with price ₹' . $price . '/day!';
        $emailjsVehicleApproved = null;

        if (($ownerRow['owner_email'] ?? '') !== '' && filter_var($ownerRow['owner_email'], FILTER_VALIDATE_EMAIL)) {
            $support = vride_shop_contact_details();
            $pack = vride_build_vehicle_approved_owner_email($ownerRow, $price, $support);

            $ejReady = vride_emailjs_ready();
            $ejCfg = vride_emailjs_config();
            if (!isset($ejCfg['server_mail_backup'])) {
                $alsoPhp = true;
            } else {
                $alsoPhp = filter_var($ejCfg['server_mail_backup'], FILTER_VALIDATE_BOOLEAN);
            }

            $sentPhp = false;
            if ($alsoPhp || ! $ejReady) {
                $sentPhp = vride_send_html_mail(
                    (string) $ownerRow['owner_email'],
                    $pack['subject'],
                    $pack['html'],
                    $pack['text']
                );
            }

            if ($ejReady) {
                $emailjsVehicleApproved = $pack['flat'];
                if ($sentPhp) {
                    $successMsg .= ' Owner notified (server mail + EmailJS).';
                } else {
                    $successMsg .= ' EmailJS will notify the owner (server mail failed or disabled — check mail / backup setting).';
                }
            } else {
                $successMsg .= $sentPhp
                    ? ' Owner notified by email.'
                    : ' Owner email could not be sent (configure sendmail/SMTP or EmailJS in emailjs_config.php).';
            }
        }

        return [
            'ok' => true,
            'message' => $successMsg,
            'type' => 'success',
            'tab' => $tab,
            'emailjs_vehicle_approved' => $emailjsVehicleApproved,
        ];
    }

    if ($action === 'reject_vehicle') {
        $id = (int) ($post['id'] ?? 0);
        if ($id < 1) {
            return ['ok' => false, 'message' => 'Invalid vehicle.', 'type' => 'error', 'tab' => $tab];
        }
        $pdo->prepare("UPDATE vehicles SET status='rejected' WHERE id=?")->execute([$id]);

        return ['ok' => true, 'message' => 'Vehicle rejected.', 'type' => 'error', 'tab' => $tab];
    }

    if ($action === 'approve_booking') {
        $id = (int) ($post['id'] ?? 0);
        $final = (float) ($post['final_amount'] ?? 0);
        $note = trim($post['note'] ?? '');
        if ($id < 1) {
            return ['ok' => false, 'message' => 'Invalid booking.', 'type' => 'error', 'tab' => $tab];
        }

        $bookingStmt = $pdo->prepare('SELECT id, vehicle_id, pickup_date, return_date, status FROM bookings WHERE id=? LIMIT 1');
        $bookingStmt->execute([$id]);
        $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return ['ok' => false, 'message' => 'Booking not found.', 'type' => 'error', 'tab' => $tab];
        }

        $overlapStmt = $pdo->prepare(
            "SELECT id FROM bookings WHERE vehicle_id=? AND status='approved' AND id<>?
             AND pickup_date IS NOT NULL AND return_date IS NOT NULL
             AND pickup_date <= return_date
             AND pickup_date <= ? AND return_date >= ? LIMIT 1"
        );
        $overlapStmt->execute([$booking['vehicle_id'], $booking['id'], $booking['return_date'], $booking['pickup_date']]);
        $overlapId = (int) ($overlapStmt->fetchColumn() ?: 0);

        if ($overlapId > 0) {
            return [
                'ok' => false,
                'message' => "Cannot approve: overlapping approved booking #{$overlapId}.",
                'type' => 'error',
                'tab' => $tab,
            ];
        }

        $pdo->prepare('UPDATE bookings SET status=?, final_amount=?, admin_note=? WHERE id=?')
            ->execute(['approved', $final, $note, $id]);

        $rejectConflicts = $pdo->prepare(
            "UPDATE bookings SET status='rejected',
             admin_note=CONCAT(COALESCE(admin_note,''), CASE WHEN COALESCE(admin_note,'')='' THEN '' ELSE ' | ' END,
             'Auto-rejected: vehicle unavailable for selected dates.')
             WHERE vehicle_id=? AND status='pending' AND id<>?
             AND pickup_date IS NOT NULL AND return_date IS NOT NULL
             AND pickup_date <= return_date
             AND pickup_date <= ? AND return_date >= ?"
        );
        $rejectConflicts->execute([$booking['vehicle_id'], $booking['id'], $booking['return_date'], $booking['pickup_date']]);
        $rejectedCount = $rejectConflicts->rowCount();
        $extra = $rejectedCount > 0 ? " {$rejectedCount} overlapping pending request(s) auto-rejected." : '';

        $detailsStmt = $pdo->prepare(
            "SELECT b.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                    v.title AS v_title, v.type AS v_type, v.category AS v_category, v.city AS v_city,
                    v.price_per_day, v.final_price
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN vehicles v ON b.vehicle_id = v.id
             WHERE b.id=? LIMIT 1"
        );
        $detailsStmt->execute([$id]);
        $bookingDetails = $detailsStmt->fetch(PDO::FETCH_ASSOC) ?: $booking;

        $emailData = vride_build_booking_invoice_email($bookingDetails, $bookingDetails, $bookingDetails);
        $mailSent = false;
        if (!empty($bookingDetails['user_email'])) {
            $mailSent = vride_send_html_mail(
                (string) $bookingDetails['user_email'],
                $emailData['subject'],
                $emailData['html'],
                $emailData['text']
            );
        }

        if (!$mailSent) {
            $extra .= ' Approval email could not be sent automatically.';
        }

        return [
            'ok' => true,
            'message' => "Booking approved! Final amount: ₹{$final}.{$extra}",
            'type' => 'success',
            'tab' => $tab,
        ];
    }

    if ($action === 'reject_booking') {
        $id = (int) ($post['id'] ?? 0);
        if ($id < 1) {
            return ['ok' => false, 'message' => 'Invalid booking.', 'type' => 'error', 'tab' => $tab];
        }
        $pdo->prepare("UPDATE bookings SET status='rejected' WHERE id=?")->execute([$id]);

        return ['ok' => true, 'message' => 'Booking rejected.', 'type' => 'error', 'tab' => $tab];
    }

    if ($action === 'complete_booking') {
        $id = (int) ($post['id'] ?? 0);
        if ($id < 1) {
            return ['ok' => false, 'message' => 'Invalid booking.', 'type' => 'error', 'tab' => $tab];
        }
        $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$id]);

        return ['ok' => true, 'message' => 'Booking marked completed.', 'type' => 'success', 'tab' => $tab];
    }

    return ['ok' => false, 'message' => 'Unknown action.', 'type' => 'error', 'tab' => $tab];
}

/** Snapshot payload for realtime UI (+ AI hints on pending vehicles) */
function admin_build_snapshot(?PDO $pdo, string $tab): array
{
    $d = admin_fetch_data($pdo);
    foreach ($d['pendingVehicles'] as $k => $v) {
        $d['pendingVehicles'][$k]['ai'] = aiAdminDecision($v);
    }

    return [
        'tab' => $tab,
        'stats' => $d['stats'],
        'pendingVehicles' => $d['pendingVehicles'],
        'pendingBookings' => $d['pendingBookings'],
        'allVehicles' => $d['allVehicles'],
        'allBookings' => $d['allBookings'],
        'server_time' => time(),
        'db' => $pdo !== null,
    ];
}
