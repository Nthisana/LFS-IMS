<?php
// db.php
// Set up the database connection using PDO

$host = 'localhost';
$db   = 'coffin_db'; // change to your database name
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO($dsn, $user, $pass);
    // Enable error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
