<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = 'localhost';
$db = 'ekatatvayoga';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
$userId = $_SESSION['user_id'];

// Fetch all subscription plans
$result = $conn->query("SELECT * FROM subscription_plans ORDER BY created_at DESC");
$plans = $result->fetch_all(MYSQLI_ASSOC);

// Fetch current user's subscription plan
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
  <title>Subscription Plans - Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  
  <!-- Razorpay checkout script -->
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
<div class="container my-4">
  <h2 class="mb-4 text-center">Available Subscription Plans</h2>

  <?php if ($message): ?>
    <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($plans as $plan): ?>
      <div class="col">
        <div class="card h-100 shadow">
          <?php if ($plan['qr_code']): ?>
            <img src="uploads/qr_codes/<?= htmlspecialchars($plan['qr_code']) ?>" class="card-img-top" alt="QR Code" style="max-height:150px; object-fit:contain;">
          <?php endif; ?>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($plan['name']) ?></h5>
            <p class="card-text"><?= nl2br(htmlspecialchars($plan['description'])) ?></p>
            <p><strong>Duration:</strong> <?= htmlspecialchars($plan['duration']) ?></p>
            <p><strong>Amount:</strong>
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
          <div class="card-footer text-center">
            <button type="button" class="btn btn-primary pay-btn"
                    data-plan-id="<?= $plan['id'] ?>"
                    data-plan-name="<?= htmlspecialchars($plan['name']) ?>"
                    data-amount="<?php
                      if ($plan['workshop']) {
                          echo 0;
                      } else {
                          switch ($plan['duration']) {
                              case '1 Month': echo $plan['amount_1'] * 100; break; // amount in paise
                              case '3 Months': echo $plan['amount_3'] * 100; break;
                              case '6 Months': echo $plan['amount_6'] * 100; break;
                              default: echo 0;
                          }
                      }
                    ?>">
              Pay with Razorpay
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
document.querySelectorAll('.pay-btn').forEach(button => {
  button.addEventListener('click', function () {
    const planId = this.dataset.planId;
    const planName = this.dataset.planName;
    const amount = this.dataset.amount;

    if (amount <= 0) {
      alert('Invalid payment amount for this plan.');
      return;
    }

    fetch('create_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `plan_id=${planId}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }

      const options = {
        key: "YOUR_RAZORPAY_KEY_ID", // Replace with your Razorpay Key ID
        amount: data.amount,
        currency: data.currency,
        name: "Eka Tatva Wellness",
        description: `Subscribe to ${planName}`,
        order_id: data.order_id,
        handler: function (response) {
          // Verify payment server-side
          fetch('verify_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `razorpay_payment_id=${response.razorpay_payment_id}&razorpay_order_id=${response.razorpay_order_id}&razorpay_signature=${response.razorpay_signature}&plan_id=${planId}`
          })
          .then(res => res.text())
          .then(msg => alert(msg))
          .catch(() => alert('Payment verification failed.'));
        },
        prefill: {
          name: "<?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>",
          email: "<?= htmlspecialchars($_SESSION['user_email'] ?? 'user@example.com') ?>"
        },
        theme: {
          color: "#0d6efd"
        }
      };

      const rzp = new Razorpay(options);
      rzp.open();
    })
    .catch(() => alert('Failed to initiate payment. Please try again.'));
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

