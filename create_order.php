<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['plan_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$planId = intval($_POST['plan_id']);

// DB connection
$host = 'localhost';
$db = 'ekatatva';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch plan details to get amount
$stmt = $conn->prepare("SELECT name, duration, amount_1, amount_3, amount_6, workshop FROM subscription_plans WHERE id = ?");
$stmt->bind_param("i", $planId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['error' => 'Plan not found']);
    exit;
}

$plan = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($plan['workshop']) {
    echo json_encode(['error' => 'Workshop plans are not payable online']);
    exit;
}

// Determine amount in paise (smallest currency unit)
switch ($plan['duration']) {
    case '1 Month':
        $amount = $plan['amount_1'] * 100;
        break;
    case '3 Months':
        $amount = $plan['amount_3'] * 100;
        break;
    case '6 Months':
        $amount = $plan['amount_6'] * 100;
        break;
    default:
        echo json_encode(['error' => 'Invalid plan duration']);
        exit;
}

// Razorpay API credentials - set these with your real keys
$keyId = 'YOUR_RAZORPAY_KEY_ID';
$keySecret = 'YOUR_RAZORPAY_SECRET';

// Create order using Razorpay API (using curl)
$data = [
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => 'rcpt_' . time() . '_' . $planId,
    'payment_capture' => 1
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_USERPWD, $keyId . ":" . $keySecret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    echo json_encode(['error' => 'Failed to create order']);
    exit;
}

$order = json_decode($response, true);

echo json_encode([
    'order_id' => $order['id'],
    'amount' => $order['amount'],
    'currency' => $order['currency']
]);
