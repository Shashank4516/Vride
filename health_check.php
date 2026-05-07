<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "VRide Health Check\n";
echo "==================\n";

$pdo = getDB();
if (!$pdo) {
    echo "DB: FAIL (connection unavailable)\n";
    exit(1);
}

echo "DB: OK\n";

$tables = ['users', 'vehicles', 'bookings'];
foreach ($tables as $table) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$table]);
    $exists = (int)$stmt->fetchColumn() > 0;
    echo sprintf("Table %-8s: %s\n", $table, $exists ? 'OK' : 'MISSING');
}

$adminStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'");
$adminCount = (int)$adminStmt->fetchColumn();
echo "Admin users: " . $adminCount . "\n";
