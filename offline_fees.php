<?php
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Offline Fees - Eka Tatva Wellness</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f7f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 750px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            background-color: #dcedc8;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            margin-top: 30px;
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
        .address, .note {
            margin-top: 30px;
            font-size: 14px;
            color: #444;
        }
        .highlight {
            font-weight: bold;
            color: #2e7d32;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Offline Class Fees</h2>

    <!-- Group Classes -->
    <div class="section-title">Group Classes (In-Person)</div>
    <div class="plan">
        <div class="plan-text">
            <strong>1 class • INR 1,000</strong><br>
            <small>Valid for 1 day</small>
        </div>
    </div>
    <div class="plan">
        <div class="plan-text">
            <strong>8 classes • INR 7,200</strong><br>
            <small>Valid for 1 month</small>
        </div>
    </div>
    <div class="plan">
        <div class="plan-text">
            <strong>12 classes • INR 10,800</strong><br>
            <small>Valid for 1 month</small>
        </div>
    </div>
    <div class="plan">
        <div class="plan-text">
            <strong>16 classes • INR 13,600</strong><br>
            <small>Valid for 1 month</small>
        </div>
    </div>

    <!-- Individual Classes -->
    <div class="section-title">Individual Classes (1-on-1)</div>
    <div class="plan">
        <div class="plan-text">
            <strong>1 class • INR 2,000</strong><br>
            <small>Valid for 1 day</small>
        </div>
    </div>
    <div class="plan">
        <div class="plan-text">
            <strong>4 classes • INR 7,500</strong><br>
            <small>Valid for 1 month</small>
        </div>
    </div>
    <div class="plan">
        <div class="plan-text">
            <strong>8 classes • INR 14,000</strong><br>
            <small>Valid for 1 month</small>
        </div>
    </div>

    <!-- Studio Address -->
    <div class="address">
        <p><span class="highlight">Studio Address:</span><br>
        No: 325, Third Floor, "SAPTHAGIRI", 4th Main, 4th Cross,<br>
        Vijayanagar 1st Stage, Mysore</p>
    </div>

    <!-- Payment Note -->
    <div class="note">
        <p><span class="highlight">Note:</span> You can pay at the studio via cash, UPI, or bank transfer in offline mode.</p>
    </div>
</div>

</body>
</html>
