<?php
session_start();

// Check if admin is logged in (adjust according to your admin session logic)
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$host = 'localhost';
$db = 'ekatatvayoga';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all subscribed users with their subscription plans
$sql = "SELECT u.id AS user_id, u.name AS user_name, u.email, u.subscription_start, 
               p.name AS plan_name, p.duration, p.amount_1, p.amount_3, p.amount_6, p.workshop, p.qr_code
        FROM users u
        LEFT JOIN subscription_plans p ON u.subscription_plan_id = p.id
        WHERE u.subscription_plan_id IS NOT NULL
        ORDER BY u.subscription_start DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Subscribed Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4">
    <h2>Subscribed Users</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>No users have subscribed yet.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subscription Start</th>
                    <th>Plan Name</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Workshop</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['subscription_start']) ?></td>
                    <td><?= htmlspecialchars($row['plan_name']) ?></td>
                    <td><?= htmlspecialchars($row['duration']) ?></td>
                    <td>
                        <?php
                        if ($row['workshop']) {
                            echo "N/A (Workshop)";
                        } else {
                            switch ($row['duration']) {
                                case '1 Month':
                                    echo $row['amount_1'] ? "₹" . $row['amount_1'] : "N/A";
                                    break;
                                case '3 Months':
                                    echo $row['amount_3'] ? "₹" . $row['amount_3'] : "N/A";
                                    break;
                                case '6 Months':
                                    echo $row['amount_6'] ? "₹" . $row['amount_6'] : "N/A";
                                    break;
                                default:
                                    echo "N/A";
                            }
                        }
                        ?>
                    </td>
                    <td><?= $row['workshop'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <?php if ($row['qr_code']): ?>
                            <img src="uploads/qr_codes/<?= htmlspecialchars($row['qr_code']) ?>" alt="QR Code" style="height: 80px; width: auto;">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
</body>
</html>
