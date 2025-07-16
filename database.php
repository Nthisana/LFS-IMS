<?php
// Connect to the database
include 'db.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user's full name
$username = $_SESSION['username'];
$name = ''; // Default fallback
$userStmt = $conn->prepare("SELECT Name FROM login_details WHERE Username = ?");
$userStmt->execute([$username]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    $name = $user['Name'];
}

// Function to calculate the number of days between given date and today
function daysBetween($fromDate) {
    if (!$fromDate) return '';
    $from = new DateTime($fromDate);
    $now = new DateTime();
    return $from->diff($now)->days;
}

// Fetch all coffin records
$stmt = $conn->query("SELECT * FROM coffins");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coffins Database</title>
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
            border: none; padding: 5px 10px; 
            margin-right: 5px; 
            color: white; 
            cursor: pointer; 
            border-radius: 4px; 
        }

        .dt-button:hover { 
            background-color: rgb(117, 89, 89); 
        }

        #coffinTable { 
            font-size: 12px; 
        }

        #coffinTable button { 
            font-size: 11px; 
            padding: 3px 6px; 
        }

        .editing { 
            background-color: #ffffcc !important; 
        }

        .dt-button-collection .dt-button {
            background-color: #330000;
            color: white;
        }
    </style>
</head>
<body>
<a href="dashboard.php" style="display: inline-block; margin-bottom: 15px; padding: 6px 12px; background-color: #550000; color: white; text-decoration: none; border-radius: 4px;"> ← Back </a>
<h2>Coffins Database</h2>
<table id="coffinTable" class="display nowrap">
    <thead>
        <tr>
            <th>No.</th>
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
            <th class="action-col">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $counter = 1; foreach ($rows as $row): ?>
        <tr data-id="<?= $row['id'] ?>">
            <td><?= $counter++ ?></td>
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
            <td class="action-col">
                <button class="edit-btn">Edit</button>
                <button class="delete-btn">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
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
let editedRow = null;

$(document).on('click', '.edit-btn', function () {
    if (editedRow !== null) {
        editedRow.find('td').not(':last').attr('contenteditable', false).removeClass('editing');
    }
    editedRow = $(this).closest('tr');
    editedRow.find('td').not(':last, :nth-child(1), :nth-child(11), :nth-child(13)').attr('contenteditable', true).addClass('editing');
});

$(document).on('click', '.update-btn', function () {
    const row = $(this).closest('tr');
    const cells = row.find('td');

    const data = {
        region: cells.eq(1).text(),
        branch: cells.eq(2).text(),
        coffin_type: cells.eq(3).text(),
        code: cells.eq(4).text(),
        storage: cells.eq(5).text(),
        status: cells.eq(6).text(),
        transfer_location: cells.eq(7).text(),
        previous_location: cells.eq(8).text(),
        arrival_date: cells.eq(9).text(),
        action_date: cells.eq(11).text()
    };

    fetch('update_row.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {
        alert(resp.message); // ✅ Always show success or error message
        if (resp.success) location.reload();
    })
    .catch(err => alert("Update failed."));
});

$(document).on('click', '.delete-btn', function () {
    if (!confirm("Are you sure you wish to delete?")) return;
    const row = $(this).closest('tr');
    const code = row.find('td').eq(4).text();
    $.post('delete_coffin.php', { code: code }, function (res) {
        alert(res);
        location.reload();
    });
});

$(document).ready(function () {
    $('#coffinTable').DataTable({
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
                orientation: 'landscape', // Force landscape mode
                pageSize: 'A4',           // A4 is wider in landscape
                customize: function (doc) {
                doc.defaultStyle.fontSize = 8;        // Smaller font for fitting
                doc.styles.tableHeader.fontSize = 9;  // Header font size

                // Calculate equal widths for all columns (percentage of total width)
                var columnCount = doc.content[1].table.body[0].length;
                var columnWidth = (100 / columnCount) + '%';

                // Set all column widths equally to this percentage string
                doc.content[1].table.widths = new Array(columnCount).fill(columnWidth);

                // Reduce margins to fit more width
                doc.pageMargins = [10, 10, 10, 10];  // left, top, right, bottom

                // Optional: prevent table from breaking mid-row
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
        text: 'Clear All',
        className: 'dt-button',
        action: function () {
            if (confirm('Are you sure you want to delete all coffin records?')) {
                fetch('clear_all.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(res => {
                    alert(res.message);
                    if (res.success) location.reload();
                })
                .catch(() => alert("Failed to clear records."));
            }
        }
    }
],
        search: { smart: false, regex: false },
        language: {
        searchPlaceholder: "Search coffins",
        search: ""
    }
    });
});

</script>

</body>
</html>