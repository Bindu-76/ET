<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$db = 'ekatatvayoga';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    $message = 'Subscription plan deleted.';
} elseif (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Handle Add Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plan'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $amount_1 = $_POST['amount_1'] !== '' ? floatval($_POST['amount_1']) : 0.0;
    $amount_3 = $_POST['amount_3'] !== '' ? floatval($_POST['amount_3']) : 0.0;
    $amount_6 = $_POST['amount_6'] !== '' ? floatval($_POST['amount_6']) : 0.0;
    $workshop = isset($_POST['workshop']) ? 1 : 0;
    $razorpay_plan_id = !empty($_POST['razorpay_plan_id']) ? trim($_POST['razorpay_plan_id']) : '';
    $amount_paise = isset($_POST['amount_paise']) ? intval($_POST['amount_paise']) : 0;
    $currency = !empty($_POST['currency']) ? trim($_POST['currency']) : 'INR';

    // Handle QR code upload
    $qr_code_filename = '';
    if (!empty($_FILES['qr_code']['name'])) {
        $target_dir = "uploads/qr_codes/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $ext = pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION);
        $qr_code_filename = uniqid('qr_') . '.' . $ext;
        $target_file = $target_dir . $qr_code_filename;

        if (!move_uploaded_file($_FILES['qr_code']['tmp_name'], $target_file)) {
            $message = "Failed to upload QR code.";
        }
    }

    if (!$message) {
        $stmt = $conn->prepare("INSERT INTO subscription_plans 
            (name, description, duration, amount_1, amount_3, amount_6, workshop, qr_code, razorpay_plan_id, currency, amount_paise, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param('sssdddisssi',
            $name, $description, $duration,
            $amount_1, $amount_3, $amount_6,
            $workshop, $qr_code_filename,
            $razorpay_plan_id, $currency,
            $amount_paise
        );

        if ($stmt->execute()) {
            $message = "Subscription plan added successfully.";
        } else {
            $message = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle Delete Plan
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete']) && empty($_POST)) {
    $delete_id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT qr_code FROM subscription_plans WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->bind_result($qr_file);
    $stmt->fetch();
    $stmt->close();

    if ($qr_file && file_exists("uploads/qr_codes/" . $qr_file)) {
        unlink("uploads/qr_codes/" . $qr_file);
    }

    $stmt = $conn->prepare("DELETE FROM subscription_plans WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: manage_plans.php?message=deleted");
        exit();
    } else {
        $message = "Failed to delete plan: " . $conn->error;
    }
    $stmt->close();
}

// Fetch plans
$result = $conn->query("SELECT * FROM subscription_plans ORDER BY created_at DESC");
$plans = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin - Manage Subscription Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4">
    <h2 class="mb-4 text-center">Manage Subscription Plans</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h4>Add New Plan</h4>
    <form method="POST" enctype="multipart/form-data" action="manage_plans.php" class="mb-5">
        <input type="hidden" name="add_plan" value="1" />
        <div class="mb-3">
            <label for="name" class="form-label">Plan Name</label>
            <input type="text" id="name" name="name" class="form-control" required />
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <input type="text" id="duration" name="duration" class="form-control" required />
        </div>

        <div class="mb-3">
            <label class="form-label">Prices (1 / 3 / 6 Months)</label>
            <input type="number" step="0.01" name="amount_1" class="form-control mb-2" placeholder="1 Month Price">
            <input type="number" step="0.01" name="amount_3" class="form-control mb-2" placeholder="3 Months Price">
            <input type="number" step="0.01" name="amount_6" class="form-control mb-2" placeholder="6 Months Price">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="workshop" name="workshop">
            <label class="form-check-label" for="workshop">Includes Workshop</label>
        </div>

        <div class="mb-3">
            <label for="razorpay_plan_id" class="form-label">Razorpay Plan ID</label>
            <input type="text" id="razorpay_plan_id" name="razorpay_plan_id" class="form-control" />
        </div>

        <div class="mb-3">
            <label for="amount_paise" class="form-label">Amount in Paise</label>
            <input type="number" id="amount_paise" name="amount_paise" class="form-control" />
        </div>

        <div class="mb-3">
            <label for="currency" class="form-label">Currency</label>
            <input type="text" id="currency" name="currency" class="form-control" value="INR" />
        </div>

        <div class="mb-3">
            <label for="qr_code" class="form-label">QR Code Image</label>
            <input type="file" id="qr_code" name="qr_code" class="form-control" accept="image/*" />
        </div>

        <button type="submit" class="btn btn-success">Add Plan</button>
    </form>

    <h4>Existing Plans</h4>
    <table class="table table-bordered align-middle">
        <thead>
        <tr>
            <th>Plan Name</th>
            <th>Duration</th>
            <th>Workshop</th>
            <th>Price (1 / 3 / 6 months)</th>
            <th>QR Code</th>
            <th>Razorpay ID</th>
            <th>Currency</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($plans) === 0): ?>
            <tr><td colspan="8" class="text-center">No plans found.</td></tr>
        <?php else: foreach ($plans as $plan): ?>
            <tr>
                <td><?= htmlspecialchars($plan['name']) ?></td>
                <td><?= htmlspecialchars($plan['duration']) ?></td>
                <td><?= $plan['workshop'] ? 'Yes' : 'No' ?></td>
                <td>
                    <?= $plan['amount_1'] !== null ? number_format($plan['amount_1'], 2) : '-' ?> /
                    <?= $plan['amount_3'] !== null ? number_format($plan['amount_3'], 2) : '-' ?> /
                    <?= $plan['amount_6'] !== null ? number_format($plan['amount_6'], 2) : '-' ?>
                </td>
                <td>
                    <?php if (!empty($plan['qr_code']) && file_exists("uploads/qr_codes/" . $plan['qr_code'])): ?>
                        <img src="uploads/qr_codes/<?= htmlspecialchars($plan['qr_code']) ?>" alt="QR" height="50">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($plan['razorpay_plan_id']) ?></td>
                <td><?= htmlspecialchars($plan['currency']) ?></td>
                <td>
                    <a href="edit_plan.php?id=<?= $plan['id'] ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                    <a href="manage_plans.php?delete=<?= $plan['id'] ?>" onclick="return confirm('Delete this plan?')" class="btn btn-sm btn-danger mb-1">Delete</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
