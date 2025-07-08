<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Initialize variables
$class = null;
$errors = [];
$success = '';

// Handle form submission for updating class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class'])) {
    $id = (int)$_POST['id'];
    $class_name = trim($_POST['class_name']);
    $instructor = trim($_POST['instructor']);
    $class_date = $_POST['class_date'];
    $class_time = $_POST['class_time'];
    $duration = (int)$_POST['duration'];
    $description = trim($_POST['description']);
    $video_link = trim($_POST['video_link']);

    // Validate required fields
    if ($class_name === '') $errors[] = "Class name is required.";
    if ($instructor === '') $errors[] = "Instructor is required.";
    if ($class_date === '') $errors[] = "Class date is required.";
    if ($class_time === '') $errors[] = "Class time is required.";
    if ($duration <= 0) $errors[] = "Duration must be greater than zero.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE yoga_classes SET 
            class_name = ?, instructor = ?, class_date = ?, class_time = ?, duration = ?, description = ?, video_link = ?
            WHERE id = ?");
        $stmt->execute([$class_name, $instructor, $class_date, $class_time, $duration, $description, $video_link, $id]);
        $success = "Class updated successfully.";
    }
}

// If a class ID is passed via GET, fetch its details for editing
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM yoga_classes WHERE id = ?");
    $stmt->execute([$id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$class) {
        die("Class not found.");
    }
}

// Fetch all classes for the list
$stmt = $pdo->query("SELECT id, class_name, class_date FROM yoga_classes ORDER BY class_date DESC");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Yoga Classes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center">Edit Yoga Classes</h1>

    <div class="row">
        <div class="col-md-4">
            <h4>Select a class to edit</h4>
            <ul class="list-group">
                <?php foreach ($classes as $c): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($c['class_name']) ?>
                        <a href="?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="col-md-8">
            <?php if ($class): ?>
                <h4>Edit Class: <?= htmlspecialchars($class['class_name']) ?></h4>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="id" value="<?= $class['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="class_name" class="form-label">Class Name</label>
                        <input type="text" name="class_name" id="class_name" class="form-control" required
                            value="<?= htmlspecialchars($class['class_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="instructor" class="form-label">Instructor</label>
                        <input type="text" name="instructor" id="instructor" class="form-control" required
                            value="<?= htmlspecialchars($class['instructor']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="class_date" class="form-label">Date</label>
                        <input type="date" name="class_date" id="class_date" class="form-control" required
                            value="<?= htmlspecialchars($class['class_date']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="class_time" class="form-label">Time</label>
                        <input type="time" name="class_time" id="class_time" class="form-control" required
                            value="<?= htmlspecialchars($class['class_time']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="duration" class="form-control" min="1" required
                            value="<?= htmlspecialchars($class['duration']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($class['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="video_link" class="form-label">Video Link (optional)</label>
                        <input type="url" name="video_link" id="video_link" class="form-control"
                            value="<?= htmlspecialchars($class['video_link']) ?>">
                    </div>

                    <button type="submit" name="update_class" class="btn btn-success">Update Class</button>
                </form>
            <?php else: ?>
                <p>Select a class from the list to edit its details.</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="manage_classes.php" class="btn btn-link mt-4">Back to Manage Classes</a>
</div>
</body>
</html>
