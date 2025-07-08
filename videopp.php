<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Fetch all videos
$stmt = $pdo->query("SELECT * FROM class_videos ORDER BY uploaded_at DESC");
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Yoga Class Videos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">Watch Yoga Class Videos</h2>

    <div class="row">
        <?php foreach ($videos as $video): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="ratio ratio-16x9">
                    <iframe src="<?= htmlspecialchars($video['video_link']) ?>" title="<?= htmlspecialchars($video['title']) ?>" allowfullscreen></iframe>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($video['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($video['description']) ?></p>
                    <small class="text-muted">Instructor: <?= htmlspecialchars($video['instructor']) ?> | Uploaded: <?= date('M j, Y', strtotime($video['uploaded_at'])) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
