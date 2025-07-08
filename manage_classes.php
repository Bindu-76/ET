<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Fetch all classes
$stmt = $pdo->query("SELECT * FROM yoga_classes ORDER BY class_date DESC");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Yoga Classes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
  <h1 class="mb-4 text-center">Manage Yoga Classes</h1>

  <div class="mb-4 text-center">
    <a href="add_class.php" class="btn btn-success">➕ Add New Class</a>
    <a href="admin_bookings.php" class="btn btn-primary">📅 View Bookings</a>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Class Name</th>
        <th>Date</th>
        <th>Time</th>
        <th>Instructor</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($classes as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['class_name']) ?></td>
        <td><?= htmlspecialchars($c['class_date']) ?></td>
        <td><?= htmlspecialchars(date('h:i A', strtotime($c['class_time']))) ?></td>
        <td><?= htmlspecialchars($c['instructor']) ?></td>
        <td>
          <a href="edit_class.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="delete_class.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
