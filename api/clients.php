<?php
require_once '../config/db.php';
require_once '../config/cors.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM clients ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    try {
        $stmt = $db->prepare("INSERT INTO clients (patient, numero, ville, note, statut) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['patient'], $data['numero'], $data['ville'], $data['note'] ?? '', $data['statut']]);
        $data['id'] = $db->lastInsertId();
        echo json_encode($data);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    }

} elseif ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    try {
        $stmt = $db->prepare("UPDATE clients SET patient=?, numero=?, ville=?, note=?, statut=? WHERE id=?");
        $stmt->execute([$data['patient'], $data['numero'], $data['ville'], $data['note'] ?? '', $data['statut'], $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    }

} elseif ($method === 'DELETE' && $id) {
    try {
        $stmt = $db->prepare("DELETE FROM clients WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
