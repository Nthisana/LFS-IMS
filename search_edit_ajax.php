<?php
// search function found in database.php
include 'db.php'; // your DB connection

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

try {
    // Prepare a LIKE query for multiple columns
    $likeQ = '%' . $q . '%';

    $stmt = $pdo->prepare("
        SELECT 
            Region,
            Branch,
            `Coffin Name`,
            Code,
            Storage,
            Status,
            `Transfer Location`,
            `Previous Location`,
            `Arrival date`,
            `Action Date`
        FROM coffins
        WHERE
            Region LIKE :q OR
            Branch LIKE :q OR
            `Coffin Name` LIKE :q OR
            Code LIKE :q OR
            Storage LIKE :q OR
            Status LIKE :q OR
            `Transfer Location` LIKE :q OR
            `Previous Location` LIKE :q
        ORDER BY `Arrival date` DESC
        LIMIT 100
    ");
    $stmt->execute([':q' => $likeQ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
