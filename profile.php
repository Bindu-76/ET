<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ekatatvayoga");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$profile_updated = false;
if (isset($_SESSION['profile_updated']) && $_SESSION['profile_updated']) {
    $profile_updated = true;
    unset($_SESSION['profile_updated']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Eka Tatva Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center mb-3">Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
    <p class="text-center text-muted">Here are your profile details</p>

    <?php if ($profile_updated): ?>
        <div class="alert alert-success text-center">✅ Profile updated successfully!</div>
    <?php endif; ?>

    <div class="card shadow p-4 mx-auto" style="max-width: 800px;">
        <form method="post" action="update_profile.php" enctype="multipart/form-data">
            <!-- Profile Photo -->
            <div class="text-center mb-4">
                <img src="uploads/<?= htmlspecialchars($user['profile_photo'] ?? 'default.jpg') ?>" class="profile-photo mb-2">
                <div>
                    <input type="file" name="profile_photo" class="form-control mt-2" accept="image/*">
                </div>
            </div>

            <!-- Editable Fields -->
            <h5 class="text-primary mt-3 mb-3">Edit Your Information</h5>
            <div class="mb-3">
                <label>Name:</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Phone:</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Gender:</label>
                <select name="gender" class="form-select" required>
                    <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Country:</label>
                <select class="form-select" name="country" required>
                    <option value="">-- Select Country --</option>
                    <option value="India" <?= $user['country'] == 'India' ? 'selected' : '' ?>>India</option>
                    <option value="United States" <?= $user['country'] == 'United States' ? 'selected' : '' ?>>United States</option>
                    <option value="United Kingdom" <?= $user['country'] == 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                    <option value="Canada" <?= $user['country'] == 'Canada' ? 'selected' : '' ?>>Canada</option>
                    <option value="Australia" <?= $user['country'] == 'Australia' ? 'selected' : '' ?>>Australia</option>
                    <option value="Others" <?= $user['country'] == 'Others' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Yoga Level:</label>
                <select name="yoga_level" class="form-select" required>
                    <option value="Beginner" <?= ($user['yoga_level'] ?? '') == 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="Intermediate" <?= ($user['yoga_level'] ?? '') == 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="Advanced" <?= ($user['yoga_level'] ?? '') == 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <!-- Read-only Fields -->
            <h5 class="text-secondary mt-4 mb-3">Account Details</h5>
            <div class="mb-3">
                <label>Subscription Plan:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['subscription']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Join Date:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['join_date']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Last Login:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['last_login']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Account Status:</label>
                <input type="text" class="form-control" value="<?= $user['is_active'] ? 'Active' : 'Inactive' ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Role:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Subscription Start:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['subscription_start']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Subscription End:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['subscription_end']) ?>" readonly>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-success">Update Profile</button>
                <a href="home.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
