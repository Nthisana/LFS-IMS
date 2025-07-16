<?php
session_start();

// Denies direct access to this page (redirects to welcome.php page when accessing this page directly)
if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: welcome.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];
$department = $_SESSION['department'];
$fullName = '';

try {
    $stmt = $conn->prepare("SELECT Name FROM login_details WHERE Username = ? AND Department = ?");
    $stmt->execute([$username, $department]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $fullName = $user['Name'];
    }
    $_SESSION['fullName'] = $fullName;
} catch (Exception $e) {
    $fullName = 'Unknown User';
}

// Fetch all trail records
$trail_records = [];
try {
    $stmt = $conn->query("SELECT id, branch, name, coffin_type, quantity, request_date, action, created_at FROM request_trail ORDER BY created_at ASC");
    $trail_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $trail_records = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Trail</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 15px; 
        }
        table { 
            width: 100%; 
        }
        h2 { 
            margin-bottom: 15px; 
        }
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
        #trailTable { 
            font-size: 12px; 
        }
        .dt-button-collection .dt-button {
            background-color: #330000;
            color: white;
        }
        #trailTable td, #trailTable th {
            text-align: left;
            padding: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" style="display: inline-block; margin-bottom: 15px; padding: 6px 12px; background-color: #550000; color: white; text-decoration: none; border-radius: 4px;"> ← Back </a>
    <h2>Request Trail</h2>
    <table id="trailTable" class="display nowrap">
        <thead>
            <tr>
                <th>No.</th>
                <th>Branch</th>
                <th>Name</th>
                <th>Coffin Type</th>
                <th>Quantity</th>
                <th>Request Date</th>
                <th>Action</th>
                <th>Created at</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trail_records)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No trail records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($trail_records as $record): ?>
                    <tr>
                        <td><?php echo !empty($record['id']) ? htmlspecialchars($record['id']) : '-'; ?></td>
                        <td><?php echo !empty($record['branch']) ? htmlspecialchars($record['branch']) : '-'; ?></td>
                        <td><?php echo !empty($record['name']) ? htmlspecialchars($record['name']) : '-'; ?></td>
                        <td><?php echo !empty($record['coffin_type']) ? htmlspecialchars($record['coffin_type']) : '-'; ?></td>
                        <td><?php echo !empty($record['quantity']) ? htmlspecialchars($record['quantity']) : '-'; ?></td>
                        <td><?php echo !empty($record['request_date']) ? htmlspecialchars($record['request_date']) : '-'; ?></td>
                        <td><?php echo !empty($record['action']) ? htmlspecialchars($record['action']) : '-'; ?></td>
                        <td><?php echo !empty($record['created_at']) ? htmlspecialchars($record['created_at']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

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
                            {
                                extend: 'excelHtml5',
                                text: 'Export to Excel'
                            },
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
                                        hLineWidth: function(i, node) { return 0.5; },
                                        vLineWidth: function(i, node) { return 0.5; },
                                        hLineColor: function(i, node) { return '#aaa'; },
                                        vLineColor: function(i, node) { return '#aaa'; },
                                        paddingLeft: function(i, node) { return 4; },
                                        paddingRight: function(i, node) { return 4; },
                                        fillColor: function (rowIndex, node, columnIndex) {
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
                        text: 'Clear',
                        className: 'dt-button',
                        action: function (e, dt, node, config) {
                            if (confirm('Are you sure you want to clear all trail records? This will delete all data from the table.')) {
                                fetch('clear_trail.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    alert(data.message);
                                    if (data.success) {
                                        location.reload(); // Refresh the page to reflect the cleared table
                                    }
                                })
                                .catch(() => alert('Error clearing trail records.'));
                            }
                        }
                    }
                ],
                search: { smart: false, regex: false },
                language: {
                    searchPlaceholder: "Search trail",
                    search: ""
                },
                columns: [
                    { data: 'id' },
                    { data: 'branch' },
                    { data: 'name' },
                    { data: 'coffin_type' },
                    { data: 'quantity' },
                    { data: 'request_date' },
                    { data: 'action' },
                    { data: 'created_at' }
                ]
            });
        });
    </script>
</body>
</html>