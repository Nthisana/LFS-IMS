<?php
session_start();

// Denies direct access to this page (redirects to welcome.php if not logged in)
if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: welcome.php");
    exit();
}

include 'db.php';

try {
    $branch = $_POST['branch'];
    $name = $_POST['name'];
    $coffin_types = $_POST['coffin_type'];
    $quantities = $_POST['quantity'];
    $request_date = date('Y-m-d');

    // Prepare statements for inserting into coffin_requests and request_trail
    $stmt_requests = $conn->prepare("INSERT INTO coffin_requests (branch, name, coffin_type, quantity, request_date) VALUES (?, ?, ?, ?, ?)");
    $stmt_trail = $conn->prepare("INSERT INTO request_trail (branch, name, coffin_type, quantity, request_date, action) VALUES (?, ?, ?, ?, ?, 'Requested')");

    foreach ($coffin_types as $index => $coffin_type) {
        if (!empty($coffin_type) && !empty($quantities[$index]) && $quantities[$index] > 0) {
            // Insert into coffin_requests
            $stmt_requests->execute([$branch, $name, $coffin_type, $quantities[$index], $request_date]);
            // Insert into request_trail with Action = Requested
            $stmt_trail->execute([$branch, $name, $coffin_type, $quantities[$index], $request_date]);
        }
    }

    header("Location: requests.php");
    exit();
} catch (Exception $e) {
    echo "Error saving request: " . $e->getMessage();
}
?>