<?php
require_once '../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM bookings ORDER BY date ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("INSERT INTO bookings (patient, service, date, price, statut) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['patient'], $data['service'], $data['date'], $data['price'], $data['statut']]);
    $data['id'] = $db->lastInsertId();
    echo json_encode($data);

} elseif ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("UPDATE bookings SET patient=?, service=?, date=?, price=?, statut=? WHERE id=?");
    $stmt->execute([$data['patient'], $data['service'], $data['date'], $data['price'], $data['statut'], $id]);
    echo json_encode(['success' => true]);

} elseif ($method === 'DELETE' && $id) {
    $stmt = $db->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
