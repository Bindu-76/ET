<?php
$host = 'localhost';
$dbname = 'ekatatvayoga';  // your database name
$user = 'root';          // your MySQL username (default XAMPP is root)
$pass = '';              // your MySQL password (default is empty)

// PDO connection with error handling
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
