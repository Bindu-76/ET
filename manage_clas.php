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
  <title>Manage Classes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Custom button style for shine and shadow */
    .btn-lg {
      height: 60px; /* increased height */
      font-size: 1.25rem; /* keep it readable */
      font-weight: 600;
      position: relative;
      overflow: hidden;
      transition: box-shadow 0.3s ease;
    }
    .btn-lg:hover {
      box-shadow:
        0 0 8px 2px rgba(255, 255, 255, 0.8),
        0 0 20px 4px rgba(255, 255, 255, 0.6),
        0 0 30px 6px rgba(255, 255, 255, 0.4);
    }
    /* Animate a subtle shine effect on hover */
    .btn-lg::before {
      content: "";
      position: absolute;
      top: 0;
      left: -75%;
      width: 50%;
      height: 100%;
      background: linear-gradient(120deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0.1) 60%, rgba(255,255,255,0) 100%);
      transform: skewX(-25deg);
      transition: left 0.5s ease;
      pointer-events: none;
      z-index: 2;
    }
    .btn-lg:hover::before {
      left: 125%;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-5 text-center">
    <h1 class="mb-4">Manage Yoga Classes</h1>
    <div class="d-grid gap-3 col-6 mx-auto">
      <a href="add_class.php" class="btn btn-success btn-lg">Add a Class</a>
      <a href="edit_class.php" class="btn btn-warning btn-lg">Edit a Class</a>
      <a href="delete_class.php" class="btn btn-danger btn-lg">Delete a Class</a>
       <a href="admin_bookings.php" class="btn btn-danger btn-lg">Bookings</a>
    </div>
  </div>
</body>
</html>
