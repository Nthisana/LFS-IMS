<?php
include 'db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) throw new Exception("Missing ID");

    $stmt = $conn->prepare("DELETE FROM coffin_trail WHERE id = ?");
    $stmt->execute([$data['id']]);

    $response['success'] = true;
    $response['message'] = 'Trail record deleted.';
} catch (Exception $e) {
    $response['message'] = 'Delete failed: ' . $e->getMessage();
}

echo json_encode($response);
