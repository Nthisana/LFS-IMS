<?php
session_start();

// Denies direct access to this page (redirects to welcome.php if not logged in)
if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: welcome.php");
    exit();
}

include 'db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];

    // Fetch the request details before deletion
    $stmt_fetch = $conn->prepare("SELECT branch, name, coffin_type, quantity, request_date FROM coffin_requests WHERE id = ?");
    $stmt_fetch->execute([$id]);
    $request = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        // Insert into request_trail with Action = Approved
        $stmt_trail = $conn->prepare("INSERT INTO request_trail (branch, name, coffin_type, quantity, request_date, action) VALUES (?, ?, ?, ?, ?, 'Approved')");
        $stmt_trail->execute([
            $request['branch'],
            $request['name'],
            $request['coffin_type'],
            $request['quantity'],
            $request['request_date']
        ]);

        // Delete from coffin_requests
        $stmt_delete = $conn->prepare("DELETE FROM coffin_requests WHERE id = ?");
        $stmt_delete->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Request marked as done and added to trail.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting request: ' . $e->getMessage()]);
}
?>