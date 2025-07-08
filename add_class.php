<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php'; // your PDO connection file

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $class_name = trim($_POST['class_name'] ?? '');
    $instructor = trim($_POST['instructor'] ?? '');
    $class_date = $_POST['class_date'] ?? '';
    $class_time = $_POST['class_time'] ?? '';
    $duration = intval($_POST['duration'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $video_link = trim($_POST['video_link'] ?? '');

    // Validate required fields
    if (empty($class_name) || empty($instructor) || empty($class_date) || empty($class_time) || $duration <= 0) {
        $error_msg = 'Please fill in all required fields with valid values.';
    } else {
        try {
            $sql = "INSERT INTO yoga_classes (class_name, instructor, class_date, class_time, duration, description, video_link) 
                    VALUES (:class_name, :instructor, :class_date, :class_time, :duration, :description, :video_link)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':class_name' => $class_name,
                ':instructor' => $instructor,
                ':class_date' => $class_date,
                ':class_time' => $class_time,
                ':duration' => $duration,
                ':description' => $description,
                ':video_link' => $video_link,
            ]);

            $success_msg = "Class '$class_name' added successfully.";
            // Clear variables so form resets
            $class_name = $instructor = $class_date = $class_time = $duration = $description = $video_link = '';

        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add New Yoga Class</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <h1 class="mb-4 text-center">Add a New Yoga Class</h1>

    <?php if ($success_msg): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <form method="POST" action="add_class.php" class="mx-auto" style="max-width:600px;">
      <div class="mb-3">
        <label for="class_name" class="form-label">Class Name <span class="text-danger">*</span></label>
        <input type="text" name="class_name" id="class_name" class="form-control" required
               value="<?php echo htmlspecialchars($class_name ?? ''); ?>" />
      </div>

      <div class="mb-3">
        <label for="instructor" class="form-label">Instructor Name <span class="text-danger">*</span></label>
        <input type="text" name="instructor" id="instructor" class="form-control" required
               value="<?php echo htmlspecialchars($instructor ?? ''); ?>" />
      </div>

      <div class="mb-3">
        <label for="class_date" class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="class_date" id="class_date" class="form-control" required
               value="<?php echo htmlspecialchars($class_date ?? ''); ?>" />
      </div>

      <div class="mb-3">
        <label for="class_time" class="form-label">Time <span class="text-danger">*</span></label>
        <input type="time" name="class_time" id="class_time" class="form-control" required
               value="<?php echo htmlspecialchars($class_time ?? ''); ?>" />
      </div>

      <div class="mb-3">
        <label for="duration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
        <input type="number" name="duration" id="duration" class="form-control" min="1" required
               value="<?php echo htmlspecialchars($duration ?? ''); ?>" />
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="4" class="form-control"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
      </div>

      <div class="mb-3">
        <label for="video_link" class="form-label">Video/Join Link (optional)</label>
        <input type="url" name="video_link" id="video_link" class="form-control"
               value="<?php echo htmlspecialchars($video_link ?? ''); ?>" />
      </div>

      <div class="d-flex justify-content-between">
        <a href="manage_classes.php" class="btn btn-secondary">Back to Manage Classes</a>
        <button type="submit" class="btn btn-success">Add Class</button>
      </div>
    </form>
  </div>
</body>
</html>
