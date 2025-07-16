<?php 
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=coffin_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$department = $_GET['department'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM login_details WHERE Department = ? AND Username = ? AND Password = ?");
    $stmt->execute([$department, $username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['username'] = $user['Username'];
        $_SESSION['department'] = $user['Department'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Login failed: Wrong username/password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #1a0000;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        .container {
            background-color: #330000;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);
        }

        .department-display {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgb(117, 78, 0);
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #fff;
            color: #000;
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: rgb(117, 78, 0);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }

        button:hover {
            background-color: rgb(233, 204, 147);
            color: black;
        }

        .error {
            color: #ff0000ff;
            margin-top: 10px;
            font-size: 14px;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgb(117, 78, 0);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: rgb(233, 204, 147);
            color: black;
        }
    </style>
</head>
<body>

<?php if (!empty($department)): ?>
    <div class="department-display">
        Department: <?= htmlspecialchars($department) ?>
    </div>
<?php endif; ?>

<a href="welcome.php" class="back-button">← Back</a>
<div class="container">
    <h2>Login</h2>

    <form method="post" action="login.php?department=<?= urlencode($department) ?>" id="loginForm" autocomplete="off">
        <input type="text" name="username" id="username" placeholder="Username" required autocomplete="off">
        <input type="password" name="password" id="password" placeholder="Password" required autocomplete="off">
        <button type="submit">Login</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
</div>

</body>
</html>
