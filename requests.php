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

// Fetch all requests
$requests = [];
try {
    $stmt = $conn->query("SELECT id, branch, name, coffin_type, quantity, request_date FROM coffin_requests ORDER BY request_date DESC");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $requests = [];
}

// Define hardcoded branches (add more as needed)
$hardcodedBranches = [
'Maseru',
'Hlotse',
'Lithoteng',
'Maputsoe',
'Mohales Hoek',
'Kolonyama',
'Mantsebo',
'Majane',
'Makhakhe',
'Nyakosoba',
'Tsakholo',
'Pitseng',
'Moeketsane',
'Rampai',
'Marakabei',
'Mphorosane',
'TY',
'Thabana Morena',
'Quthing',
'Mphaki',
'Mt. Moorosi',
'Qachas Nek',
'Thaba Tseka',
'Mafeteng',
'Mokhotlong',
'Bokong',
'Semonkong'
];

// Detect current page
$currentPage = basename($_SERVER['PHP_SELF']);
$isTablePage = in_array($currentPage, ['damages.php', 'transfers.php', 'write-off.php', 'in-stock.php', 'sales.php']);
$isRequestPage = in_array($currentPage, ['add_request.php', 'requests.php']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Coffin Requests</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .sidebar {
            width: 220px;
            background-color: #550000;
            padding-top: 20px;
            height: 100vh;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: white;
            color: black;
        }
        .sidebar a.active {
            background-color: white;
            color: black;
        }
        .sidebar .submenu {
            display: none;
            padding-left: 20px;
        }
        .sidebar .has-sub.active .submenu {
            display: block;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #ccc;
        }
        .user-icon {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 240px);
            overflow-x: auto;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        #branchSelect {
            padding: 6px 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        #approveBtn {
            background-color: #550000;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #approveBtn:hover {
            background-color: #800000;
        }
        #searchInput {
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #550000;
            border-radius: 4px;
            width: 250px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #550000;
            color: white;
        }
        .select-col {
            display: table-cell;
        }
        .approve-checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        #requestTable {
            display: block;
            max-height: 480px; /* Increased to accommodate ~10 rows */
            overflow-y: auto; /* Vertical scrollbar */
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.has-sub > a');
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    parent.classList.toggle('active');
                });
            });

            // Approve functionality
            document.getElementById('approveBtn').addEventListener('click', function() {
                const branch = document.getElementById('branchSelect').value;
                const checkboxes = document.querySelectorAll('.approve-checkbox:checked');
                if (!branch || branch === 'select') {
                    alert('Please select a branch.');
                    return;
                }
                if (checkboxes.length === 0) {
                    alert('Please select at least one coffin to approve.');
                    return;
                }

                const ids = Array.from(checkboxes).map(cb => cb.getAttribute('data-id'));
                fetch('approve_requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids, branch: branch })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        checkboxes.forEach(cb => cb.closest('tr').remove());
                        const rows = document.querySelectorAll('#requestTable tbody tr');
                        if (rows.length === 0) {
                            document.querySelector('#requestTable tbody').innerHTML = '<tr><td colspan="7">No requests found.</td></tr>';
                        }
                    }
                })
                .catch(() => alert('Error approving requests.'));
            });

            // Attach event listeners to delete buttons (if any remain)
            document.querySelectorAll('.done-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to mark this request as done?')) {
                        fetch('delete_request.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                this.closest('tr').remove();
                                const rows = document.querySelectorAll('#requestTable tbody tr');
                                if (rows.length === 0) {
                                    document.querySelector('#requestTable tbody').innerHTML = '<tr><td colspan="7">No requests found.</td></tr>';
                                }
                            }
                        })
                        .catch(() => alert('Error deleting request.'));
                    }
                });
            });

            // Live search functionality
            function liveSearch() {
                const searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
                const rows = document.querySelectorAll('#requestTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
            document.getElementById('searchInput').addEventListener('input', liveSearch);
        });

        function toggleLogoutMenu() {
            const menu = document.getElementById('logoutMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function logout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = 'welcome.php';
            }
        }
    </script>
</head>
<body>
<div class="sidebar">
    <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Coffin Entry</a>

    <div class="has-sub <?= $isTablePage ? 'active' : '' ?>">
        <a href="#">Tables ▾</a>
        <div class="submenu">
            <a href="damages.php" class="<?= $currentPage === 'damages.php' ? 'active' : '' ?>">Damages</a>
            <a href="transfers.php" class="<?= $currentPage === 'transfers.php' ? 'active' : '' ?>">Transfers</a>
            <a href="write-off.php" class="<?= $currentPage === 'write-off.php' ? 'active' : '' ?>">Write-offs</a>
            <a href="in-stock.php" class="<?= $currentPage === 'in-stock.php' ? 'active' : '' ?>">In-stock</a>
            <a href="sales.php" class="<?= $currentPage === 'sales.php' ? 'active' : '' ?>">Sales</a>
        </div>
    </div>

    <div class="has-sub <?= $isRequestPage ? 'active' : '' ?>">
        <a href="#">Requests ▾</a>
        <div class="submenu">
            <a href="add_request.php" class="<?= $currentPage === 'add_request.php' ? 'active' : '' ?>">Add Request</a>
            <a href="requests.php" class="<?= $currentPage === 'requests.php' ? 'active' : '' ?>">Requests</a>
        </div>
    </div>

    <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; border-top: 1px solid #555; padding-top: 10px;">
        <div class="user-info" onclick="toggleLogoutMenu()" style="cursor: pointer;">
            <img src="images/user_icon.png" alt="User Icon" class="user-icon">
            <span><?= htmlspecialchars($fullName) ?></span>
        </div>
        <div id="logoutMenu" style="display: none; margin-top: 5px;">
            <a href="#" onclick="logout()" style="color: black; text-decoration: underline; font-size: 13px;">Logout</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="header-container">
        <h2>Coffin Requests</h2>
        <input type="text" id="searchInput" placeholder="Search requests..." oninput="liveSearch()" autocomplete="off">
    </div>
    <div class="controls">
        <select id="branchSelect">
            <option value="select">-- Select branch --</option>
            <?php foreach ($hardcodedBranches as $branch): ?>
                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
            <?php endforeach; ?>
        </select>
        <button id="approveBtn">Approve</button>
    </div>
    <table id="requestTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Branch</th>
                <th>Name</th>
                <th>Coffin Type</th>
                <th>Quantity</th>
                <th>Date</th>
                <th class="select-col">Select</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="7">No requests found.</td>
                </tr>
            <?php else: ?>
                <?php $counter = 1; foreach ($requests as $request): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($request['branch']) ?></td>
                        <td><?= htmlspecialchars($request['name']) ?></td>
                        <td><?= htmlspecialchars($request['coffin_type']) ?></td>
                        <td><?= htmlspecialchars($request['quantity']) ?></td>
                        <td><?= htmlspecialchars($request['request_date']) ?></td>
                        <td class="select-col">
                            <input type="checkbox" class="approve-checkbox" data-id="<?= htmlspecialchars($request['id']) ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>