<?php 
// clear all button in database page (database.php)
include 'db.php';

try {
    $conn->exec("DELETE FROM coffins");
    echo json_encode(['success' => true, 'message' => 'All records deleted.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
