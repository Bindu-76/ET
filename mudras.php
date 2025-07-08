<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

require_once "config.php";

// Fetch all mudras ordered by name
$sql = "SELECT id, name, description, image_path FROM mudras ORDER BY name ASC";
$result = $conn->query($sql);

$mudras = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $mudras[] = $row;
    }
} else {
    die("Database query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mudras Library | Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #fffaf2;
      font-family: 'Segoe UI', sans-serif;
      padding: 30px;
    }
    h1 {
      text-align: center;
      margin-bottom: 40px;
      color: #333;
    }
    .mudra-card {
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 30px;
      background-color: white;
      transition: transform 0.3s ease;
    }
    .mudra-card:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    .mudra-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-bottom: 1px solid #ddd;
    }
    .mudra-card-body {
      padding: 15px;
    }
    .mudra-name {
      font-size: 1.4rem;
      font-weight: 600;
      color: #007bff;
      margin-bottom: 8px;
    }
    .mudra-desc {
      font-size: 1rem;
      color: #555;
    }
    @media (min-width: 768px) {
      .mudras-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
      }
    }
    @media (max-width: 767px) {
      .mudras-grid {
        display: flex;
        flex-direction: column;
        gap: 25px;
      }
    }
  </style>
</head>
<body>
  <h1>Mudras Library</h1>
  <div class="mudras-grid">
    <?php foreach ($mudras as $mudra): ?>
      <div class="mudra-card">
        <?php if (!empty($mudra['image_path']) && file_exists($mudra['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($mudra['image_path']); ?>" alt="<?php echo htmlspecialchars($mudra['name']); ?>" />
        <?php else: ?>
          <img src="uploads/default.png" alt="No Image Available" />
        <?php endif; ?>
        <div class="mudra-card-body">
          <div class="mudra-name"><?php echo htmlspecialchars($mudra['name']); ?></div>
          <div class="mudra-desc"><?php echo nl2br(htmlspecialchars($mudra['description'])); ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
