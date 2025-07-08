<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Fetch booking info with user and class details
$stmt = $pdo->prepare("
    SELECT 
        b.id AS booking_id, 
        u.name, 
        u.email, 
        c.class_name, 
        c.class_date, 
        c.class_time, 
        b.completed
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN yoga_classes c ON b.class_id = c.id
    ORDER BY c.class_date DESC, c.class_time DESC
");
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin - Bookings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Bookings Overview</h1>
    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">No bookings found.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Class Name</th>
                    <th>Class Date</th>
                    <th>Class Time</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['booking_id']) ?></td>
                        <td><?= htmlspecialchars($b['name']) ?></td>
                        <td><?= htmlspecialchars($b['email']) ?></td>
                        <td><?= htmlspecialchars($b['class_name']) ?></td>
                        <td><?= date('M j, Y', strtotime($b['class_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($b['class_time'])) ?></td>
                        <td><?= $b['completed'] ? 'Yes' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
