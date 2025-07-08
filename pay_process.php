<?php
session_start();
require_once "config.php"; // DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$subscription_plan_id = $_POST['plan_id'] ?? null;
$mode = $_POST['mode'] ?? null; // 'online' or 'offline'
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? null;

if (!$subscription_plan_id || !in_array($mode, ['online', 'offline'])) {
    die("Invalid submission.");
}

if ($mode === 'online') {
    // Razorpay API Verification
    $razorpay_key = 'YOUR_RAZORPAY_KEY_ID';
    $razorpay_secret = 'YOUR_RAZORPAY_SECRET';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.razorpay.com/v1/payments/$razorpay_payment_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "$razorpay_key:$razorpay_secret",
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) {
        die("Payment verification failed.");
    }

    $payment_data = json_decode($response, true);
    if ($payment_data['status'] !== 'captured') {
        die("Payment not captured. Please try again.");
    }
} else {
    $razorpay_payment_id = null; // Not needed for offline
}

// Insert into subscriptions table
$stmt = $conn->prepare("INSERT INTO subscriptions (user_id, subscription_plan_id, mode, razorpay_payment_id, paid_at)
                        VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $user_id, $subscription_plan_id, $mode, $razorpay_payment_id);

if ($stmt->execute()) {
    $subscription_id = $stmt->insert_id;
    $stmt->close();

    // Optionally update user's current plan
    $update_stmt = $conn->prepare("UPDATE users SET subscription_plan_id = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $subscription_plan_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect to receipt or homepage
    header("Location: receipt.php?id=$subscription_id");
    exit();
} else {
    die("Database error: " . $stmt->error);
}
?>
