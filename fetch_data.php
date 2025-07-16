<?php // sends information to dashboard.php for dashboard upload
include 'db.php';

header('Content-Type: application/json');

$stmt = $conn->query("SELECT * FROM coffins");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows);
