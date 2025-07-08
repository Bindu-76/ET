<?php
session_start();
// Optional: You can enforce user login here if you want.
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

require 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT * FROM poses ORDER BY created_at DESC");
    $poses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Yoga Pose Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .pose-card img {
      max-width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
    }
    .pose-card {
      min-height: 350px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4 text-center">Yoga Pose Library</h2>

  <?php if (count($poses) === 0): ?>
    <p class="text-center">No poses available at the moment. Please check back later.</p>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($poses as $pose): ?>
        <div class="col-md-4">
          <div class="card pose-card shadow-sm">
            <?php if ($pose['image_path'] && file_exists($pose['image_path'])): ?>
              <img src="<?= htmlspecialchars($pose['image_path']) ?>" alt="<?= htmlspecialchars($pose['name']) ?>" class="card-img-top" />
            <?php else: ?>
              <img src="https://via.placeholder.com/300x180?text=No+Image" alt="No Image" class="card-img-top" />
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($pose['name']) ?></h5>
              <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($pose['category']) ?></h6>
              <p class="card-text"><?= nl2br(htmlspecialchars($pose['description'])) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
