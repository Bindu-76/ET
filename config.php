<?php
$host = "localhost";
$db_user = "root";
$db_pass = ""; // default XAMPP MySQL password is empty
$db_name = "ekatatvayoga"; // Use your database name

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
