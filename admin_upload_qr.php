<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qr_code'])) {
  $upload_dir = 'qr/';
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  $file_tmp = $_FILES['qr_code']['tmp_name'];
  $target_path = $upload_dir . 'current_qr.png'; // Always overwrite

  if (move_uploaded_file($file_tmp, $target_path)) {
    $msg = "QR code uploaded successfully!";
  } else {
    $msg = "Failed to upload QR code.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload QR Code</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3 class="mb-3">📤 Upload New QR Code</h3>
  
  <?php if (isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="qr_code" class="form-label">Select QR Code Image:</label>
      <input type="file" class="form-control" name="qr_code" id="qr_code" required>
    </div>
    <button type="submit" class="btn btn-success">Upload QR</button>
    <a href="subscription_reports.php" class="btn btn-secondary">Back</a>
  </form>
</div>
</body>
</html>

