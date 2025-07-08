<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

if (!isset($_GET['id'])) {
    die("No class ID provided.");
}

$id = (int)$_GET['id'];

// Fetch class info
$stmt = $pdo->prepare("SELECT class_name FROM yoga_classes WHERE id = ?");
$stmt->execute([$id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$class) {
    die("Class not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['confirm'] === 'yes') {
        $stmt = $pdo->prepare("DELETE FROM yoga_classes WHERE id = ?");
        $stmt->execute([$id]);
        echo "Class deleted successfully. <a href='manage_classes.php'>Go back</a>";
        exit();
    } else {
        header("Location: manage_classes.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head><title>Delete Class</title></head>
<body>
<h2>Are you sure you want to delete: <?= htmlspecialchars($class['class_name']) ?>?</h2>

<form method="post">
    <button name="confirm" value="yes">Yes, Delete</button>
    <button name="confirm" value="no">Cancel</button>
</form>
</body>
</html>

