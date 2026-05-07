<?php
require_once __DIR__ . '/db.php';

/**
 * Best display name from Google/Firebase user record — profile name first, otherwise from Gmail address.
 */
function google_derived_display_name(array $fu, string $email): string {
    $n = trim((string)($fu['displayName'] ?? ''));
    if ($n !== '') {
        return $n;
    }
    foreach ($fu['providerUserInfo'] ?? [] as $pi) {
        if (!is_array($pi)) {
            continue;
        }
        $n = trim((string)($pi['displayName'] ?? ''));
        if ($n !== '') {
            return $n;
        }
    }
    $local = explode('@', $email)[0] ?? '';
    $local = preg_replace('/\+.*$/', '', $local);
    $local = trim(str_replace(['.', '_'], ' ', $local));
    $local = preg_replace('/\s+/', ' ', $local);
    if ($local === '') {
        return 'Rider';
    }

    return function_exists('mb_convert_case')
        ? mb_convert_case($local, MB_CASE_TITLE, 'UTF-8')
        : ucwords(strtolower($local));
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$idToken = is_array($body) ? ($body['idToken'] ?? '') : '';
$idToken = is_string($idToken) ? trim($idToken) : '';

if ($idToken === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id token']);
    exit;
}

$cfg = require __DIR__ . '/firebase_web.php';
$apiKey = $cfg['apiKey'] ?? '';
if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Server misconfiguration']);
    exit;
}

$url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . rawurlencode($apiKey);
$payload = json_encode(['idToken' => $idToken]);

$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => 15,
        'ignore_errors' => true,
    ],
]);
$raw = @file_get_contents($url, false, $ctx);
if ($raw === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Could not verify token with Firebase']);
    exit;
}

$resp = json_decode($raw, true);
if (empty($resp['users'][0])) {
    http_response_code(401);
    $msg = isset($resp['error']['message']) ? 'Token rejected' : 'Invalid or expired token';
    echo json_encode(['error' => $msg]);
    exit;
}

$fu = $resp['users'][0];
$email = strtolower(trim($fu['email'] ?? ''));
if ($email === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Google account has no email']);
    exit;
}

$name = google_derived_display_name($fu, $email);
$phone = trim((string)($fu['phoneNumber'] ?? ''));

$pdo = getDB();
if (!$pdo) {
    http_response_code(503);
    echo json_encode(['error' => 'Database unavailable']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$existing = $stmt->fetch();

if (!$existing) {
    $hash = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
    $ins = $pdo->prepare(
        'INSERT INTO users (name,email,phone,password,role,city) VALUES (?,?,?,?,?,?)'
    );
    $ins->execute([
        $name,
        $email,
        $phone !== '' ? $phone : '',
        $hash,
        'user',
        '',
    ]);
    $uid = (int)$pdo->lastInsertId();
    $role = 'user';
    flash('Welcome to VRide, ' . $name . '! You\'re signed in with ' . $email . '.', 'success');
} else {
    $uid = (int)$existing['id'];
    $role = $existing['role'] ?? 'user';
    if ($name !== '') {
        $upd = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
        $upd->execute([$name, $uid]);
    }
}

$_SESSION['user_id'] = $uid;
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = $role;
$_SESSION['auth_via'] = 'google';

/* Logged-in "home": main app feed with personalized welcome */
$redirect = $role === 'admin' ? 'admin.php' : 'index.php';
echo json_encode(['ok' => true, 'redirect' => $redirect]);
