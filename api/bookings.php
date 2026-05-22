<?php
require_once '../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    $stmt = $db->query("
        SELECT b.id, c.patient, b.client_id, s.nom AS service, b.service_id,
               b.`date`, b.price, b.statut, b.created_at
        FROM bookings b
        LEFT JOIN clients c ON b.client_id = c.id
        LEFT JOIN services s ON b.service_id = s.id
        ORDER BY b.`date` ASC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    try {
        $stmt = $db->prepare("INSERT INTO bookings (client_id, service_id, `date`, price, statut) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['client_id'], $data['service_id'], $data['date'], $data['price'], $data['statut']]);
        $newId = $db->lastInsertId();
        $stmt = $db->prepare("
            SELECT b.id, c.patient, b.client_id, s.nom AS service, b.service_id,
                   b.`date`, b.price, b.statut
            FROM bookings b
            LEFT JOIN clients c ON b.client_id = c.id
            LEFT JOIN services s ON b.service_id = s.id
            WHERE b.id = ?
        ");
        $stmt->execute([$newId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    }

} elseif ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    try {
        $stmt = $db->prepare("UPDATE bookings SET client_id=?, service_id=?, `date`=?, price=?, statut=? WHERE id=?");
        $stmt->execute([$data['client_id'], $data['service_id'], $data['date'], $data['price'], $data['statut'], $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    }

} elseif ($method === 'DELETE' && $id) {
    $stmt = $db->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
