<?php
include 'config.php'; // database config
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fees - Eka Tatva Wellness</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #eef4f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 100px;
            text-align: center;
        }
        .btn-custom {
            width: 260px;
            height: 65px;
            font-size: 18px;
            margin: 15px;
            border-radius: 10px;
        }
        .header-title {
            margin-bottom: 40px;
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="header-title">Select Fee Payment Type</h1>

    <div class="d-flex justify-content-center flex-wrap">
        <!-- Online Group Classes -->
        <form action="online_fees.php" method="post" style="display: inline;">
            <button type="submit" class="btn btn-success btn-custom">
                Online Group Classes
            </button>
        </form>

        <!-- In-Person Classes -->
        <form action="inperson_fees.php" method="post" style="display: inline;">
            <button type="submit" class="btn btn-warning btn-custom">
                In-Person Classes
            </button>
        </form>

        <!-- Offline Classes -->
        <form action="offline_fees.php" method="post" style="display: inline;">
            <button type="submit" class="btn btn-primary btn-custom">
                Offline Classes
            </button>
        </form>
    </div>
</div>

</body>
</html>
