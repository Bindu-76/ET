<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard | Subscription Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f8f9fa;
      padding: 50px;
    }
    .btn-container {
      max-width: 400px;
      margin: auto;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 40px;
    }
  </style>
</head>
<body>
  <h1>Admin Subscription Management</h1>
  <div class="btn-container">
    <a href="manage_plans.php" class="btn btn-primary btn-lg">Manage Plans</a>
    <a href="subscribed_users.php" class="btn btn-success btn-lg">Subscription Report</a>
  </div>
</body>
</html>
