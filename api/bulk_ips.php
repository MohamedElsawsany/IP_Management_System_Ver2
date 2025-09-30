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

// Increase execution time and memory for large batches
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    // Validate required fields
    $required = ['start_ip', 'end_ip', 'subnet_id', 'device_type_id', 'branch_id'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            exit;
        }
    }
    
    $start_ip = trim($input['start_ip']);
    $end_ip = trim($input['end_ip']);
    $subnet_id = (int)$input['subnet_id'];
    $device_type_id = (int)$input['device_type_id'];
    $branch_id = (int)$input['branch_id'];
    $device_name_prefix = $input['device_name_prefix'] ?? 'Device';
    $description = $input['description'] ?? 'Bulk inserted IP';
    $skip_existing = isset($input['skip_existing']) && $input['skip_existing'] === true;
    $batch_size = isset($input['batch_size']) ? (int)$input['batch_size'] : 1000;
    
    // Validate IP addresses
    if (!filter_var($start_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid start IP address']);
        exit;
    }
    
    if (!filter_var($end_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid end IP address']);
        exit;
    }
    
    // Convert IPs to long integers for range calculation
    $start_long = ip2long($start_ip);
    $end_long = ip2long($end_ip);
    
    if ($start_long === false || $end_long === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid IP address format']);
        exit;
    }
    
    if ($start_long > $end_long) {
        http_response_code(400);
        echo json_encode(['error' => 'Start IP must be less than or equal to End IP']);
        exit;
    }
    
    $total_ips = $end_long - $start_long + 1;
    
    // Safety limit check (can be adjusted)
    if ($total_ips > 2000000) {
        http_response_code(400);
        echo json_encode(['error' => "Range too large. Maximum 2,000,000 IPs per batch. Total requested: " . number_format($total_ips)]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        
        // Prepare statements
        $checkStmt = $pdo->prepare("SELECT ip_address FROM ips WHERE ip_address = ? LIMIT 1");
        
        // Process IPs in batches
        $current_long = $start_long;
        
        while ($current_long <= $end_long) {
            $batch_values = [];
            $batch_params = [];
            $batch_count = 0;
            
            // Build batch
            for ($i = 0; $i < $batch_size && $current_long <= $end_long; $i++, $current_long++) {
                $ip_address = long2ip($current_long);
                
                // Skip if exists and skip_existing is true
                if ($skip_existing) {
                    $checkStmt->execute([$ip_address]);
                    if ($checkStmt->fetch()) {
                        $skipped++;
                        continue;
                    }
                }
                
                // Generate device name - use IP suffix or counter
                $ip_suffix = str_replace('.', '-', $ip_address);
                $device_name = $device_name_prefix . '-' . $ip_suffix;
                
                // Add to batch
                $batch_values[] = "(?, ?, ?, ?, ?, ?)";
                $batch_params[] = $ip_address;
                $batch_params[] = $subnet_id;
                $batch_params[] = $device_name;
                $batch_params[] = $device_type_id;
                $batch_params[] = $branch_id;
                $batch_params[] = $description;
                
                $batch_count++;
            }
            
            // Insert batch if we have records
            if ($batch_count > 0) {
                try {
                    $sql = "INSERT INTO ips (ip_address, subnet_id, device_name, device_type_id, branch_id, description) 
                            VALUES " . implode(', ', $batch_values);
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($batch_params);
                    $inserted += $batch_count;
                    
                } catch (PDOException $e) {
                    // If batch insert fails, try individual inserts
                    if ($e->getCode() == 23000) { // Duplicate entry
                        for ($j = 0; $j < $batch_count; $j++) {
                            $offset = $j * 6;
                            try {
                                $singleStmt = $pdo->prepare(
                                    "INSERT INTO ips (ip_address, subnet_id, device_name, device_type_id, branch_id, description) 
                                     VALUES (?, ?, ?, ?, ?, ?)"
                                );
                                $singleStmt->execute(array_slice($batch_params, $offset, 6));
                                $inserted++;
                            } catch (PDOException $innerE) {
                                if ($skip_existing && $innerE->getCode() == 23000) {
                                    $skipped++;
                                } else {
                                    $errors[] = "Failed to insert " . $batch_params[$offset] . ": " . $innerE->getMessage();
                                }
                            }
                        }
                    } else {
                        throw $e;
                    }
                }
            }
            
            // Prevent memory overflow for very large ranges
            if ($inserted % 10000 == 0 && $inserted > 0) {
                // Commit and start new transaction every 10k inserts
                $pdo->commit();
                $pdo->beginTransaction();
            }
        }
        
        // Commit final transaction
        $pdo->commit();
        
        $response = [
            'success' => true,
            'message' => "Bulk insertion completed",
            'inserted' => $inserted,
            'skipped' => $skipped,
            'total_processed' => $total_ips,
            'start_ip' => $start_ip,
            'end_ip' => $end_ip,
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