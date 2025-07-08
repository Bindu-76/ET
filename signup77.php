<?php
session_start();
require_once 'config.php';

$error = '';

// Default photo if no upload
$default_photo = 'default.png';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $gender = $_POST["gender"];
    $country = trim($_POST["country"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Profile photo upload handling
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_name = basename($_FILES['profile_photo']['name']);
        $file_size = $_FILES['profile_photo']['size'];
        $file_type = mime_content_type($file_tmp);

        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif ($file_size > $max_size) {
            $error = "Profile photo must be under 2MB.";
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = uniqid('profile_', true) . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $error = "Failed to upload photo.";
            }
        }
    } else {
        $new_filename = $default_photo;
    }

    if (empty($error)) {
        if (!empty($name) && !empty($gender) && !empty($country) && !empty($email) && !empty($password) && $password === $confirm_password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // default role

            $stmt = $conn->prepare("INSERT INTO users (name, gender, country, email, password, profile_photo, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $gender, $country, $email, $hashed, $new_filename, $role);

            if ($stmt->execute()) {
                // Auto-login user after signup
                $_SESSION["id"] = $stmt->insert_id;
                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;
                $_SESSION["profile_photo"] = $new_filename;

                header("Location: home.php");
                exit();
            } else {
                $error = "Email already exists or something went wrong.";
                if ($new_filename !== $default_photo && file_exists($upload_path)) {
                    unlink($upload_path);
                }
            }
            $stmt->close();
        } else {
            $error = "Please fill all fields and make sure passwords match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Signup - Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f2f9ff;
    }
    .signup-container {
      max-width: 500px;
      margin: 50px auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <h2 class="text-center mb-4">Signup</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" name="name" required />
      </div>

      <div class="mb-3">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" name="gender">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other" selected>Other</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="country" class="form-label">Country</label>
        <input type="text" class="form-control" name="country" value="India" required />
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required />
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required />
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="confirm_password" required />
      </div>

      <div class="mb-3">
        <label for="profile_photo" class="form-label">Profile Photo (optional)</label>
        <input type="file" class="form-control" name="profile_photo" accept="image/*" />
        <div class="form-text">Allowed types: JPG, JPEG, PNG, GIF. Max size: 2MB.</div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
  </div>
</body>
</html>

