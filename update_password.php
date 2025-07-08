<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"] ?? '';
    $password = $_POST["password"] ?? '';
    $confirm = $_POST["confirm"] ?? '';

    if ($password === $confirm && !empty($token)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id);
            $stmt->fetch();

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
            $update->bind_param("si", $hashed, $user_id);
            $update->execute();

            echo "<script>alert('Password updated successfully. Please login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Invalid or expired token.'); window.location='forgot_password.php';</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
    }
}
?>
