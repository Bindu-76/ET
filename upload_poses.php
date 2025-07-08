<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$success = '';
$error = '';
$editing = false;
$editId = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    // Get image path to delete file
    $stmt = $pdo->prepare("SELECT image_path FROM poses WHERE id = ?");
    $stmt->execute([$deleteId]);
    $pose = $stmt->fetch();
    if ($pose) {
        if ($pose['image_path'] && file_exists($pose['image_path'])) {
            unlink($pose['image_path']);
        }
        $delStmt = $pdo->prepare("DELETE FROM poses WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $success = "Pose deleted successfully.";
    } else {
        $error = "Pose not found.";
    }
}

// Handle Edit load
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM poses WHERE id = ?");
    $stmt->execute([$editId]);
    $pose = $stmt->fetch();
    if ($pose) {
        $editing = true;
    } else {
        $error = "Pose not found for editing.";
    }
}

// Handle POST (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;
    $editing = $editId ? true : false;

    if (empty($name) || empty($category)) {
        $error = "Name and Category are required.";
    } else {
        $imagePath = null;
        $newImageUploaded = false;

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorCode = $_FILES['image']['error'];
            if ($errorCode === UPLOAD_ERR_OK) {
                $targetDir = "uploads/poses/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                $filename = basename($_FILES["image"]["name"]);
                $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($fileType, $allowed)) {
                    $error = "Invalid image type. Allowed: " . implode(", ", $allowed);
                } else {
                    $targetFile = $targetDir . time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        $imagePath = $targetFile;
                        $newImageUploaded = true;
                    } else {
                        $error = "Failed to move uploaded image.";
                    }
                }
            } else {
                $error = "Error uploading image.";
            }
        }

        if (!$error) {
            try {
                if ($editing) {
                    if ($newImageUploaded) {
                        // Delete old image if exists
                        $stmt = $pdo->prepare("SELECT image_path FROM poses WHERE id = ?");
                        $stmt->execute([$editId]);
                        $oldImage = $stmt->fetchColumn();
                        if ($oldImage && file_exists($oldImage)) {
                            unlink($oldImage);
                        }
                        $image_to_save = $imagePath;
                    } else {
                        // Keep old image
                        $stmt = $pdo->prepare("SELECT image_path FROM poses WHERE id = ?");
                        $stmt->execute([$editId]);
                        $image_to_save = $stmt->fetchColumn();
                    }

                    $stmt = $pdo->prepare("UPDATE poses SET name = ?, category = ?, description = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$name, $category, $description, $image_to_save, $editId]);
                    $success = "Pose updated successfully!";
                    $editing = false;
                    $editId = null;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO poses (name, category, description, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $category, $description, $imagePath]);
                    $success = "Pose added successfully!";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all poses to show
$poses = $pdo->query("SELECT * FROM poses ORDER BY created_at DESC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Yoga Poses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    img.pose-img {
      max-width: 100px;
      height: auto;
      border-radius: 6px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4"><?= $editing ? "Edit Pose" : "Add New Pose" ?></h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="mb-5">
    <input type="hidden" name="edit_id" value="<?= $editing ? htmlspecialchars($pose['id']) : '' ?>" />
    <div class="mb-3">
      <label class="form-label">Pose Name</label>
      <input type="text" name="name" class="form-control" required value="<?= $editing ? htmlspecialchars($pose['name']) : '' ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">Category</label>
      <input type="text" name="category" class="form-control" required value="<?= $editing ? htmlspecialchars($pose['category']) : '' ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" rows="3" class="form-control"><?= $editing ? htmlspecialchars($pose['description']) : '' ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Pose Image (jpg, png, gif)</label>
      <input type="file" name="image" accept="image/*" class="form-control" <?= $editing ? '' : 'required' ?> />
      <?php if ($editing && $pose['image_path'] && file_exists($pose['image_path'])): ?>
        <small>Current Image:</small><br />
        <img src="<?= htmlspecialchars($pose['image_path']) ?>" alt="Pose Image" class="pose-img mt-1" />
      <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editing ? "Update Pose" : "Add Pose" ?></button>
    <?php if ($editing): ?>
      <a href="upload_poses.php" class="btn btn-secondary ms-2">Cancel</a>
    <?php endif; ?>
  </form>

  <h3>Existing Poses</h3>
  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Description</th>
        <th>Image</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (count($poses) === 0): ?>
      <tr><td colspan="6" class="text-center">No poses found.</td></tr>
    <?php else: ?>
      <?php foreach ($poses as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['id']) ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['category']) ?></td>
          <td><?= htmlspecialchars($p['description']) ?></td>
          <td>
            <?php if ($p['image_path'] && file_exists($p['image_path'])): ?>
              <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="Pose Image" class="pose-img" />
            <?php else: ?>
              No image
            <?php endif; ?>
          </td>
          <td>
            <a href="upload_poses.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="upload_poses.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this pose?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
