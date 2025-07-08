<?php
session_start();
require_once "config.php"; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan = $_POST['plan'] ?? '';
    $goal = trim($_POST['goal'] ?? '');
    $experience = $_POST['experience'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validate inputs
    $valid_plans = ['1_week', '1_month', '3_months', '6_months'];
    $valid_experience = ['Beginner', 'Intermediate', 'Advanced'];

    if (!in_array($plan, $valid_plans)) {
        die("Invalid subscription plan selected.");
    }
    if (empty($goal)) {
        die("Please enter your primary yoga goal.");
    }
    if (!in_array($experience, $valid_experience)) {
        die("Invalid experience level selected.");
    }

    // Insert subscription data
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan, goal, experience, notes, subscribed_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("issss", $user_id, $plan, $goal, $experience, $notes);

    if ($stmt->execute()) {
        // Update user's subscription status in users table
        $update_stmt = $conn->prepare("UPDATE users SET subscription = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("si", $plan, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        $stmt->close();
        $_SESSION['success_message'] = "Subscription successful! Thank you for joining Eka Tatva Wellness.";
        header("Location: home.php");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
} else {
    header("Location: subscribe.php");
    exit();
}
