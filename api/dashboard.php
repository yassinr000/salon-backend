<?php
require_once '../config/db.php';
require_once '../config/cors.php';

$db    = getDB();
$today = date('Y-m-d');

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE date = ?");
$stmt->execute([$today]);
$todayCount = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COALESCE(SUM(price), 0) FROM bookings WHERE date = ? AND statut = 'confirme'");
$stmt->execute([$today]);
$todayRevenue = $stmt->fetchColumn();

$stmt = $db->query("SELECT statut, COUNT(*) as count FROM bookings GROUP BY statut");
$byStatus = [];
foreach ($stmt->fetchAll() as $row) {
    $byStatus[$row['statut']] = (int) $row['count'];
}

$total = array_sum($byStatus);

echo json_encode([
    'today_count'   => (int)   $todayCount,
    'today_revenue' => (float) $todayRevenue,
    'confirme'      => $byStatus['confirme']    ?? 0,
    'en-attente'    => $byStatus['en-attente']  ?? 0,
    'annule'        => $byStatus['annule']      ?? 0,
    'total'         => $total,
]);
