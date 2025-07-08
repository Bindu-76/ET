<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    if (!empty($email) && !empty($password) && !empty($role)) {
        if ($role === "user") {
            $stmt = $conn->prepare("SELECT id, name, password, subscription FROM users WHERE email = ? AND is_active = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $name, $hashed_password, $subscription);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user'] = $name;
                    $_SESSION['subscription'] = $subscription;

                    $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $update->bind_param("i", $user_id);
                    $update->execute();

                    header("Location: home.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "User not found or inactive.";
            }
            $stmt->close();
        } elseif ($role === "admin") {
            $stmt = $conn->prepare("SELECT id, name, password FROM admin WHERE email = ? AND is_active = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($admin_id, $name, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['admin_id'] = $admin_id;
                    $_SESSION['admin'] = $name;
                    $_SESSION['user_id'] = $admin_id;
                    $_SESSION['role'] = 'admin';

                    $update = $conn->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?");
                    $update->bind_param("i", $admin_id);
                    $update->execute();

                    header("Location: admin.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "Admin not found or inactive.";
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-image: url('images/p26 hover.jpg');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-box {
      max-width: 400px;
      margin: 100px auto;
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .logo {
      display: block;
      margin: 0 auto 20px;
      max-width: 150px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <img src="images/p25logo.jpg" alt="Eka Tatva Wellness" class="logo" />
    <h3 class="text-center mb-4">Login to Your Account</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>

      <div class="mb-3">
         <label for="password" class="form-label">Password</label>
         <div class="input-group">
          <input type="password" class="form-control" id="password" name="password" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePassword">Show</button>
       </div>
     </div>


      <div class="mb-3">
        <label for="role" class="form-label">Login as</label>
        <select class="form-select" name="role" id="role" required>
          <option value="">-- Select Role --</option>
          <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>User</option>
          <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <a href="forgot_password.php" class="text-decoration-none small">Forgot Password?</a>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <p class="text-center mt-3">
      Don't have an account? <a href="signup.php">Sign up here</a>
    </p>
  </div>
  <script>
  const togglePassword = document.querySelector("#togglePassword");
  const passwordField = document.querySelector("#password");

  togglePassword.addEventListener("click", function () {
    const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
    passwordField.setAttribute("type", type);
    this.textContent = type === "password" ? "Show" : "Hide";
  });
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
