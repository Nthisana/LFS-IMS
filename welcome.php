<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - LFS IMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #1a0000;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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

        .container img {
            width: 180px; /* logo size */
            height: auto;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #fff;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #ddd;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #fff;
            color: #000;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color:rgb(117, 78, 0); 
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: rgb(233, 204, 147);
            color: black;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Company Logo -->
    <img src="images/lfs-logo.png" alt="LFS Logo">

    <!-- System Name -->
    <h1>LFS IMS</h1>

    <!-- Department prompt -->
    <p>Choose your department</p>

    <!-- Department dropdown -->
    <form action="login.php" method="get">
        <select name="department" required>
            <option value="">-- Select Department --</option>
            <option value="Finance">Finance</option>
            <option value="Preneed">Preneed</option>
            <option value="Undertaker">Undertaker</option>
            <option value="Mortuary">Mortuary</option>
            <option value="Sales">Sales</option>
            <option value="Transport">Transport</option>
            <option value="Insurance and Property">Insurance and Property</option>
            <option value="Management">Management</option>
        </select>

        <!-- Submit button -->
        <button type="submit">Submit</button>
    </form>
</div>

</body>
</html>
