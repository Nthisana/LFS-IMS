<?php
session_start();
include 'db.php';

$stmt = $conn->query("SELECT * FROM coffin_trail ORDER BY updated_at ASC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to calculate days between two dates
function daysBetween($fromDate) {
    if (!$fromDate) return '';
    $from = new DateTime($fromDate);
    $now = new DateTime();
    return $from->diff($now)->days;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coffin Trail Log</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 15px; }
        table { width: 100%; font-size: 12px; }
        .dt-button {
            background-color: #330000;
            border: none;
            padding: 5px 10px;
            margin-right: 5px;
            color: white;
            cursor: pointer;
            border-radius: 4px;
        }
        .dt-button:hover {
            background-color: rgb(117, 89, 89);
        }
        .dt-button-collection .dt-button {
            background-color: #330000;
            color: white;
        }
        button.delete-trail {
            padding: 4px 8px;
            background-color: #D3D3D3;
            color: black;
            font-size: 11px;
            border: 1px solid grey;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<a href="dashboard.php" style="display: inline-block; margin-bottom: 15px; padding: 6px 12px; background-color:rgb(117, 78, 0); color: white; text-decoration: none; border-radius: 4px;"> ← Back </a>
<h2>Coffin Trail Log</h2>

<table id="trailTable" class="display nowrap">
    <thead>
        <tr>
            <th>No.</th>
            <th>Region</th>
            <th>Branch</th>
            <th>Coffin Type</th>
            <th>Code</th>
            <th>Storage</th>
            <th>Status</th>
            <th>Transfer Location</th>
            <th>Previous Location</th>
            <th>Arrival Date</th>
            <th>In-Store Duration</th>
            <th>Action Date</th>
            <th>Action Duration</th>
            <th>Action Performed</th>
            <th>Updated By</th>
            <th>Updated At</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($logs as $log): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($log['region']) ?></td>
            <td><?= htmlspecialchars($log['branch']) ?></td>
            <td><?= htmlspecialchars($log['coffin_type']) ?></td>
            <td><?= htmlspecialchars($log['code']) ?></td>
            <td><?= htmlspecialchars($log['storage']) ?></td>
            <td><?= htmlspecialchars($log['status']) ?></td>
            <td><?= htmlspecialchars($log['transfer_location']) ?></td>
            <td><?= htmlspecialchars($log['previous_location']) ?></td>
            <td><?= htmlspecialchars($log['arrival_date']) ?></td>
            <td><?= $log['in_store_duration'] ?></td>
            <td><?= htmlspecialchars($log['action_date']) ?></td>
            <td><?= $log['action_duration'] ?></td>
            <td><?= htmlspecialchars($log['action_performed']) ?></td>
            <td><?= htmlspecialchars($log['updated_by']) ?></td>
            <td><?= htmlspecialchars($log['updated_at']) ?></td>
            <td><button class="delete-trail" data-id="<?= $log['id'] ?>">Delete</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    $('#trailTable').DataTable({
        scrollX: true,
        ordering: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'collection',
                text: 'Download',
                className: 'dt-button',
                buttons: [
                    { extend: 'excelHtml5', text: 'Export to Excel' },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export to PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader.fontSize = 9;
                            var columnCount = doc.content[1].table.body[0].length;
                            var columnWidth = (100 / columnCount) + '%';
                            doc.content[1].table.widths = new Array(columnCount).fill(columnWidth);
                            doc.pageMargins = [10, 10, 10, 10];
                            doc.content[1].layout = {
                                hLineWidth: function() { return 0.5; },
                                vLineWidth: function() { return 0.5; },
                                hLineColor: function() { return '#aaa'; },
                                vLineColor: function() { return '#aaa'; },
                                paddingLeft: function() { return 4; },
                                paddingRight: function() { return 4; },
                                fillColor: function (rowIndex) {
                                    return (rowIndex % 2 === 0) ? null : '#f9f9f9';
                                }
                            };
                        }
                    }
                ]
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'dt-button'
            },
            {
                text: 'Clear All',
                className: 'dt-button',
                action: function () {
                    if (confirm('Are you sure you want to clear the entire trail log?')) {
                        fetch('clear_trail.php', {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(res => {
                            alert(res.message);
                            if (res.success) location.reload();
                        })
                        .catch(() => alert("Failed to clear trail."));
                    }
                }
            }
        ],
        language: {
            searchPlaceholder: "Search trail",
            search: ""
        }
    });
});

$(document).on('click', '.delete-trail', function () {
    if (!confirm('Delete this trail entry?')) return;
    
    const id = $(this).data('id');

    fetch('delete_trail.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    })
    .catch(() => alert("Error deleting trail record."));
});

</script>
</body>
</html>
