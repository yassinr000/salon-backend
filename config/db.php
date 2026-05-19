<?php
// Database credentials — update these to match your setup
define('DB_HOST', 'sql111.infinityfree.com');
define('DB_NAME', 'if0_41940604_salondb');
define('DB_USER', 'if0_41940604');
define('DB_PASS', 'bekkaribi123');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }
    return $pdo;
}
