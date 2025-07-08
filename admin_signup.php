<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $phone    = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];

    if (!empty($name) && !empty($email) && !empty($password) && ($password === $confirm)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'superadmin';

        $stmt = $conn->prepare("INSERT INTO admin (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed, $phone, $role);

        if ($stmt->execute()) {
            $_SESSION["admin_id"] = $stmt->insert_id;
            $_SESSION["admin_name"] = $name;
            $_SESSION["admin_email"] = $email;
            $_SESSION["admin_role"] = $role;

            header("Location: login.php");
            exit();
        } else {
            $error = "Admin email already exists or something went wrong.";
        }
        $stmt->close();
    } else {
        $error = "Please fill all fields and ensure passwords match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Signup - Eka Tatva Wellness</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f2f2f2;
    }
    .signup-container {
      max-width: 500px;
      margin: 60px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <h2 class="text-center mb-4">Admin Signup</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" name="phone" class="form-control" pattern="[0-9]{10}" title="Enter 10-digit phone number" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="showPasswordToggle" onclick="togglePassword()">
        <label class="form-check-label" for="showPasswordToggle">
          Show Password
        </label>
      </div>

      <button type="submit" class="btn btn-primary w-100">Register as Admin</button>
    </form>
  </div>

  <script>
    function togglePassword() {
      const password = document.getElementById("password");
      const confirm = document.getElementById("confirm_password");
      const type = password.type === "password" ? "text" : "password";
      password.type = type;
      confirm.type = type;
    }
  </script>
</body>
</html>
