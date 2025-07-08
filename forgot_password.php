<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // Email found
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour

            $stmt->bind_result($user_id);
            $stmt->fetch();

            $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
            $update->bind_param("ssi", $token, $expiry, $user_id);
            $update->execute();

            $resetLink = "http://localhost/reset_password.php?token=" . $token;

            $subject = "Eka Tatva | Password Reset";
            $message = "Click the link below to reset your password:\n$resetLink\n\nThis link will expire in 1 hour.";
            $headers = "From: no-reply@ekatatva.com";

            mail($email, $subject, $message, $headers);
            $success = "If this email is registered, a reset link has been sent.";
        } else {
            $success = "If this email is registered, a reset link has been sent.";
        }

        $stmt->close();
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Forgot Password | Eka Tatva Wellness</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-image: url('images/p26 hover.jpg');
      background-size: cover;
      background-position: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .forgot-box {
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
  <div class="forgot-box">
    <img src="images/p25logo.jpg" alt="Eka Tatva Wellness" class="logo" />
    <h3 class="text-center mb-4">Forgot Your Password?</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (!empty($success)): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
      <div class="mb-3">
        <label for="email" class="form-label">Enter your registered email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>

    <p class="text-center mt-3">
      <a href="login.php" class="text-decoration-none">Back to Login</a>
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
