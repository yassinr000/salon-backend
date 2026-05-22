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
    $stmt = $db->query("SELECT * FROM services ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO services (nom, prix, employe, duree) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['nom'], $data['prix'], $data['employe'], $data['duree']]);
    $data['id'] = $db->lastInsertId();
    echo json_encode($data);

} elseif ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("UPDATE services SET nom=?, prix=?, employe=?, duree=? WHERE id=?");
    $stmt->execute([$data['nom'], $data['prix'], $data['employe'], $data['duree'], $id]);
    echo json_encode(['success' => true]);

} elseif ($method === 'DELETE' && $id) {
    $stmt = $db->prepare("DELETE FROM services WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
