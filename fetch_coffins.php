<?php 
session_start();
include 'db.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';

try {
    if ($search) {
        $stmt = $conn->prepare("SELECT * FROM coffins WHERE coffin_type LIKE ? OR branch LIKE ? OR storage LIKE ? OR status LIKE ? ORDER BY id DESC");
        $like = "%$search%";
        $stmt->execute([$like, $like, $like, $like]);
    } else {
        $stmt = $conn->query("SELECT * FROM coffins ORDER BY id DESC");
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (Exception $e) {
    echo json_encode([]);
}
