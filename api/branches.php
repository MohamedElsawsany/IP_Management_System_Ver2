<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $stmt = $pdo->prepare("
        SELECT b.id, b.name, COUNT(i.id) as ip_count 
        FROM branches b 
        LEFT JOIN ips i ON b.id = i.branch_id 
        GROUP BY b.id, b.name 
        ORDER BY b.name
    ");
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($branches);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>