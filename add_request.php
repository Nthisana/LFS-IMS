<?php
session_start();

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

// Define coffin types with optgroup structure
$coffinTypes = [
    'Single Options' => [
        'Own Coffin (No Name)', 'LTL', 'Cherry MDF', 'Grain Caramel', 'Mid Brown', 
        'Fume Deal', 'Steel Casket', 'Dutch Economy Cherry', 'Lincoln'
    ],
    'Std Flatlid' => [
        'Std Flatlid Red Wood', 'Std Flatlid Kiaat', 'Std Flatlid Mahogany', 
        'Std Flatlid Red Velvet', 'Std Flatlid Pecan'
    ],
    'Avante' => [
        'Avante Red Wood', 'Avante Kiaat', 'Avante Mahogany', 
        'Avante Red Velvet', 'Avante Pecan', 'Avante White'
    ],
    'Straight Cut' => [
        'Straight Cut Red Wood', 'Straight Cut Kiaat', 'Straight Cut Mahogany', 
        'Straight Cut Red Velvet', 'Straight Cut Pecan'
    ],
    'Coffin 1' => [
        'Coffin 1 Red Wood', 'Coffin 1 Kiaat', 'Coffin 1 Mahogany', 
        'Coffin 1 Red Velvet', 'Coffin 1 White', 'Coffin 1 Pecan'
    ],
    'Coffin 2' => [
        'Coffin 2 Red Wood', 'Coffin 2 Kiaat', 'Coffin 2 Mahogany', 
        'Coffin 2 Red Velvet', 'Coffin 2 White', 'Coffin 2 Pecan'
    ],
    'Coffin 2.6' => [
        'Coffin 2.6 Red Wood', 'Coffin 2.6 Kiaat', 'Coffin 2.6 Mahogany', 
        'Coffin 2.6 Red Velvet', 'Coffin 2.6 White', 'Coffin 2.6 Pecan'
    ],
    'Coffin 3' => [
        'Coffin 3 Red Wood', 'Coffin 3 Kiaat', 'Coffin 3 Mahogany', 
        'Coffin 3 Red Velvet', 'Coffin 3 White', 'Coffin 3 Pecan'
    ],
    'Coffin 4' => [
        'Coffin 4 Red Wood', 'Coffin 4 Kiaat', 'Coffin 4 Mahogany', 
        'Coffin 4 Red Velvet', 'Coffin 4 White', 'Coffin 4 Pecan'
    ],
    'Coffin 5' => [
        'Coffin 5 Red Wood', 'Coffin 5 Kiaat', 'Coffin 5 Mahogany', 
        'Coffin 5 Red Velvet', 'Coffin 5 White', 'Coffin 5 Pecan'
    ],
    'Coffin 6*6' => [
        'Coffin 6*6 Red Wood', 'Coffin 6*6 Kiaat', 'Coffin 6*6 Mahogany', 
        'Coffin 6*6 Red Velvet', 'Coffin 6*6 White', 'Coffin 6*6 Pecan'
    ],
    'Over Size Coffin' => [
        'Over Size Coffin Red Wood', 'Over Size Coffin Kiaat', 'Over Size Coffin Mahogany', 
        'Over Size Coffin Red Velvet', 'Over Size Coffin White', 'Over Size Coffin Pecan'
    ],
    'Casket 1' => [
        'Casket 1 Red Wood', 'Casket 1 Kiaat', 'Casket 1 Mahogany', 
        'Casket 1 Red Velvet', 'Casket 1 White', 'Casket 1 Pecan'
    ],
    'Casket 2' => [
        'Casket 2 Red Wood', 'Casket 2 Kiaat', 'Casket 2 Mahogany', 
        'Casket 2 Red Velvet', 'Casket 2 White', 'Casket 2 Pecan'
    ],
    'Casket 2.6' => [
        'Casket 2.6 Red Wood', 'Casket 2.6 Kiaat', 'Casket 2.6 Mahogany', 
        'Casket 2.6 Red Velvet', 'Casket 2.6 White', 'Casket 2.6 Pecan'
    ],
    'Casket 3' => [
        'Casket 3 Red Wood', 'Casket 3 Kiaat', 'Casket 3 Mahogany', 
        'Casket 3 Red Velvet', 'Casket 3 White', 'Casket 3 Pecan'
    ],
    'Casket 4' => [
        'Casket 4 Red Wood', 'Casket 4 Kiaat', 'Casket 4 Mahogany', 
        'Casket 4 Red Velvet', 'Casket 4 White', 'Casket 4 Pecan'
    ],
    'Casket 5' => [
        'Casket 5 Red Wood', 'Casket 5 Kiaat', 'Casket 5 Mahogany', 
        'Casket 5 Red Velvet', 'Casket 5 White', 'Casket 5 Pecan'
    ],
    'Open Face Coffin' => [
        'Open Face Coffin Red Wood', 'Open Face Coffin Kiaat', 'Open Face Coffin Mahogany', 
        'Open Face Coffin Red Velvet', 'Open Face Coffin Pecan', 'Open Face Coffin White'
    ],
    '2 Tier Coffin' => [
        '2 Tier Coffin Red Wood', '2 Tier Coffin Kiaat', '2 Tier Coffin Mahogany', 
        '2 Tier Coffin Red Velvet', '2 Tier Coffin Pecan', '2 Tier Coffin White'
    ],
    '3 Tier Coffin' => [
        '3 Tier Coffin Red Wood', '3 Tier Coffin Kiaat', '3 Tier Coffin Mahogany', 
        '3 Tier Coffin Red Velvet', '3 Tier Coffin Pecan', '3 Tier Coffin White'
    ],
    'Dutch Cannondale' => [
        'Dutch Cannondale Red Wood', 'Dutch Cannondale Kiaat', 'Dutch Cannondale Mahogany', 
        'Dutch Cannondale Red Velvet', 'Dutch Cannondale Pecan', 'Dutch Cannondale White', 
        'Dutch Cannondale Princeton'
    ],
    'Dutch Cleveland' => [
        'Dutch Cleveland Red Wood', 'Dutch Cleveland Kiaat', 'Dutch Cleveland Mahogany', 
        'Dutch Cleveland Red Velvet', 'Dutch Cleveland Pecan', 'Dutch Cleveland White', 
        'Dutch Cleveland Princeton'
    ],
    'Mini Dome (Kingston Dome)' => [
        'Mini Dome Red Wood', 'Mini Dome Kiaat', 'Mini Dome Mahogany', 
        'Mini Dome Red Velvet', 'Mini Dome White', 'Mini Dome Pecan', 
        'Mini Dome Cherry', 'Mini Dome Princeton'
    ],
    'Superior Dome' => [
        'Superior Dome Red Wood', 'Superior Dome Kiaat', 'Superior Dome Mahogany', 
        'Superior Dome Red Velvet', 'Superior Dome White', 'Superior Dome Pecan', 
        'Superior Dome Cherry', 'Superior Dome Princeton'
    ],
    'Royal Dome' => [
        'Royal Dome Red Wood', 'Royal Dome Kiaat', 'Royal Dome Mahogany', 
        'Royal Dome Red Velvet', 'Royal Dome White', 'Royal Dome Pecan', 
        'Royal Dome Cherry', 'Royal Dome Princeton'
    ],
    'Prince Dome' => [
        'Prince Dome Red Wood', 'Prince Dome Kiaat', 'Prince Dome Mahogany', 
        'Prince Dome Red Velvet', 'Prince Dome White', 'Prince Dome Pecan', 
        'Prince Dome Cherry Matte', 'Prince Dome Cherry Gloss', 'Prince Dome Princeton'
    ],
    'Std Dome' => [
        'Std Dome Red Wood', 'Std Dome Kiaat', 'Std Dome Mahogany', 
        'Std Dome Red Velvet', 'Std Dome White', 'Std Dome Pecan', 
        'Std Dome Cherry Matte', 'Std Dome Cherry Gloss', 'Std Dome Princeton', 
        'Std Dome Hamlock'
    ],
    '4 Corner Dome' => [
        '4 Corner Dome Red Wood', '4 Corner Dome Kiaat', '4 Corner Dome Mahogany', 
        '4 Corner Dome Red Velvet', '4 Corner Dome White', '4 Corner Dome Pecan', 
        '4 Corner Dome Cherry Matte', '4 Corner Dome Cherry Gloss', '4 Corner Dome Princeton', 
        '4 Corner Dome Hamlock'
    ],
    'Quarter View Boston' => [
        'Quarter View Boston Red Wood', 'Quarter View Boston Kiaat', 'Quarter View Boston Mahogany', 
        'Quarter View Boston Red Velvet', 'Quarter View Boston White', 'Quarter View Boston Pecan', 
        'Quarter View Boston Cherry Matte', 'Quarter View Boston Cherry Gloss', 
        'Quarter View Boston Princeton', 'Quarter View Boston Hamlock', 'Quarter View Boston Imbuia'
    ],
    'Raised Half View' => [
        'Raised Half View Red Wood', 'Raised Half View Kiaat', 'Raised Half View Mahogany', 
        'Raised Half View Red Velvet', 'Raised Half View White', 'Raised Half View Pecan', 
        'Raised Half View Cherry Matte', 'Raised Half View Cherry Gloss', 'Raised Half View Princeton', 
        'Raised Half View Hamlock'
    ],
    '2 Tier Casket' => [
        '2 Tier Casket Red Wood', '2 Tier Casket Kiaat', '2 Tier Casket Mahogany', 
        '2 Tier Casket Red Velvet', '2 Tier Casket Pecan', '2 Tier Casket Cherry Matte', 
        '2 Tier Casket Cherry Gloss', '2 Tier Casket White', '2 Tier Casket Princeton'
    ],
    '3 Tier Casket' => [
        '3 Tier Casket Red Wood', '3 Tier Casket Kiaat', '3 Tier Casket Mahogany', 
        '3 Tier Casket Red Velvet', '3 Tier Casket Pecan', '3 Tier Casket Cherry Matte', 
        '3 Tier Casket Cherry Gloss', '3 Tier Casket White', '3 Tier Casket Princeton'
    ],
    '4 Tier Casket' => [
        '4 Tier Casket Red Wood', '4 Tier Casket Kiaat', '4 Tier Casket Mahogany', 
        '4 Tier Casket Red Velvet', '4 Tier Casket Pecan', '4 Tier Casket Cherry Matte', 
        '4 Tier Casket Cherry Gloss', '4 Tier Casket White', '4 Tier Casket Princeton'
    ],
    'Dutch Swing Bar' => [
        'Dutch Swing Bar Red Wood', 'Dutch Swing Bar Kiaat', 'Dutch Swing Bar Mahogany', 
        'Dutch Swing Bar Red Velvet', 'Dutch Swing Bar Pecan', 'Dutch Swing Bar Cherry Matte', 
        'Dutch Swing Bar Cherry Gloss', 'Dutch Swing Bar White'
    ]
];

// Detect current page
$currentPage = basename($_SERVER['PHP_SELF']);
$isTablePage = in_array($currentPage, ['damages.php', 'transfers.php', 'write-off.php', 'in-stock.php', 'sales.php']);
$isRequestPage = in_array($currentPage, ['add_request.php', 'requests.php']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Place Coffin Request</title>
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
        .sidebar a:hover{
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
        label {
            font-weight: bold;
        }
        select, input[type="number"], button {
            padding: 8px;
            margin: 6px 0;
            width: 100%;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background-color: #550000;
            color: white;
        }
        .submit-btn {
            background-color: #550000;
            color: white;
            padding: 6px 14px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        .submit-btn:hover {
            background-color: #800000;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 10px;
            background-color: #ccc;
            padding: 5px 10px;
            text-decoration: none;
            color: black;
            border-radius: 4px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() { // Initializes event listeners when the DOM is fully loaded
            const toggles = document.querySelectorAll('.has-sub > a');
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) { // Adds click event listener to toggle submenu visibility
                    e.preventDefault(); // Prevents default link navigation
                    const parent = this.parentElement; // Gets the parent element of the clicked link
                    parent.classList.toggle('active'); // Toggles the 'active' class to show/hide the submenu
                });
            });
        });

        function toggleLogoutMenu() { // Toggles the visibility of the logout menu
            const menu = document.getElementById('logoutMenu'); // Gets the logout menu element
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block'; // Switches display between block and none
        }

        function logout() { // Handles user logout with confirmation
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
    <h2>Place Coffin Request</h2>
    <form action="save_request.php" method="POST">
        <label for="branch">Select Branch:</label>
        <select name="branch" required>
            <option value="">-- Select Branch --</option>
            <?php
            $branches = [
                "Hlotse", "Lithoteng"
            ];
            foreach ($branches as $branch) {
                echo "<option>$branch</option>";
            }
            ?>
        </select>
        <input type="hidden" name="name" value="<?= htmlspecialchars($fullName) ?>">

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Coffin Type</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 10; $i++): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <select name="coffin_type[]">
                                <option value="">-- Select Coffin --</option>
                                <?php foreach ($coffinTypes as $group => $options): ?>
                                    <optgroup label="<?= htmlspecialchars($group) ?>">
                                        <?php foreach ($options as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="quantity[]" min="1" placeholder="0">
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <button class="submit-btn" type="submit">Submit Request</button>
    </form>
</div>
</body>
</html>