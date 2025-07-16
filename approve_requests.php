<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: welcome.php");
    exit();
}

include 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'No action performed.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];
    $branch = $input['branch'] ?? '';

    if (!empty($ids) && $branch && $branch !== 'select') {
        try {
            $conn->beginTransaction(); // Start transaction for consistency

            // Fetch details of selected requests
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT id, branch, name, coffin_type, quantity, request_date FROM coffin_requests WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $selectedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Insert into request_trail with action 'Approved'
            $insertStmt = $conn->prepare("INSERT INTO request_trail (branch, name, coffin_type, quantity, request_date, action, created_at) VALUES (?, ?, ?, ?, ?, 'Approved', NOW())");
            foreach ($selectedRequests as $request) {
                $insertStmt->execute([
                    $request['branch'],
                    $request['name'],
                    $request['coffin_type'],
                    $request['quantity'],
                    $request['request_date']
                ]);
            }

            // Optionally delete from coffin_requests (uncomment if desired)
            /*
            $deleteStmt = $conn->prepare("DELETE FROM coffin_requests WHERE id IN ($placeholders)");
            $deleteStmt->execute($ids);
            */

            $conn->commit(); // Commit transaction
            $response = [
                'success' => true,
                'message' => 'Selected coffins approved and logged to trail successfully.'
            ];
        } catch (Exception $e) {
            $conn->rollBack(); // Roll back on error
            $response['message'] = 'Error approving requests: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid input or branch not selected.';
    }
}

echo json_encode($response);
?>