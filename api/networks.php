<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    $branch_id = $_GET['branch_id'] ?? null;
    
    if (!$branch_id || !is_numeric($branch_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Valid branch ID is required']);
        exit;
    }
    
    $branch_id = (int)$branch_id;
    
    // Get networks grouped by subnet for the branch
    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(SUBSTRING_INDEX(i.ip_address, '.', 3), '.0') as network,
            s.id as subnet_id,
            s.prefix,
            s.subnet_mask,
            COUNT(i.id) as ip_count
        FROM ips i
        JOIN subnets s ON i.subnet_id = s.id
        WHERE i.branch_id = ?
        GROUP BY network, s.id, s.prefix, s.subnet_mask
        ORDER BY INET_ATON(CONCAT(SUBSTRING_INDEX(i.ip_address, '.', 3), '.0'))
    ");
    
    $stmt->execute([$branch_id]);
    $networks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($networks);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>