<?php
session_start();

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$db = 'ekatatvayoga';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
$userId = $_SESSION['user_id'];

// Handle subscription form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $planId = intval($_POST['plan_id']);

    // Optional: verify plan exists
    $stmt = $conn->prepare("SELECT id FROM subscription_plans WHERE id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Update user's subscription
        $update = $conn->prepare("UPDATE users SET subscription_plan_id = ?, subscription_start = NOW() WHERE id = ?");
        $update->bind_param("ii", $planId, $userId);
        if ($update->execute()) {
            $message = "Subscription updated successfully!";
        } else {
            $message = "Error updating subscription: " . $conn->error;
        }
        $update->close();
    } else {
        $message = "Selected plan does not exist.";
    }

    $stmt->close();
}

// Fetch all subscription plans to display
$result = $conn->query("SELECT * FROM subscription_plans ORDER BY created_at DESC");
$plans = $result->fetch_all(MYSQLI_ASSOC);

// Fetch current user's subscription
$currentPlanId = null;
$stmt = $conn->prepare("SELECT subscription_plan_id FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($currentPlanId);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Subscription Plans</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4">
  <h2>Available Subscription Plans</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" action="subscription.php">
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php foreach ($plans as $plan): ?>
      <div class="col">
        <div class="card h-100">
          <?php if ($plan['qr_code']): ?>
            <img src="uploads/qr_codes/<?= htmlspecialchars($plan['qr_code']) ?>" class="card-img-top" alt="QR Code" style="max-height:150px; object-fit: contain;">
          <?php endif; ?>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($plan['name']) ?></h5>
            <p class="card-text"><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
            <p><strong>Duration:</strong> <?= htmlspecialchars($plan['duration']) ?></p>
            <p>
              <strong>Amount:</strong>
              <?php
                if ($plan['workshop']) {
                  echo "N/A (Workshop)";
                } else {
                  switch ($plan['duration']) {
                    case '1 Month':
                      echo $plan['amount_1'] ? "₹" . $plan['amount_1'] : "N/A";
                      break;
                    case '3 Months':
                      echo $plan['amount_3'] ? "₹" . $plan['amount_3'] : "N/A";
                      break;
                    case '6 Months':
                      echo $plan['amount_6'] ? "₹" . $plan['amount_6'] : "N/A";
                      break;
                    default:
                      echo "N/A";
                  }
                }
              ?>
            </p>
          </div>
          <div class="card-footer">
            <input type="radio" name="plan_id" value="<?= $plan['id'] ?>" id="plan_<?= $plan['id'] ?>"
              <?= ($currentPlanId == $plan['id']) ? 'checked' : '' ?> required>
            <label for="plan_<?= $plan['id'] ?>">
              <?= ($currentPlanId == $plan['id']) ? "Current Plan" : "Select this Plan" ?>
            </label>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Subscribe / Change Plan</button>
    </div>
  </form>

</div>
</body>
</html>
