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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bulk Coffin Entry</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .sub {
            background-color: rgb(91, 5, 5);
            color: white;
            margin-top: 10px;
        }
        .sub:hover {
            background-color: #1a0000;
            color: white;
        }
        #searchInput {
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #550000;
            border-radius: 4px;
            width: 250px;
        }
        th {
            background-color: #550000;
            color: white;
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
            padding-left: 20px;
            display: none;
        }
        .sidebar .submenu.active {
            display: block;
        }
        .sidebar .has-sub > a.active + .submenu {
            display: block;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 600px;
        }
        th, td {
            padding: 6px;
            border: 1px solid #ccc;
            text-align: left;
            font-size: 12px;
        }
        th:nth-child(1), td:nth-child(1) { /* No. column */
            width: 50px;
        }
        th:nth-child(2), td:nth-child(2) { /* Coffin Type column */
            width: 40%;
        }
        th:nth-child(3), td:nth-child(3) { /* Status column */
            width: 30%;
        }
        th:nth-child(4), td:nth-child(4) { /* Action Date column */
            width: 25%;
        }
        td input[type="text"],
        td input[type="date"],
        td select {
            width: 100%;
            box-sizing: border-box;
            font-size: 12px;
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 240px);
            overflow-x: auto;
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
        .btn-action {
            background-color: #550000;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            margin-right: 8px;
            cursor: pointer;
        }
        .btn-action:hover {
            background-color: #800000;
        }
        .action-col {
            display: table-cell;
        }
        .edit-btn, .delete-btn {
            background-color: #550000;
            color: white;
            padding: 4px 8px;
            margin-top: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-btn:hover, .delete-btn:hover {
            background-color: #800000;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="index.php">Coffin Entry</a>
    <div class="has-sub">
        <a href="#" onclick="toggleSubmenu(event, this)">Tables ▾</a>
        <div class="submenu">
            <a href="damages.php">Damages</a>
            <a href="transfers.php">Transfers</a>
            <a href="write-off.php">Write-offs</a>
            <a href="in-stock.php">In-stock</a>
            <a href="sales.php">Sales</a>
        </div>
    </div>
    <div class="has-sub">
        <a href="#" onclick="toggleSubmenu(event, this)">Requests ▾</a>
        <div class="submenu">
            <a href="add_request.php">Add Request</a>
            <a href="requests.php">Requests</a>
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
    <div id="insertionForm">
        <div class="header-container">
            <h2>Coffin Insertion</h2>
            <input type="text" id="searchInput" placeholder="Search coffins" oninput="liveSearch()" autocomplete="off">
        </div>

        <form id="coffinForm">
            <label>Branch:</label>
            <select name="branch" id="branch" required>
                <option value="">-- Select Branch --</option>
                <option>Hlotse</option>
                <option>Lithoteng</option>
                <option>Maseru</option>
                <option>Maputsoe</option>
                <option>Mohale's Hoek</option>
                <option>Kolonyama</option>
                <option>Mantsebo</option>
                <option>Majane</option>
                <option>Makhakhe</option>
                <option>Nyakosoba</option>
                <option>Tsakholo</option>
                <option>Pitseng</option>
                <option>Moeketsane</option>
                <option>Rampai</option>
                <option>Marakabei</option>
                <option>Mphorosane</option>
                <option>TY</option>
                <option>Thabana Morena</option>
                <option>Quthing</option>
                <option>Mphaki</option>
                <option>Mt. Moorosi</option>
                <option>Qacha's Nek</option>
                <option>Thaba Tseka</option>
                <option>Mafeteng</option>
                <option>Mokhotlong</option>
                <option>Bokong</option>
                <option>Semonkong</option>
            </select>

            <label>Storage:</label>
            <select name="storage" id="storage" required>
                <option value="">-- Select Storage --</option>
                <option>Show room</option>
                <option>Store room</option>
            </select>

            <label>Arrival Date:</label>
            <input type="date" name="arrival_date">

            <br><br>
            <table id="coffinTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Coffin Type</th>
                        <th>Status</th>
                        <th>Action Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i=0; $i<15; $i++): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <select name="coffin_type[]">
                                <option value="">-- Choose coffin type --</option>
                                <option>Own Coffin (No Name)</option>
                                <option>LTL</option>
                                <option>Cherry MDF</option>
                                <option>Grain Caramel</option>
                                <option>Mid Brown</option>
                                <option>Fume Deal</option>
                                <option>Steel Casket</option>
                                <option>Dutch Economy Cherry</option>
                                <option>Lincoln</option>
                                <optgroup label="Std Flatlid">
                                    <option>Std Flatlid Red Wood</option>
                                    <option>Std Flatlid Kiaat</option>
                                    <option>Std Flatlid Mahogany</option>
                                    <option>Std Flatlid Red Velvet</option>
                                    <option>Std Flatlid Pecan</option>
                                </optgroup>
                                <optgroup label="Avante">
                                    <option>Avante Red Wood</option>
                                    <option>Avante Kiaat</option>
                                    <option>Avante Mahogany</option>
                                    <option>Avante Red Velvet</option>
                                    <option>Avante Pecan</option>
                                    <option>Avante White</option>
                                </optgroup>
                                <optgroup label="Straight Cut">
                                    <option>Straight Cut Red Wood</option>
                                    <option>Straight Cut Kiaat</option>
                                    <option>Straight Cut Mahogany</option>
                                    <option>Straight Cut Red Velvet</option>
                                    <option>Straight Cut Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 1">
                                    <option>Coffin 1 Red Wood</option>
                                    <option>Coffin 1 Kiaat</option>
                                    <option>Coffin 1 Mahogany</option>
                                    <option>Coffin 1 Red Velvet</option>
                                    <option>Coffin 1 White</option>
                                    <option>Coffin 1 Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 2">
                                    <option>Coffin 2 Red Wood</option>
                                    <option>Coffin 2 Kiaat</option>
                                    <option>Coffin 2 Mahogany</option>
                                    <option>Coffin 2 Red Velvet</option>
                                    <option>Coffin 2 White</option>
                                    <option>Coffin 2 Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 2.6">
                                    <option>Coffin 2.6 Red Wood</option>
                                    <option>Coffin 2.6 Kiaat</option>
                                    <option>Coffin 2.6 Mahogany</option>
                                    <option>Coffin 2.6 Red Velvet</option>
                                    <option>Coffin 2.6 White</option>
                                    <option>Coffin 2.6 Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 3">
                                    <option>Coffin 3 Red Wood</option>
                                    <option>Coffin 3 Kiaat</option>
                                    <option>Coffin 3 Mahogany</option>
                                    <option>Coffin 3 Red Velvet</option>
                                    <option>Coffin 3 White</option>
                                    <option>Coffin 3 Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 4">
                                    <option>Coffin 4 Red Wood</option>
                                    <option>Coffin 4 Kiaat</option>
                                    <option>Coffin 4 Mahogany</option>
                                    <option>Coffin 4 Red Velvet</option>
                                    <option>Coffin 4 White</option>
                                    <option>Coffin 4 Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 5">
                                    <option>Coffin 5 Red Wood</option>
                                    <option>Coffin 5 Kiaat</option>
                                    <option>Coffin 5 Mahogany</option>
                                    <option>Coffin 5 Red Velvet</option>
                                    <option>Coffin 5 White</option>
                                    <option>Coffin 5 Pecan</option>
                                </optgroup>
                                <optgroup label="Coffin 6*6">
                                    <option>Coffin 6*6 Red Wood</option>
                                    <option>Coffin 6*6 Kiaat</option>
                                    <option>Coffin 6*6 Mahogany</option>
                                    <option>Coffin 6*6 Red Velvet</option>
                                    <option>Coffin 6*6 White</option>
                                    <option>Coffin 6*6 Pecan</option>
                                </optgroup>
                                <optgroup label="Over Size Coffin">
                                    <option>Over Size Coffin Red Wood</option>
                                    <option>Over Size Coffin Kiaat</option>
                                    <option>Over Size Coffin Mahogany</option>
                                    <option>Over Size Coffin Red Velvet</option>
                                    <option>Over Size Coffin White</option>
                                    <option>Over Size Coffin Pecan</option>
                                </optgroup>
                                <optgroup label="Casket 1">
                                    <option>Casket 1 Red Wood</option>
                                    <option>Casket 1 Kiaat</option>
                                    <option>Casket 1 Mahogany</option>
                                    <option>Casket 1 Red Velvet</option>
                                    <option>Casket 1 White</option>
                                    <option>Casket 1 Pecan</option>
                                </optgroup>
                                <optgroup label="Casket 2">
                                    <option>Casket 2 Red Wood</option>
                                    <option>Casket 2 Kiaat</option>
                                    <option>Casket 2 Mahogany</option>
                                    <option>Casket 2 Red Velvet</option>
                                    <option>Casket 2 White</option>
                                    <option>Casket 2 Pecan</option>
                                </optgroup>
                                <optgroup label="Casket 2.6">
                                    <option>Casket 2.6 Red Wood</option>
                                    <option>Casket 2.6 Kiaat</option>
                                    <option>Casket 2.6 Mahogany</option>
                                    <option>Casket 2.6 Red Velvet</option>
                                    <option>Casket 2.6 White</option>
                                    <option>Casket 2.6 Pecan</option>
                                </optgroup>
                                <optgroup label="Casket 3">
                                    <option>Casket 3 Red Wood</option>
                                    <option>Casket 3 Kiaat</option>
                                    <option>Casket 3 Mahogany</option>
                                    <option>Casket 3 Red Velvet</option>
                                    <option>Casket 3 White</option>
                                    <option>Casket 3 Pecan</option>
                                </optgroup>
                                <optgroup label="Casket 4">
                                    <option>Casket 4 Red Wood</option>
                                    <option>Casket 4 Kiaat</option>
                                    <option>Casket 4 Mahogany</option>
                                    <option>Casket 4 Red Velvet</option>
                                    <option>Casket 4 White</option>
                                    <option>Casket 4 Pecan</option>
                                </optgroup>
                                <optgroup label="Casket 5">
                                    <option>Casket 5 Red Wood</option>
                                    <option>Casket 5 Kiaat</option>
                                    <option>Casket 5 Mahogany</option>
                                    <option>Casket 5 Red Velvet</option>
                                    <option>Casket 5 White</option>
                                    <option>Casket 5 Pecan</option>
                                </optgroup>
                                <optgroup label="Open Face Coffin">
                                    <option>Open Face Coffin Red Wood</option>
                                    <option>Open Face Coffin Kiaat</option>
                                    <option>Open Face Coffin Mahogany</option>
                                    <option>Open Face Coffin Red Velvet</option>
                                    <option>Open Face Coffin Pecan</option>
                                    <option>Open Face Coffin White</option>
                                </optgroup>
                                <optgroup label="2 Tier Coffin">
                                    <option>2 Tier Coffin Red Wood</option>
                                    <option>2 Tier Coffin Kiaat</option>
                                    <option>2 Tier Coffin Mahogany</option>
                                    <option>2 Tier Coffin Red Velvet</option>
                                    <option>2 Tier Coffin Pecan</option>
                                    <option>2 Tier Coffin White</option>
                                </optgroup>
                                <optgroup label="3 Tier Coffin">
                                    <option>3 Tier Coffin Red Wood</option>
                                    <option>3 Tier Coffin Kiaat</option>
                                    <option>3 Tier Coffin Mahogany</option>
                                    <option>3 Tier Coffin Red Velvet</option>
                                    <option>3 Tier Coffin Pecan</option>
                                    <option>3 Tier Coffin White</option>
                                </optgroup>
                                <optgroup label="Dutch Cannondale">
                                    <option>Dutch Cannondale Red Wood</option>
                                    <option>Dutch Cannondale Kiaat</option>
                                    <option>Dutch Cannondale Mahogany</option>
                                    <option>Dutch Cannondale Red Velvet</option>
                                    <option>Dutch Cannondale Pecan</option>
                                    <option>Dutch Cannondale White</option>
                                    <option>Dutch Cannondale Princeton</option>
                                </optgroup>
                                <optgroup label="Dutch Cleveland">
                                    <option>Dutch Cleveland Red Wood</option>
                                    <option>Dutch Cleveland Kiaat</option>
                                    <option>Dutch Cleveland Mahogany</option>
                                    <option>Dutch Cleveland Red Velvet</option>
                                    <option>Dutch Cleveland Pecan</option>
                                    <option>Dutch Cleveland White</option>
                                    <option>Dutch Cleveland Princeton</option>
                                </optgroup>
                                <optgroup label="Mini Dome (Kingston Dome)">
                                    <option>Mini Dome Red Wood</option>
                                    <option>Mini Dome Kiaat</option>
                                    <option>Mini Dome Mahogany</option>
                                    <option>Mini Dome Red Velvet</option>
                                    <option>Mini Dome White</option>
                                    <option>Mini Dome Pecan</option>
                                    <option>Mini Dome Cherry</option>
                                    <option>Mini Dome Princeton</option>
                                </optgroup>
                                <optgroup label="Superior Dome">
                                    <option>Superior Dome Red Wood</option>
                                    <option>Superior Dome Kiaat</option>
                                    <option>Superior Dome Mahogany</option>
                                    <option>Superior Dome Red Velvet</option>
                                    <option>Superior Dome White</option>
                                    <option>Superior Dome Pecan</option>
                                    <option>Superior Dome Cherry</option>
                                    <option>Superior Dome Princeton</option>
                                </optgroup>
                                <optgroup label="Royal Dome">
                                    <option>Royal Dome Red Wood</option>
                                    <option>Royal Dome Kiaat</option>
                                    <option>Royal Dome Mahogany</option>
                                    <option>Royal Dome Red Velvet</option>
                                    <option>Royal Dome White</option>
                                    <option>Royal Dome Pecan</option>
                                    <option>Royal Dome Cherry</option>
                                    <option>Royal Dome Princeton</option>
                                </optgroup>
                                <optgroup label="Prince Dome">
                                    <option>Prince Dome Red Wood</option>
                                    <option>Prince Dome Kiaat</option>
                                    <option>Prince Dome Mahogany</option>
                                    <option>Prince Dome Red Velvet</option>
                                    <option>Prince Dome White</option>
                                    <option>Prince Dome Pecan</option>
                                    <option>Prince Dome Cherry Matte</option>
                                    <option>Prince Dome Cherry Gloss</option>
                                    <option>Prince Dome Princeton</option>
                                </optgroup>
                                <optgroup label="Std Dome">
                                    <option>Std Dome Red Wood</option>
                                    <option>Std Dome Kiaat</option>
                                    <option>Std Dome Mahogany</option>
                                    <option>Std Dome Red Velvet</option>
                                    <option>Std Dome White</option>
                                    <option>Std Dome Pecan</option>
                                    <option>Std Dome Cherry Matte</option>
                                    <option>Std Dome Cherry Gloss</option>
                                    <option>Std Dome Princeton</option>
                                    <option>Std Dome Hamlock</option>
                                </optgroup>
                                <optgroup label="4 Corner Dome">
                                    <option>4 Corner Dome Red Wood</option>
                                    <option>4 Corner Dome Kiaat</option>
                                    <option>4 Corner Dome Mahogany</option>
                                    <option>4 Corner Dome Red Velvet</option>
                                    <option>4 Corner Dome White</option>
                                    <option>4 Corner Dome Pecan</option>
                                    <option>4 Corner Dome Cherry Matte</option>
                                    <option>4 Corner Dome Cherry Gloss</option>
                                    <option>4 Corner Dome Princeton</option>
                                    <option>4 Corner Dome Hamlock</option>
                                </optgroup>
                                <optgroup label="Quarter View Boston">
                                    <option>Quarter View Boston Red Wood</option>
                                    <option>Quarter View Boston Kiaat</option>
                                    <option>Quarter View Boston Mahogany</option>
                                    <option>Quarter View Boston Red Velvet</option>
                                    <option>Quarter View Boston White</option>
                                    <option>Quarter View Boston Pecan</option>
                                    <option>Quarter View Boston Cherry Matte</option>
                                    <option>Quarter View Boston Cherry Gloss</option>
                                    <option>Quarter View Boston Princeton</option>
                                    <option>Quarter View Boston Hamlock</option>
                                    <option>Quarter View Boston Imbuia</option>
                                </optgroup>
                                <optgroup label="Raised Half View">
                                    <option>Raised Half View Red Wood</option>
                                    <option>Raised Half View Kiaat</option>
                                    <option>Raised Half View Mahogany</option>
                                    <option>Raised Half View Red Velvet</option>
                                    <option>Raised Half View White</option>
                                    <option>Raised Half View Pecan</option>
                                    <option>Raised Half View Cherry Matte</option>
                                    <option>Raised Half View Cherry Gloss</option>
                                    <option>Raised Half View Princeton</option>
                                    <option>Raised Half View Hamlock</option>
                                </optgroup>
                                <optgroup label="2 Tier Casket">
                                    <option>2 Tier Casket Red Wood</option>
                                    <option>2 Tier Casket Kiaat</option>
                                    <option>2 Tier Casket Mahogany</option>
                                    <option>2 Tier Casket Red Velvet</option>
                                    <option>2 Tier Casket Pecan</option>
                                    <option>2 Tier Casket Cherry Matte</option>
                                    <option>2 Tier Casket Cherry Gloss</option>
                                    <option>2 Tier Casket White</option>
                                    <option>2 Tier Casket Princeton</option>
                                </optgroup>
                                <optgroup label="3 Tier Casket">
                                    <option>3 Tier Casket Red Wood</option>
                                    <option>3 Tier Casket Kiaat</option>
                                    <option>3 Tier Casket Mahogany</option>
                                    <option>3 Tier Casket Red Velvet</option>
                                    <option>3 Tier Casket Pecan</option>
                                    <option>3 Tier Casket Cherry Matte</option>
                                    <option>3 Tier Casket Cherry Gloss</option>
                                    <option>3 Tier Casket White</option>
                                    <option>3 Tier Casket Princeton</option>
                                </optgroup>
                                <optgroup label="4 Tier Casket">
                                    <option>4 Tier Casket Red Wood</option>
                                    <option>4 Tier Casket Kiaat</option>
                                    <option>4 Tier Casket Mahogany</option>
                                    <option>4 Tier Casket Red Velvet</option>
                                    <option>4 Tier Casket Pecan</option>
                                    <option>4 Tier Casket Cherry Matte</option>
                                    <option>4 Tier Casket Cherry Gloss</option>
                                    <option>4 Tier Casket White</option>
                                    <option>4 Tier Casket Princeton</option>
                                </optgroup>
                                <optgroup label="Dutch Swing Bar">
                                    <option>Dutch Swing Bar Red Wood</option>
                                    <option>Dutch Swing Bar Kiaat</option>
                                    <option>Dutch Swing Bar Mahogany</option>
                                    <option>Dutch Swing Bar Red Velvet</option>
                                    <option>Dutch Swing Bar Pecan</option>
                                    <option>Dutch Swing Bar Cherry Matte</option>
                                    <option>Dutch Swing Bar Cherry Gloss</option>
                                    <option>Dutch Swing Bar White</option>
                                </optgroup>
                            </select>
                        </td>
                        <td>
                            <select name="status[]">
                                <option>In-stock</option>
                                <option>Damage</option>
                                <option>Write-off</option>
                                <option>Own coffin</option>
                            </select>
                        </td>
                        <td><input type="date" name="action_date[]"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div class="btns">
                <button type="submit" class="sub">Insert</button>
                <button type="button" class="sub" onclick="clearForm()">Clear Table</button>
            </div>
        </form>
        <div id="result"></div>
        <div id="searchResults" style="display:none; margin-top: 20px;"></div>
    </div>
</div>

<script>
function toggleLogoutMenu() {
    const menu = document.getElementById('logoutMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function logout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = 'welcome.php';
    }
}

function toggleSubmenu(event, element) {
    event.preventDefault();
    const submenu = element.nextElementSibling;
    const isActive = submenu.classList.contains('active');
    
    document.querySelectorAll('.sidebar .submenu').forEach(sub => {
        sub.classList.remove('active');
    });
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
    });

    if (!isActive) {
        submenu.classList.add('active');
        element.classList.add('active');
    }
}

function setActiveLink() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('.sidebar a');
    
    links.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
}

function liveSearch() {
    const term = document.getElementById('searchInput').value.trim().toLowerCase();

    if (term.length === 0) {
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('coffinForm').style.display = 'block';
        return;
    }

    fetch('database.php')
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const table = doc.querySelector('#coffinTable');

            if (!table) {
                document.getElementById('searchResults').innerHTML = "<p>No table found.</p>";
                return;
            }

            const rows = table.querySelectorAll('tbody tr');
            let found = false;
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(term)) {
                    row.style.display = '';
                    found = true;
                } else {
                    row.style.display = 'none';
                }
            });

            table.querySelectorAll('.action-col').forEach(col => {
                col.style.display = 'table-cell';
            });

            table.querySelectorAll('.edit-btn').forEach(btn => {
                btn.onclick = () => {
                    const row = btn.closest('tr');
                    row.contentEditable = true;
                    row.classList.add('editable-row');

                    if (!document.getElementById('updateBtn')) {
                        const updateBtn = document.createElement('button');
                        updateBtn.id = 'updateBtn';
                        updateBtn.textContent = 'Update';
                        updateBtn.className = 'sub';
                        updateBtn.style.marginRight = '10px';

                        const undoBtn = document.createElement('button');
                        undoBtn.id = 'undoBtn';
                        undoBtn.textContent = 'Undo';
                        undoBtn.className = 'sub';

                        document.querySelector('.header-container').appendChild(updateBtn);
                        document.querySelector('.header-container').appendChild(undoBtn);

                        updateBtn.onclick = () => {
                            const editedCells = row.querySelectorAll('td');
                            const code = row.querySelector('.code-cell').innerText.trim();
                            const data = {
                                code: code,
                                coffin_type: editedCells[3].innerText.trim(),
                                storage: editedCells[5].innerText.trim(),
                                status: editedCells[6].innerText.trim(),
                                action_date: editedCells[7].innerText.trim()
                            };

                            fetch('update_row.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(data)
                            })
                            .then(response => response.json())
                            .then(res => {
                                alert(res.message);
                                if (res.success) {
                                    row.contentEditable = false;
                                    row.classList.remove('editable-row');
                                    updateBtn.remove();
                                    undoBtn.remove();
                                }
                            });
                        };

                        undoBtn.onclick = () => {
                            window.location.reload();
                        };
                    }
                };
            });

            table.querySelectorAll('.delete-btn').forEach(btn => {
                btn.onclick = () => {
                    const row = btn.closest('tr');
                    const code = row.querySelector('.code-cell').innerText.trim();

                    if (confirm(`Are you sure you want to delete coffin with code ${code}?`)) {
                        fetch('delete_row.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ code: code })
                        })
                        .then(response => response.json())
                        .then(res => {
                            alert(res.message);
                            if (res.success) {
                                row.remove();
                            }
                        });
                    }
                };
            });

            document.getElementById('coffinForm').style.display = 'none';
            document.getElementById('searchResults').style.display = 'block';
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('searchResults').appendChild(table);
        })
        .catch(() => {
            document.getElementById('searchResults').innerHTML = "<p style='color:red;'>Error fetching data.</p>";
        });
}

function clearForm() {
    document.getElementById('coffinForm').reset();
    document.getElementById('result').innerText = '';
}

document.addEventListener('DOMContentLoaded', setActiveLink);

const form = document.getElementById('coffinForm');
form.addEventListener('submit', function(e) {
    e.preventDefault();

    const branch = document.getElementById("branch").value;
    const storage = document.getElementById("storage").value;
    if (branch !== "Hlotse" && branch !== "Lithoteng" && storage === "Store room") {
        alert("Invalid Storage: Store room is only available for Hlotse and Lithoteng.");
        return;
    }

    const formData = new FormData(form);

    // Log form data for debugging
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    fetch('insert_coffins_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            });
        }
        return response.text(); // Get raw text first
    })
    .then(text => {
        console.log('Raw response:', text); // Log raw response for debugging
        try {
            const data = JSON.parse(text);
            alert(data.message);
            if (data.success) {
                clearForm();
            }
        } catch (e) {
            throw new Error(`JSON parse error: ${e.message}, response: ${text}`);
        }
    })
    .catch(error => {
        console.error('Insert error:', error);
        alert(`Error submitting data: ${error.message}`);
    });
});
</script>
</body>
</html>