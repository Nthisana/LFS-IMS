<?php
include 'db.php';

$term = $_GET['term'] ?? '';
$term = "%$term%";

$stmt = $conn->prepare("SELECT * FROM coffins WHERE coffin_type LIKE ? OR code LIKE ? OR branch LIKE ? OR region LIKE ?");
$stmt->execute([$term, $term, $term, $term]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

function daysBetween($fromDate) {
    if (!$fromDate) return '';
    $from = new DateTime($fromDate);
    $now = new DateTime();
    return $from->diff($now)->days;
}

if (count($results) === 0) {
    echo "<p>No results found.</p>";
    exit;
}
?>

<table border="1" cellspacing="0" cellpadding="6" style="width:100%; font-size:13px; border-collapse: collapse;">
    <thead style="background:#eee;">
        <tr>
            <th>Region</th>
            <th>Branch</th>
            <th>Coffin Name</th>
            <th>Code</th>
            <th>Storage</th>
            <th>Status</th>
            <th>Transfer Location</th>
            <th>Previous Location</th>
            <th>Arrival Date</th>
            <th>In-Store Duration</th>
            <th>Action Date</th>
            <th>Action Duration</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['region']) ?></td>
            <td><?= htmlspecialchars($row['branch']) ?></td>
            <td><?= htmlspecialchars($row['coffin_type']) ?></td>
            <td><?= htmlspecialchars($row['code']) ?></td>
            <td><?= htmlspecialchars($row['storage']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['transfer_location']) ?></td>
            <td><?= htmlspecialchars($row['previous_location']) ?></td>
            <td><?= htmlspecialchars($row['arrival_date']) ?></td>
            <td><?= daysBetween($row['arrival_date']) ?></td>
            <td><?= htmlspecialchars($row['action_date']) ?></td>
            <td><?= daysBetween($row['action_date']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
