<?php // delete_row.php - delete + log into coffin_trail
session_start();
include 'db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['code'])) {
        throw new Exception("Code is required.");
    }

    $code = $data['code'];

    // Get the current user
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in.");
    }

    $stmtUser = $conn->prepare("SELECT Name FROM login_details WHERE Username = ?");
    $stmtUser->execute([$_SESSION['username']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $updatedBy = $user ? $user['Name'] : 'Unknown';

    // Fetch the coffin to be deleted
    $stmt = $conn->prepare("SELECT * FROM coffins WHERE code = ?");
    $stmt->execute([$code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("Coffin not found.");
    }

    // Calculate durations
    $inStoreDuration = (new DateTime($row['arrival_date']))->diff(new DateTime())->days;
    $actionDuration = $row['action_date'] 
        ? (new DateTime($row['action_date']))->diff(new DateTime())->days 
        : 0;

    // Log to coffin_trail
    $trailStmt = $conn->prepare("INSERT INTO coffin_trail (
        region, branch, coffin_type, code, storage, arrival_date, status,
        transfer_location, previous_location, action_date,
        in_store_duration, action_duration, action_performed,
        updated_by, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $trailStmt->execute([
        $row['region'], $row['branch'], $row['coffin_type'], $row['code'], $row['storage'],
        $row['arrival_date'], $row['status'], $row['transfer_location'], $row['previous_location'],
        $row['action_date'], $inStoreDuration, $actionDuration, 'Deleted', $updatedBy
    ]);

    // Now delete the coffin
    $deleteStmt = $conn->prepare("DELETE FROM coffins WHERE code = ?");
    $deleteStmt->execute([$code]);

    $response['success'] = true;
    $response['message'] = 'Coffin deleted and trail recorded.';

} catch (Exception $e) {
    $response['message'] = 'Delete failed: ' . $e->getMessage();
}

echo json_encode($response);
