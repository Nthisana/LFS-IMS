
<?php
session_start();
include 'db.php';

function daysBetween($fromDate) {
    if (!$fromDate) return '';
    $from = new DateTime($fromDate);
    $now = new DateTime();
    return $from->diff($now)->days;
}

// Fetch ONLY coffins with status = In-stock
$stmt = $conn->prepare("SELECT * FROM coffins WHERE LOWER(TRIM(status)) = 'In-stock'");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>In-Stock Coffins</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        body { font-family: Arial; padding: 15px; }
        table { width: 100%; }
        h2 { margin-bottom: 15px; }
        .dt-button {
            background-color: #330000;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 4px;
            cursor: pointer;
        }
        .dt-button:hover {
            background-color: #774444;
        }

        #coffinTable {
            font-size: 12px;
        }
    </style>
</head>
<body>

<a href="index.php" style="display: inline-block; margin-bottom: 15px; padding: 6px 12px; background-color: #550000; color: white; text-decoration: none; border-radius: 4px;">
    ← Back
</a>

<h2>In-Stock Coffins</h2>

<table id="coffinTable" class="display nowrap">
    <thead>
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
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr data-id="<?= $row['id'] ?>">
            <td><?= htmlspecialchars($row['region']) ?></td>
            <td><?= htmlspecialchars($row['branch']) ?></td>
            <td><?= htmlspecialchars($row['coffin_type']) ?></td>
            <td class="code-cell"><?= htmlspecialchars($row['code']) ?></td>
            <td><?= htmlspecialchars($row['storage']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['transfer_location']) ?></td>
            <td><?= htmlspecialchars($row['previous_location']) ?></td>
            <td><?= htmlspecialchars($row['arrival_date']) ?></td>
            <td><?= daysBetween($row['arrival_date']) ?></td>
            <td><?= htmlspecialchars($row['action_date']) ?></td>
            <td><?= daysBetween($row['action_date']) ?></td>
            <td>
                <button class="edit-btn">Edit</button>
                <button class="delete-btn">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    $('#coffinTable').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: ['excelHtml5', 'pdfHtml5', 'print']
    });
});
</script>

</body>
</html>
