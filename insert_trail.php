<?php
// Suppress PHP errors from being output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

session_start();
include 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$updatedBy = isset($_SESSION['fullName']) ? $_SESSION['fullName'] : 'Unknown';

// Handle single row from insert_coffins_ajax.php or direct call
if (!isset($_POST['data'])) {
    echo json_encode(['success' => false, 'message' => 'No data provided.']);
    exit;
}

$data = json_decode($_POST['data'], true);
if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data format.']);
    exit;
}

// Validate status
$valid_statuses = ['In-stock', 'Damage', 'Write-off', 'Own coffin'];
if (!in_array($data['status'], $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => "Invalid status: {$data['status']}"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO coffin_trail (
            region, branch, coffin_type, code, storage, status,
            previous_location, arrival_date, action_date,
            action_type, updated_by, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $data['region'],
        $data['branch'],
        $data['coffin_type'],
        $data['code'],
        $data['storage'],
        $data['status'],
        $data['previous_location'],
        $data['arrival_date'],
        $data['action_date'],
        $data['action_type'],
        $data['updated_by']
    ]);

    $response['success'] = true;
    $response['message'] = 'Trail record inserted.';
} catch (PDOException $e) {
    error_log("Trail insert error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>