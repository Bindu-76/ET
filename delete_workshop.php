<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    die('Workshop ID is required.');
}

$workshop_id = $_GET['id'];

// Delete sessions first
$stmt_sessions = $pdo->prepare("DELETE FROM sessions WHERE workshop_id = ?");
$stmt_sessions->execute([$workshop_id]);

// Delete the workshop
$stmt_workshop = $pdo->prepare("DELETE FROM workshops WHERE id = ?");
$stmt_workshop->execute([$workshop_id]);

// Redirect back
header("Location: workshop.php?deleted=1");
exit();
?>
