<?php
session_start();
require_once "config.php";

// Optional: only allow admin
// if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
//   header("Location: login.php");
//   exit();
// }

$query = $conn->prepare("
  SELECT ratings.rating, ratings.comment, ratings.created_at, users.name
  FROM ratings
  JOIN users ON ratings.user_id = users.id
  ORDER BY ratings.created_at DESC
");
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Feedback | Eka Tatva Wellness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">📝 User Ratings & Feedback</h2>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>User Name</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td>
                <?php for ($i = 0; $i < $row['rating']; $i++) echo '⭐'; ?>
              </td>
              <td><?= htmlspecialchars($row['comment']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
