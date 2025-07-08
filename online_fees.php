<?php
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Fees - Eka Tatva Wellness</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f7f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 60px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #b2ebf2;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        .description {
            font-size: 14px;
            margin: 15px 0;
            color: #444;
        }
        .plan {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .plan:last-child {
            border-bottom: none;
        }
        .plan-text {
            text-align: left;
        }
        .btn-pay {
            background-color: #00bfa5;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 20px;
        }
        .btn-pay:hover {
            background-color: #008e76;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        Online Group Classes • Access to online classes only
    </div>
    <div class="description">
        A complete online yoga plan offering a dynamic mix of Ashtanga Led Series, deep hip-opening flows
    </div>

    <!-- Plan Options -->
    <div class="plan">
        <div class="plan-text">
            <strong>1 class • INR 800</strong><br>
            <small>Valid for 1 day</small>
        </div>
        <form method="post" action="pay_process.php">
            <input type="hidden" name="plan" value="1_class">
            <button type="submit" class="btn btn-pay">Pay</button>
        </form>
    </div>

    <div class="plan">
        <div class="plan-text">
            <strong>4 classes • INR 3,100</strong><br>
            <small>Valid for 1 month</small>
        </div>
        <form method="post" action="pay_process.php">
            <input type="hidden" name="plan" value="4_classes">
            <button type="submit" class="btn btn-pay">Pay</button>
        </form>
    </div>

    <div class="plan">
        <div class="plan-text">
            <strong>8 classes • INR 6,000</strong><br>
            <small>Valid for 1 month</small>
        </div>
        <form method="post" action="pay_process.php">
            <input type="hidden" name="plan" value="8_classes">
            <button type="submit" class="btn btn-pay">Pay</button>
        </form>
    </div>

    <div class="plan">
        <div class="plan-text">
            <strong>12 classes • INR 8,600</strong><br>
            <small>Valid for 1 month</small>
        </div>
        <form method="post" action="pay_process.php">
            <input type="hidden" name="plan" value="12_classes">
            <button type="submit" class="btn btn-pay">Pay</button>
        </form>
    </div>
</div>

</body>
</html>
