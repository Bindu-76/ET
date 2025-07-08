<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$success = $error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['qr_image'])) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $error = "Only image formats allowed: jpg, jpeg, png, gif.";
    } else {
        $uploadDir = "uploads/qrcodes/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = "qr_" . time() . "." . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $filepath)) {
            // Remove old QR if exists
            $old = $pdo->query("SELECT qr_image_path FROM admin_settings WHERE id = 1")->fetchColumn();
            if ($old && file_exists($old)) unlink($old);

            $stmt = $pdo->prepare("UPDATE admin_settings SET qr_image_path = ?, updated_at = NOW() WHERE id = 1");
            $stmt->execute([$filepath]);
            $success = "QR code uploaded successfully.";
        } else {
            $error = "Failed to upload QR code.";
        }
    }
}

// Fetch existing QR
$qr_image = $pdo->query("SELECT qr_image_path FROM admin_settings WHERE id = 1")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
    <h3>Upload Payment QR Code</h3>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="qr_image" class="form-label">Select QR Code Image:</label>
            <input type="file" name="qr_image" class="form-control" required />
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <?php if ($qr_image && file_exists($qr_image)): ?>
        <h5 class="mt-4">Current QR Code:</h5>
        <img src="<?= $qr_image ?>" style="max-width: 300px;" />
    <?php endif; ?>
</body>
</html>
