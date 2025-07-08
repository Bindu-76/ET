<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest subscription for the user
$stmt = $conn->prepare("
    SELECT s.plan, s.goal, s.experience, s.notes, s.subscribed_at, u.name, u.email
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ?
    ORDER BY s.subscribed_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No subscription found.";
    exit;
}

$receipt = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - Eka Tatva Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', sans-serif;
        }
        .receipt-box {
            max-width: 700px;
            margin: 60px auto;
            padding: 30px;
            border: 1px solid #ccc;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>

<div class="receipt-box">
    <div class="text-center receipt-title">Payment Receipt</div>

    <p><span class="info-label">Name:</span> <?= htmlspecialchars($receipt['name']) ?></p>
    <p><span class="info-label">Email:</span> <?= htmlspecialchars($receipt['email']) ?></p>
    <p><span class="info-label">Subscription Plan:</span> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $receipt['plan']))) ?></p>
    <p><span class="info-label">Goal:</span> <?= htmlspecialchars($receipt['goal']) ?></p>
    <p><span class="info-label">Experience Level:</span> <?= htmlspecialchars($receipt['experience']) ?></p>
    <p><span class="info-label">Notes:</span> <?= htmlspecialchars($receipt['notes']) ?></p>
    <p><span class="info-label">Date of Subscription:</span> <?= htmlspecialchars(date("F j, Y, g:i a", strtotime($receipt['subscribed_at']))) ?></p>

    <div class="text-center mt-4">
        <a href="home.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</div>

</body>
</html>
