<?php
/**
 * JSON API for admin panel — snapshot polling + AJAX mutations (no full page reload).
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/admin_lib.php';

header('Content-Type: application/json; charset=utf-8');
$jsonFlags = (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0)
    | JSON_UNESCAPED_UNICODE;

if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'snapshot') {
    $tab = preg_replace('/[^a-z_]/', '', $_GET['tab'] ?? 'dashboard') ?: 'dashboard';
    echo json_encode(['ok' => true] + admin_build_snapshot($pdo, $tab), $jsonFlags);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $post = json_decode($raw ?: '[]', true);
        $post = is_array($post) ? $post : [];
    }

    $result = admin_run_action($pdo, $post);
    $tabBack = preg_replace('/[^a-z_]/', '', $result['tab'] ?? 'dashboard') ?: 'dashboard';
    $snapshot = admin_build_snapshot($pdo, $tabBack);

    $payload = [
        'ok' => $result['ok'],
        'message' => $result['message'],
        'messageType' => $result['type'],
    ] + $snapshot;
    if (!empty($result['emailjs_vehicle_approved']) && is_array($result['emailjs_vehicle_approved'])) {
        $payload['emailjs_vehicle_approved'] = $result['emailjs_vehicle_approved'];
    }
    echo json_encode($payload, $jsonFlags);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
