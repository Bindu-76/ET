<?php
session_start();

// If user is already logged in, redirect to home.php
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Welcome | Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      background: #fdf6e3;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #6b4226;
      text-align: center;
    }
    img {
      max-width: 150px;
      margin-bottom: 20px;
      opacity: 0;
      animation: fadeIn 3s forwards;
    }
    h1 {
      font-size: 2.5rem;
      opacity: 0;
      animation: fadeIn 3s forwards 1s;
    }
    .btn-start {
      margin-top: 30px;
      padding: 12px 25px;
      font-size: 1.1rem;
      border-radius: 8px;
      background-color: #6b4226;
      color: white;
      border: none;
      opacity: 0;
      animation: fadeIn 3s forwards 2s;
      transition: background-color 0.3s;
    }
    .btn-start:hover {
      background-color: #8b5e3c;
    }
    @keyframes fadeIn {
      to { opacity: 1; }
    }
  </style>
</head>
<body>
  <!-- Logo -->
  <img src="images/p25logo.jpg" alt="Eka Tatva Wellness Logo" />

  <!-- Welcome Message -->
  <h1>Welcome to Eka Tatva Wellness</h1>

  <!-- Button to Login -->
  <a href="login.php" class="btn btn-start">Get Started</a>
</body>
</html>
