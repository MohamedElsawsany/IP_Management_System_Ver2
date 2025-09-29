<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Get DataTables parameters
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
    $branchId = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;

    if (!$branchId) {
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Branch ID is required'
        ]);
        exit;
    }

    // Column mapping for ordering
    $columns = [
        0 => 'i.ip_address',
        1 => 'i.device_name',
        2 => 'dt.name',
        3 => 's.subnet_mask',
        4 => 'i.description'
    ];

    // Base query
    $baseQuery = "FROM ips i
                  JOIN device_types dt ON i.device_type_id = dt.id
                  JOIN subnets s ON i.subnet_id = s.id
                  WHERE i.branch_id = :branch_id";

    $params = ['branch_id' => $branchId];

    // Add search condition
    if (!empty($search)) {
        $baseQuery .= " AND (
            i.ip_address LIKE :search OR
            i.device_name LIKE :search OR
            i.description LIKE :search OR
            dt.name LIKE :search
        )";
        $params['search'] = '%' . $search . '%';
    }

    // Get total records (without filtering)
    $totalQuery = "SELECT COUNT(*) as total FROM ips i WHERE i.branch_id = :branch_id";
    $totalStmt = $pdo->prepare($totalQuery);
    $totalStmt->execute(['branch_id' => $branchId]);
    $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get filtered records count
    $filteredQuery = "SELECT COUNT(*) as total " . $baseQuery;
    $filteredStmt = $pdo->prepare($filteredQuery);
    $filteredStmt->execute($params);
    $filteredRecords = $filteredStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get actual data
    $orderByColumn = isset($columns[$orderColumn]) ? $columns[$orderColumn] : $columns[0];
    $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
    
    // Special ordering for IP addresses
    if ($orderByColumn === 'i.ip_address') {
        $orderClause = "ORDER BY INET_ATON(i.ip_address) $orderDir";
    } else {
        $orderClause = "ORDER BY $orderByColumn $orderDir";
    }

    $dataQuery = "SELECT i.id, i.ip_address, i.device_name, i.description,
                         dt.name as device_type, dt.id as device_type_id,
                         s.subnet_mask, s.id as subnet_id
                  " . $baseQuery . "
                  $orderClause
                  LIMIT :start, :length";

    $dataStmt = $pdo->prepare($dataQuery);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $dataStmt->bindValue(':' . $key, $value);
    }
    $dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
    $dataStmt->bindValue(':length', $length, PDO::PARAM_INT);
    
    $dataStmt->execute();
    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // Return DataTables response
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => intval($totalRecords),
        'recordsFiltered' => intval($filteredRecords),
        'data' => $data
    ]);

} catch (PDOException $e) {
    error_log("DataTables error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'draw' => isset($draw) ? $draw : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>