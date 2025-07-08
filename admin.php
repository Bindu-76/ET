<?php
session_start();
require_once 'config.php';

// Only allow admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #e6f2ff;
    }
    .sidebar {
      height: 100vh;
      background-color: #004080;
      color: white;
    }
    .sidebar a {
      color: white;
      display: block;
      padding: 15px;
      text-decoration: none;
      margin-bottom: 20px;
      background-color: #0059b3;
      border-radius: 12px;
      text-align: center;
      transition: background-color 0.3s;
      font-size: 1.1rem;
    }
    .sidebar a:hover {
      background-color: #0073e6;
    }
    .dashboard-content {
      padding: 30px;
      background-color: #ffffff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      min-height: 90vh;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 sidebar p-4">
      <h3 class="text-center">Admin Panel</h3>
      <p class="text-center">Welcome, <?= htmlspecialchars($admin_name) ?></p>
      <hr>
      <a href="view_users.php">📋 View Users</a>
      <a href="manage_classes.php">🧘 Manage Classes</a>
      <a href="upload_videos.php">📹 Upload Videos</a>
      <a href="upload_poses.php">🤸‍♂️ Upload Poses</a>
      <a href="upload_mudras.php">🙏 Upload Mudras</a>
      <a href="subscription_reports.php">📈 Subscription Reports</a>
      <a href="workshop.php">Workshop Manage</a>
      <a href="feedback.php">⭐ View Feedback</a>
      <a href="logout.php" class="mt-4 btn btn-light w-100">Logout</a>
    </div>

    <!-- Dashboard Content -->
    <div class="col-md-9 dashboard-content">
      <h4>📊 Welcome to Eka Tatva Wellness Admin Dashboard</h4>
      <p>Select an option from the left menu to manage content.</p>
    </div>
  </div>
</div>

</body>
</html>
