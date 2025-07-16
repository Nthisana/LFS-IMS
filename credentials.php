<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "", "coffin_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_user"])) {
    $name = trim($_POST["name"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);  // store plain text password
    $department = trim($_POST["department"]);
    $role = trim($_POST["role"]);

    // Check if username exists
    $check = $conn->prepare("SELECT * FROM login_details WHERE Username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('Username already exists: $username');</script>";
    } else {
        // Get max ID
        $result = $conn->query("SELECT MAX(id) AS max_id FROM login_details");
        $row = $result->fetch_assoc();
        $newId = $row['max_id'] ? $row['max_id'] + 1 : 1;

        // Insert new user with manual ID
        $stmt = $conn->prepare("INSERT INTO login_details (id, Name, Username, Password, Department, Role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $newId, $name, $username, $password, $department, $role);
        if ($stmt->execute()) {
            echo "<script>alert('User added successfully!'); window.location.href='credentials.php';</script>";
            exit;
        } else {
            echo "<script>alert('Failed to add user.');</script>";
        }
        $stmt->close();
    }
    $check->close();
}

// Delete user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {
    $id = (int)$_POST["delete_user"];
    $conn->query("DELETE FROM login_details WHERE id = $id");
    echo "<script>window.location.href='credentials.php';</script>";
    exit;
}

// Fetch all users ordered by ID ascending
$sql = "SELECT * FROM login_details ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Credentials</title>
    <style>
        body {
            background-color: #330000;
            color: white;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: white;
            color: black;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: rgb(174, 170, 162);
            color: black;
        }

        .buttons-container {
            text-align: center;
            margin-bottom: 15px;
        }

        .action-button {
            background-color: rgb(80, 2, 2);
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 10px;
        }

        .action-button:hover {
            background-color: rgb(31, 0, 0);
        }

        #userForm {
            width: 60%;
            margin: 0 auto 30px auto;
            background-color: rgb(50, 1, 1);
            padding: 20px;
            border-radius: 8px;
            display: none;
        }

        #userForm input, #userForm select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
            font-size: 14px;
        }

        #userForm button {
            padding: 10px 15px;
            background-color: #660000;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }

        #userForm button:hover {
            background-color: #aa0000;
        }

        #deleteForm {
            width: 40%;
            margin: 20px auto 30px auto;
            background-color: rgb(50, 1, 1);
            padding: 20px;
            border-radius: 8px;
            display: none;
            text-align: center;
        }

        #deleteForm select {
            width: 80%;
            padding: 8px;
            border-radius: 5px;
            border: none;
            font-size: 14px;
            margin-bottom: 15px;
        }

        #deleteForm button {
            padding: 10px 15px;
            background-color: maroon;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }

        #deleteForm button:hover {
            background-color: darkred;
        }

        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background-color: rgb(50, 1, 1);
        }

        th, td {
            border: 1px solid rgb(162, 159, 159);
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background-color: rgb(35, 7, 7);
        }

        tr:nth-child(even) {
            background-color: #550000;
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-button">← Back</a>

<h2>LFS IMS Users</h2>

<div class="buttons-container">
    <button class="action-button" onclick="toggleForm('add')">Add New User</button>
    <button class="action-button" onclick="toggleForm('delete')">Delete User</button>
</div>

<!-- Add User Form -->
<form id="userForm" method="POST" style="display:none;">
    <label>Full Name:</label>
    <input type="text" name="name" required>

    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Password:</label>
    <input type="text" name="password" required>

    <label>Department:</label>
    <select name="department" required>
        <option value="">Select Department</option>
        <option>Finance</option>
        <option>Preneed</option>
        <option>Undertaker</option>
        <option>Mortuary</option>
        <option>Sales</option>
        <option>Transport</option>
        <option>Insurance</option>
        <option>Property</option>
    </select>

    <label>Role:</label>
    <select name="role" required>
        <option value="">Select Role</option>
        <option>Admin</option>
        <option>Staff</option>
    </select>

    <button type="submit" name="add_user">Add</button>
</form>

<!-- Delete User Form -->
<form id="deleteForm" method="POST" style="display:none;" onsubmit="return confirm('Are you sure you want to delete this user?');">
    <label>Select User to Delete:</label><br>
    <select name="delete_user" required>
        <option value="">-- Select User --</option>
        <?php
        // We need to fetch users again for the delete dropdown, ordered by id asc
        $deleteResult = $conn->query("SELECT id, Name, Username FROM login_details ORDER BY id ASC");
        if ($deleteResult && $deleteResult->num_rows > 0) {
            while ($user = $deleteResult->fetch_assoc()) {
                echo "<option value=\"" . htmlspecialchars($user['id']) . "\">" . htmlspecialchars($user['Name']) . " (" . htmlspecialchars($user['Username']) . ")</option>";
            }
        }
        ?>
    </select><br>
    <button type="submit">Delete</button>
</form>

<!-- Users Table -->
<?php
if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr>";
    // Show headers
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";

    // Reset result pointer and show rows
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No users found in the database.</p>";
}

$conn->close();
?>

<script>
function toggleForm(formType) {
    const addForm = document.getElementById("userForm");
    const deleteForm = document.getElementById("deleteForm");
    if (formType === 'add') {
        addForm.style.display = (addForm.style.display === "block") ? "none" : "block";
        deleteForm.style.display = "none";
    } else if (formType === 'delete') {
        deleteForm.style.display = (deleteForm.style.display === "block") ? "none" : "block";
        addForm.style.display = "none";
    }
}
</script>

</body>
</html>
