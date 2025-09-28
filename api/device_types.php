<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    $stmt = $pdo->prepare("SELECT id, name FROM device_types ORDER BY name");
    $stmt->execute();
    $device_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($device_types);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>