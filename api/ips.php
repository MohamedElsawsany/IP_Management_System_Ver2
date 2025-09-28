<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function handleGet($pdo) {
    $branch_id = $_GET['branch_id'] ?? null;
    $page = (int)($_GET['page'] ?? 1);
    $records_per_page = 10;
    $offset = ($page - 1) * $records_per_page;

    if (!$branch_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Branch ID is required']);
        return;
    }

    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ips WHERE branch_id = ?");
    $count_stmt->execute([$branch_id]);
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Get IPs with pagination
    $stmt = $pdo->prepare("
        SELECT i.id, i.ip_address, i.device_name, i.description,
               dt.name as device_type, dt.id as device_type_id,
               s.subnet_mask, s.id as subnet_id
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
}

function handlePost($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required_fields = ['ip_address', 'device_name', 'device_type_id', 'subnet_id', 'branch_id'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            return;
        }
    }

    // Validate IP address format
    if (!filter_var($input['ip_address'], FILTER_VALIDATE_IP)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid IP address format']);
        return;
    }

    // Check if IP already exists
    $check_stmt = $pdo->prepare("SELECT id FROM ips WHERE ip_address = ?");
    $check_stmt->execute([$input['ip_address']]);
    if ($check_stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'IP address already exists']);
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO ips (ip_address, device_name, device_type_id, subnet_id, branch_id, description)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['ip_address'],
        $input['device_name'],
        $input['device_type_id'],
        $input['subnet_id'],
        $input['branch_id'],
        $input['description'] ?? null
    ]);

    $ip_id = $pdo->lastInsertId();
    
    // Return the created IP with related data
    $result_stmt = $pdo->prepare("
        SELECT i.id, i.ip_address, i.device_name, i.description,
               dt.name as device_type, dt.id as device_type_id,
               s.subnet_mask, s.id as subnet_id
        FROM ips i
        JOIN device_types dt ON i.device_type_id = dt.id
        JOIN subnets s ON i.subnet_id = s.id
        WHERE i.id = ?
    ");
    $result_stmt->execute([$ip_id]);
    $created_ip = $result_stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode(['message' => 'IP created successfully', 'ip' => $created_ip]);
}

function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'IP ID is required']);
        return;
    }

    // Check if IP exists
    $check_stmt = $pdo->prepare("SELECT id FROM ips WHERE id = ?");
    $check_stmt->execute([$input['id']]);
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'IP not found']);
        return;
    }

    // Validate IP address format if provided
    if (isset($input['ip_address']) && !filter_var($input['ip_address'], FILTER_VALIDATE_IP)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid IP address format']);
        return;
    }

    // Check if new IP address already exists (excluding current record)
    if (isset($input['ip_address'])) {
        $check_stmt = $pdo->prepare("SELECT id FROM ips WHERE ip_address = ? AND id != ?");
        $check_stmt->execute([$input['ip_address'], $input['id']]);
        if ($check_stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'IP address already exists']);
            return;
        }
    }

    // Build dynamic update query
    $update_fields = [];
    $values = [];
    
    $allowed_fields = ['ip_address', 'device_name', 'device_type_id', 'subnet_id', 'description'];
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $update_fields[] = "$field = ?";
            $values[] = $input[$field];
        }
    }

    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        return;
    }

    $values[] = $input['id'];
    $stmt = $pdo->prepare("UPDATE ips SET " . implode(', ', $update_fields) . " WHERE id = ?");
    $stmt->execute($values);

    // Return updated IP with related data
    $result_stmt = $pdo->prepare("
        SELECT i.id, i.ip_address, i.device_name, i.description,
               dt.name as device_type, dt.id as device_type_id,
               s.subnet_mask, s.id as subnet_id
        FROM ips i
        JOIN device_types dt ON i.device_type_id = dt.id
        JOIN subnets s ON i.subnet_id = s.id
        WHERE i.id = ?
    ");
    $result_stmt->execute([$input['id']]);
    $updated_ip = $result_stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['message' => 'IP updated successfully', 'ip' => $updated_ip]);
}

function handleDelete($pdo) {
    $ip_id = $_GET['id'] ?? null;
    
    if (!$ip_id) {
        http_response_code(400);
        echo json_encode(['error' => 'IP ID is required']);
        return;
    }

    // Check if IP exists
    $check_stmt = $pdo->prepare("SELECT id FROM ips WHERE id = ?");
    $check_stmt->execute([$ip_id]);
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'IP not found']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM ips WHERE id = ?");
    $stmt->execute([$ip_id]);

    echo json_encode(['message' => 'IP deleted successfully']);
}
?>