<?php
session_start();

$host = 'localhost';
$db = 'ekatatva';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
$duration = $_GET['duration'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planName = trim($_POST['planName'] ?? '');
    $amount = $_POST['amount'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $duration = $_POST['duration'] ?? '';

    if ($planName === '') {
        $message = "Plan Name cannot be empty.";
    } elseif ($duration === '') {
        $message = "Duration is required.";
    } else {
        $amount_1 = null;
        $amount_3 = null;
        $amount_6 = null;
        $workshop = 0;

        switch ($duration) {
            case '1 Month':
                $amount_1 = $amount !== '' ? (int)$amount : null;
                break;
            case '3 Months':
                $amount_3 = $amount !== '' ? (int)$amount : null;
                break;
            case '6 Months':
                $amount_6 = $amount !== '' ? (int)$amount : null;
                break;
            case 'Workshop':
                $workshop = 1;
                $amount = null;
                break;
            default:
                $message = "Invalid duration selected.";
        }

        if (!$message) {
            $qrFileName = null;
            if (isset($_FILES['qrCode']) && $_FILES['qrCode']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/qr_codes/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $tmpName = $_FILES['qrCode']['tmp_name'];
                $ext = pathinfo($_FILES['qrCode']['name'], PATHINFO_EXTENSION);
                $qrFileName = uniqid('qr_', true) . '.' . $ext;
                move_uploaded_file($tmpName, $uploadDir . $qrFileName);
            }

            $stmt = $conn->prepare("INSERT INTO subscription_plans (name, amount_1, amount_3, amount_6, workshop, qr_code, description, duration, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param(
                "siiissss",
                $planName,
                $amount_1,
                $amount_3,
                $amount_6,
                $workshop,
                $qrFileName,
                $description,
                $duration
            );

            if ($stmt->execute()) {
                $message = "Plan added successfully!";
            } else {
                $message = "Error adding plan: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM subscription_plans WHERE id = $id");
    header("Location: manage_plans.php");
    exit;
}

$result = $conn->query("SELECT * FROM subscription_plans ORDER BY created_at DESC");
$plans = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Subscription Plans</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4">
  <h2>Manage Subscription Plans</h2>
  <?php if ($message): ?>
    <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="mb-3">
      <label for="planName" class="form-label">Plan Name</label>
      <input type="text" name="planName" id="planName" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="duration" class="form-label">Duration</label>
      <select name="duration" id="duration" class="form-control" required>
        <option value="">Select Duration</option>
        <option value="1 Month">1 Month</option>
        <option value="3 Months">3 Months</option>
        <option value="6 Months">6 Months</option>
        <option value="Workshop">Workshop</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="amount" class="form-label">Amount (if applicable)</label>
      <input type="number" name="amount" id="amount" class="form-control">
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea name="description" id="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label for="qrCode" class="form-label">QR Code (optional)</label>
      <input type="file" name="qrCode" id="qrCode" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Add Plan</button>
  </form>

  <table class="table table-bordered table-dark">
    <thead>
      <tr>
        <th>Name</th>
        <th>Duration</th>
        <th>Amount</th>
        <th>Workshop</th>
        <th>Description</th>
        <th>QR Code</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($plans as $plan): ?>
        <tr>
          <td><?= htmlspecialchars($plan['name']) ?></td>
          <td><?= htmlspecialchars($plan['duration']) ?></td>
          <td><?= $plan['workshop'] ? 'N/A' : ($plan['duration'] === '1 Month' ? $plan['amount_1'] : ($plan['duration'] === '3 Months' ? $plan['amount_3'] : $plan['amount_6'])) ?></td>
          <td><?= $plan['workshop'] ? 'Yes' : 'No' ?></td>
          <td><?= htmlspecialchars($plan['description']) ?></td>
          <td>
            <?php if ($plan['qr_code']): ?>
              <img src="uploads/qr_codes/<?= htmlspecialchars($plan['qr_code']) ?>" alt="QR Code" style="width: 60px; height: auto;">
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($plan['created_at']) ?></td>
          <td>
            <a href="edit_plan.php?id=<?= $plan['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="?delete=<?= $plan['id'] ?>" onclick="return confirm('Are you sure you want to delete this plan?');" class="btn btn-danger btn-sm">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
