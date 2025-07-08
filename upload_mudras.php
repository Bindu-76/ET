<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$success = '';
$error = '';
$edit_id = null;
$mudra = ['name' => '', 'category' => '', 'description' => '', 'image_path' => ''];

// Handle Edit request
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM mudras WHERE id = ?");
    $stmt->execute([$edit_id]);
    $mudra = $stmt->fetch();
    if (!$mudra) {
        $error = "Mudra not found.";
        $edit_id = null;
    }
}

// Handle Delete request
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    // First get the image to delete it from disk
    $stmt = $pdo->prepare("SELECT image_path FROM mudras WHERE id = ?");
    $stmt->execute([$delete_id]);
    $row = $stmt->fetch();
    if ($row) {
        if ($row['image_path'] && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM mudras WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = "Mudra deleted successfully.";
    } else {
        $error = "Mudra not found.";
    }
}

// Handle Add/Edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $edit_id_post = isset($_POST['edit_id']) && is_numeric($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (empty($name)) {
        $error = "Name is required.";
    } else {
        $image_path = null;

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = basename($_FILES['image']['name']);
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_types)) {
                $error = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error = "Error uploading image.";
            } else {
                $targetDir = "uploads/mudras/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $newFilename = time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
                $targetFile = $targetDir . $newFilename;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $error = "Failed to move uploaded image.";
                } else {
                    $image_path = $targetFile;
                }
            }
        }

        if (!$error) {
            try {
                if ($edit_id_post) {
                    // Update existing mudra
                    if ($image_path) {
                        // Get old image to delete
                        $stmt = $pdo->prepare("SELECT image_path FROM mudras WHERE id = ?");
                        $stmt->execute([$edit_id_post]);
                        $old = $stmt->fetch();
                        if ($old && $old['image_path'] && file_exists($old['image_path'])) {
                            unlink($old['image_path']);
                        }

                        $stmt = $pdo->prepare("UPDATE mudras SET name = ?, category = ?, description = ?, image_path = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$name, $category, $description, $image_path, $edit_id_post]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE mudras SET name = ?, category = ?, description = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$name, $category, $description, $edit_id_post]);
                    }
                    $success = "Mudra updated successfully.";
                    $edit_id = null;
                } else {
                    // Insert new mudra
                    if (!$image_path) {
                        $error = "Please upload an image.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO mudras (name, category, description, image_path, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([$name, $category, $description, $image_path]);
                        $success = "Mudra added successfully.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
    }
}

// Fetch all mudras for listing
$stmt = $pdo->query("SELECT * FROM mudras ORDER BY created_at DESC");
$mudras = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Mudras</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .mudra-img {
      width: 100px;
      height: 70px;
      object-fit: cover;
      border-radius: 6px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Upload and Manage Mudras</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="mb-5">
    <input type="hidden" name="edit_id" value="<?= $edit_id ?? '' ?>" />
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Mudra Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($mudra['name'] ?? '') ?>" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($mudra['category'] ?? '') ?>" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Image <?= $edit_id ? '(Upload new to replace)' : '<span class="text-danger">*</span>' ?></label>
        <input type="file" name="image" accept="image/*" class="form-control" <?= $edit_id ? '' : 'required' ?> />
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($mudra['description'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary"><?= $edit_id ? 'Update Mudra' : 'Add Mudra' ?></button>
        <?php if ($edit_id): ?>
          <a href="upload_mudras.php" class="btn btn-secondary ms-2">Cancel</a>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <h3>Existing Mudras</h3>
  <?php if (count($mudras) === 0): ?>
    <p>No mudras found.</p>
  <?php else: ?>
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Description</th>
          <th>Image</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th style="width:130px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mudras as $m): ?>
          <tr>
            <td><?= $m['id'] ?></td>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= htmlspecialchars($m['category']) ?></td>
            <td><?= nl2br(htmlspecialchars($m['description'])) ?></td>
            <td>
              <?php if ($m['image_path'] && file_exists($m['image_path'])): ?>
                <img src="<?= htmlspecialchars($m['image_path']) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="mudra-img" />
              <?php else: ?>
                <span class="text-muted">No image</span>
              <?php endif; ?>
            </td>
            <td><?= $m['created_at'] ?></td>
            <td><?= $m['updated_at'] ?></td>
            <td>
              <a href="upload_mudras.php?edit=<?= $m['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
              <a href="upload_mudras.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure to delete this mudra?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
