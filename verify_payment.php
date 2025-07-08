<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit;
}

$required = ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature', 'plan_id'];
foreach ($required as $key) {
    if (empty($_POST[$key])) {
        echo "Missing parameter: $key";
        exit;
    }
}

$paymentId = $_POST['razorpay_payment_id'];
$orderId = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];
$planId = intval($_POST['plan_id']);
$userId = $_SESSION['user_id'];

// Razorpay API credentials
$keyId = 'YOUR_RAZORPAY_KEY_ID';
$keySecret = 'YOUR_RAZORPAY_SECRET';

// Verify signature
$data = $orderId . '|' . $paymentId;
$generated_signature = hash_hmac('sha256', $data, $keySecret);

if ($generated_signature !== $signature) {
    echo "Payment verification failed. Invalid signature.";
    exit;
}

// DB connection
$host = 'localhost';
$db = 'ekatatva';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "Database connection failed.";
    exit;
}

// Update user's subscription plan and start date
$stmt = $conn->prepare("UPDATE users SET subscription_plan_id = ?, subscription_start = NOW() WHERE id = ?");
$stmt->bind_param("ii", $planId, $userId);

if ($stmt->execute()) {
    echo "Subscription updated successfully. Thank you for your payment!";
} else {
    echo "Failed to update subscription. Please contact support.";
}

$stmt->close();
$conn->close();
