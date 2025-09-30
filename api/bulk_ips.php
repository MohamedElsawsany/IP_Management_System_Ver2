<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    // Validate required fields
    $required = ['network_prefix', 'start_ip', 'end_ip', 'subnet_id', 'device_type_id', 'branch_id'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            exit;
        }
    }
    
    // Validate numeric fields
    $start = (int)$input['start_ip'];
    $end = (int)$input['end_ip'];
    $subnet_id = (int)$input['subnet_id'];
    $device_type_id = (int)$input['device_type_id'];
    $branch_id = (int)$input['branch_id'];
    
    // Validate range
    if ($start < 1 || $start > 254 || $end < 1 || $end > 254) {
        http_response_code(400);
        echo json_encode(['error' => 'IP range must be between 1 and 254']);
        exit;
    }
    
    if ($start > $end) {
        http_response_code(400);
        echo json_encode(['error' => 'Start IP must be less than or equal to End IP']);
        exit;
    }
    
    // Validate network prefix (e.g., "192.168.1")
    $network_prefix = trim($input['network_prefix']);
    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}$/', $network_prefix)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid network prefix format. Expected: xxx.xxx.xxx']);
        exit;
    }
    
    $device_name_prefix = $input['device_name_prefix'] ?? 'Device';
    $description = $input['description'] ?? 'Bulk inserted IP';
    $skip_existing = isset($input['skip_existing']) && $input['skip_existing'] === true;
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        
        // Prepare statements
        $checkStmt = $pdo->prepare("SELECT id FROM ips WHERE ip_address = ?");
        $insertStmt = $pdo->prepare("
            INSERT INTO ips (ip_address, subnet_id, device_name, device_type_id, branch_id, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        for ($i = $start; $i <= $end; $i++) {
            $ip_address = $network_prefix . '.' . $i;
            $device_name = $device_name_prefix . '-' . $i;
            
            // Check if IP already exists
            $checkStmt->execute([$ip_address]);
            if ($checkStmt->fetch()) {
                if ($skip_existing) {
                    $skipped++;
                    continue;
                } else {
                    $errors[] = "IP $ip_address already exists";
                    continue;
                }
            }
            
            // Insert the IP
            try {
                $insertStmt->execute([
                    $ip_address,
                    $subnet_id,
                    $device_name,
                    $device_type_id,
                    $branch_id,
                    $description
                ]);
                $inserted++;
            } catch (PDOException $e) {
                $errors[] = "Failed to insert $ip_address: " . $e->getMessage();
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        $response = [
            'success' => true,
            'message' => "Bulk insertion completed",
            'inserted' => $inserted,
            'skipped' => $skipped,
            'total_processed' => ($end - $start + 1),
            'errors' => $errors
        ];
        
        if (count($errors) > 0) {
            $response['warning'] = 'Some IPs could not be inserted';
        }
        
        http_response_code(201);
        echo json_encode($response);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Bulk insert error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Bulk insert error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>