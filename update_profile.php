<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$gender = $_POST['gender'];
$country = trim($_POST['country']);
$yoga_level = $_POST['yoga_level'];
$profile_photo = $_FILES['profile_photo'] ?? null;

// Default: don't change existing profile photo
$photo_filename = null;
$upload_success = true;

if ($profile_photo && $profile_photo['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $file_tmp = $profile_photo['tmp_name'];
    $file_size = $profile_photo['size'];
    $file_type = mime_content_type($file_tmp);

    if (!in_array($file_type, $allowed_types)) {
        $upload_success = false;
        $_SESSION['profile_updated'] = false;
        header("Location: profile.php?error=Invalid image format");
        exit();
    } elseif ($file_size > $max_size) {
        $upload_success = false;
        $_SESSION['profile_updated'] = false;
        header("Location: profile.php?error=Image size exceeds 2MB");
        exit();
    } else {
        $ext = pathinfo($profile_photo['name'], PATHINFO_EXTENSION);
        $photo_filename = uniqid('profile_', true) . '.' . $ext;
        $upload_path = __DIR__ . "/uploads/$photo_filename";

        if (!move_uploaded_file($file_tmp, $upload_path)) {
            $upload_success = false;
            $_SESSION['profile_updated'] = false;
            header("Location: profile.php?error=Failed to upload photo");
            exit();
        }
    }
}

// Build the update query
if ($photo_filename) {
    $query = "UPDATE users SET name=?, email=?, phone=?, gender=?, country=?, yoga_level=?, profile_photo=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssi", $name, $email, $phone, $gender, $country, $yoga_level, $photo_filename, $user_id);
    $_SESSION['profile_photo'] = $photo_filename;
} else {
    $query = "UPDATE users SET name=?, email=?, phone=?, gender=?, country=?, yoga_level=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $name, $email, $phone, $gender, $country, $yoga_level, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['profile_updated'] = true;
} else {
    $_SESSION['profile_updated'] = false;
}

$stmt->close();
$conn->close();

header("Location: profile.php");
exit();
