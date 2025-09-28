<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$branch_id = $_GET['branch_id'] ?? null;
$page = (int)($_GET['page'] ?? 1);
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

if (!$branch_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Branch ID is required']);
    exit;
}

try {
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ips WHERE branch_id = ?");
    $count_stmt->execute([$branch_id]);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Get IPs with pagination
    $stmt = $pdo->prepare("
        SELECT i.ip_address, i.device_name, i.description,
               dt.name as device_type, s.subnet_mask
        FROM ips i
        JOIN device_types dt ON i.device_type_id = dt.id
        JOIN subnets s ON i.subnet_id = s.id
        WHERE i.branch_id = ?
        ORDER BY INET_ATON(i.ip_address)
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$branch_id, $records_per_page, $offset]);
    $ips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ips' => $ips,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'records_per_page' => $records_per_page
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>