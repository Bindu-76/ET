<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$db = 'ekatatva';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';

// Get plan ID
if (!isset($_GET['id'])) {
    header('Location: manage_plans.php');
    exit;
}

$plan_id = intval($_GET['id']);

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $amount_1 = $_POST['amount_1'] ?: null;
    $amount_3 = $_POST['amount_3'] ?: null;
    $amount_6 = $_POST['amount_6'] ?: null;
    $workshop = isset($_POST['workshop']) ? 1 : 0;
    $razorpay_plan_id = !empty($_POST['razorpay_plan_id']) ? trim($_POST['razorpay_plan_id']) : null;
    $amount_paise = isset($_POST['amount_paise']) ? intval($_POST['amount_paise']) : 0;
    $currency = !empty($_POST['currency']) ? trim($_POST['currency']) : 'INR';

    // Get current QR code filename from hidden input
    $current_qr_code = $_POST['current_qr_code'];

    // Handle QR code upload (optional)
    $qr_code_filename = $current_qr_code;
    if (!empty($_FILES['qr_code']['name'])) {
        $target_dir = "uploads/qr_codes/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $ext = pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION);
        $qr_code_filename = uniqid('qr_') . '.' . $ext;
        $target_file = $target_dir . $qr_code_filename;

        if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $target_file)) {
            // Delete old QR code file if exists and different from current
            if ($current_qr_code && file_exists($target_dir . $current_qr_code)) {
                unlink($target_dir . $current_qr_code);
            }
        } else {
            $message = "Failed to upload new QR code.";
        }
    }

    if (!$message) {
        $stmt = $conn->prepare("UPDATE subscription_plans SET 
            name = ?, description = ?, duration = ?, amount_1 = ?, amount_3 = ?, amount_6 = ?, 
            workshop = ?, qr_code = ?, razorpay_plan_id = ?, amount_paise = ?, currency = ? 
            WHERE id = ?");

        $stmt->bind_param("sssddsisssis", 
            $name, $description, $duration, 
            $amount_1, $amount_3, $amount_6, 
            $workshop, $qr_code_filename, 
            $razorpay_plan_id, $amount_paise, $currency, $plan_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Subscription plan updated successfully.";
            $stmt->close();
            header("Location: manage_plans.php");
            exit;
        } else {
            $message = "Error updating plan: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch existing plan data for form
$stmt = $conn->prepare("SELECT * FROM subscription_plans WHERE id = ?");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();
$plan = $result->fetch_assoc();
$stmt->close();

if (!$plan) {
    $_SESSION['flash_message'] = "Plan not found.";
    header("Location: manage_plans.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Subscription Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4">
    <h2>Edit Subscription Plan</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="edit_plan.php?id=<?= $plan_id ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Plan Name</label>
            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($plan['name']) ?>" />
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($plan['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <input type="text" id="duration" name="duration" class="form-control" required value="<?= htmlspecialchars($plan['duration']) ?>" />
        </div>

        <div class="mb-3">
            <label class="form-label">Prices</label>
            <div class="input-group mb-2">
                <span class="input-group-text">1 Month</span>
                <input type="number" step="0.01" min="0" name="amount_1" class="form-control" value="<?= htmlspecialchars($plan['amount_1']) ?>" />
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">3 Months</span>
                <input type="number" step="0.01" min="0" name="amount_3" class="form-control" value="<?= htmlspecialchars($plan['amount_3']) ?>" />
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">6 Months</span>
                <input type="number" step="0.01" min="0" name="amount_6" class="form-control" value="<?= htmlspecialchars($plan['amount_6']) ?>" />
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" id="workshop" name="workshop" class="form-check-input" <?= $plan['workshop'] ? 'checked' : '' ?> />
            <label for="workshop" class="form-check-label">Includes Workshop</label>
        </div>

        <div class="mb-3">
            <label for="razorpay_plan_id" class="form-label">Razorpay Plan ID</label>
            <input type="text" id="razorpay_plan_id" name="razorpay_plan_id" class="form-control" value="<?= htmlspecialchars($plan['razorpay_plan_id']) ?>" />
        </div>

        <div class="mb-3">
            <label for="amount_paise" class="form-label">Amount (in paise)</label>
            <input type="number" id="amount_paise" name="amount_paise" class="form-control" value="<?= htmlspecialchars($plan['amount_paise']) ?>" />
        </div>

        <div class="mb-3">
            <label for="currency" class="form-label">Currency</label>
            <input type="text" id="currency" name="currency" class="form-control" value="<?= htmlspecialchars($plan['currency']) ?>" />
        </div>

        <div class="mb-3">
            <label for="qr_code" class="form-label">QR Code Image</label><br />
            <?php if ($plan['qr_code']): ?>
                <img src="uploads/qr_codes/<?= htmlspecialchars($plan['qr_code']) ?>" width="100" alt="QR Code" /><br />
            <?php endif; ?>
            <input type="file" id="qr_code" name="qr_code" class="form-control" />
            <input type="hidden" name="current_qr_code" value="<?= htmlspecialchars($plan['qr_code']) ?>" />
            <small class="form-text text-muted">Upload to replace existing QR code.</small>
        </div>

        <button type="submit" class="btn btn-success">Update Plan</button>
        <a href="manage_plans.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
</body>
</html>
