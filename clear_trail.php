<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: welcome.php");
    exit();
}

include 'db.php';

try {
    // Truncate the request_trail table to remove all rows
    $conn->exec("TRUNCATE TABLE request_trail");
    echo json_encode(['success' => true, 'message' => 'All trail records have been cleared successfully.']);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error truncating request_trail: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error clearing trail records: ' . $e->getMessage()]);
}
?>