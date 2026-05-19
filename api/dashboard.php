<?php
require_once '../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$db = getDB();
$today = date('Y-m-d');

// Today's bookings count
$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE date = ?");
$stmt->execute([$today]);
$todayCount = $stmt->fetchColumn();

// Today's revenue (confirmed bookings today)
$stmt = $db->prepare("SELECT COALESCE(SUM(price), 0) FROM bookings WHERE date = ? AND statut = 'confirme'");
$stmt->execute([$today]);
$todayRevenue = $stmt->fetchColumn();

// Booking counts by status
$stmt = $db->query("SELECT statut, COUNT(*) as count FROM bookings GROUP BY statut");
$byStatus = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $byStatus[$row['statut']] = (int) $row['count'];
}

$total = array_sum($byStatus);

echo json_encode([
    'today_count'   => (int) $todayCount,
    'today_revenue' => (float) $todayRevenue,
    'confirme'      => $byStatus['confirme'] ?? 0,
    'en-attente'    => $byStatus['en-attente'] ?? 0,
    'annule'        => $byStatus['annule'] ?? 0,
    'total'         => $total,
]);
