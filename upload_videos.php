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

    // Get video link before deleting to remove local file if any
    $stmt = $pdo->prepare("SELECT video_link FROM class_videos WHERE id = ?");
    $stmt->execute([$deleteId]);
    $video = $stmt->fetch();
    if ($video) {
        // Delete local file if exists and is not a URL
        if (!preg_match('/^https?:\/\//', $video['video_link']) && file_exists($video['video_link'])) {
            unlink($video['video_link']);
        }

        $delStmt = $pdo->prepare("DELETE FROM class_videos WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $success = "Video deleted successfully.";
    } else {
        $error = "Video not found.";
    }
}

// Handle Edit form load
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM class_videos WHERE id = ?");
    $stmt->execute([$editId]);
    $video = $stmt->fetch();
    if ($video) {
        $editing = true;
    } else {
        $error = "Video not found for editing.";
    }
}

// Handle POST (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $instructor = trim($_POST['instructor']);
    $description = trim($_POST['description']);
    $video_url_input = trim($_POST['video_url'] ?? '');
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;
    $editing = $editId ? true : false;

    // Validate title and instructor
    if (empty($title) || empty($instructor)) {
        $error = "Title and Instructor are required.";
    } else {
        // Video file upload handling
        $videoPath = null;
        $newVideoUploaded = false;

        if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorCode = $_FILES['video_file']['error'];
            if ($errorCode === UPLOAD_ERR_OK) {
                $targetDir = "uploads/videos/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                $filename = basename($_FILES["video_file"]["name"]);
                $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowed = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

                if (!in_array($fileType, $allowed)) {
                    $error = "Invalid file type for upload. Allowed: " . implode(", ", $allowed);
                } else {
                    $targetFile = $targetDir . time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);
                    if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $targetFile)) {
                        $videoPath = $targetFile;
                        $newVideoUploaded = true;
                    } else {
                        $error = "Failed to move uploaded file.";
                    }
                }
            } else {
                $error = "Error uploading video file.";
            }
        }

        // Validate video URL if no file uploaded
        $videoUrl = null;
        if (!$newVideoUploaded && !empty($video_url_input)) {
            // Validate YouTube or Instagram URL
            if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|instagram\.com)\/.+$/i', $video_url_input)) {
                $error = "Please enter a valid YouTube or Instagram video URL.";
            } else {
                $videoUrl = $video_url_input;
            }
        }

        // If neither file nor valid URL provided and not editing existing video without change
        if (!$newVideoUploaded && !$videoUrl) {
            if ($editing) {
                // If editing and no new video or URL, keep old video_link
                $stmt = $pdo->prepare("SELECT video_link FROM class_videos WHERE id = ?");
                $stmt->execute([$editId]);
                $oldVideo = $stmt->fetchColumn();
                if (!$oldVideo) {
                    $error = "Existing video file/link not found.";
                }
            } else {
                $error = "Please upload a video file or enter a video URL.";
            }
        }

        if (!$error) {
            // Insert or Update DB
            try {
                if ($editing) {
                    // For editing, if new video uploaded or URL provided, update video_link
                    if ($newVideoUploaded) {
                        // Delete old file if local file
                        $stmt = $pdo->prepare("SELECT video_link FROM class_videos WHERE id = ?");
                        $stmt->execute([$editId]);
                        $oldVideoLink = $stmt->fetchColumn();
                        if ($oldVideoLink && !preg_match('/^https?:\/\//', $oldVideoLink) && file_exists($oldVideoLink)) {
                            unlink($oldVideoLink);
                        }
                        $video_link = $videoPath;
                    } elseif ($videoUrl) {
                        // URL provided
                        $stmt = $pdo->prepare("SELECT video_link FROM class_videos WHERE id = ?");
                        $stmt->execute([$editId]);
                        $oldVideoLink = $stmt->fetchColumn();
                        if ($oldVideoLink && !preg_match('/^https?:\/\//', $oldVideoLink) && file_exists($oldVideoLink)) {
                            unlink($oldVideoLink);
                        }
                        $video_link = $videoUrl;
                    } else {
                        // No change to video_link
                        $stmt = $pdo->prepare("SELECT video_link FROM class_videos WHERE id = ?");
                        $stmt->execute([$editId]);
                        $video_link = $stmt->fetchColumn();
                    }

                    $stmt = $pdo->prepare("UPDATE class_videos SET title = ?, instructor = ?, description = ?, video_link = ? WHERE id = ?");
                    $stmt->execute([$title, $instructor, $description, $video_link, $editId]);
                    $success = "Video updated successfully!";
                    $editing = false;
                    $editId = null;
                } else {
                    // Insert new video
                    $stmt = $pdo->prepare("INSERT INTO class_videos (title, instructor, description, video_link) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $instructor, $description, $videoPath ?? $videoUrl]);
                    $success = "Video uploaded successfully!";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                // Delete uploaded file if any
                if ($newVideoUploaded && file_exists($videoPath)) unlink($videoPath);
            }
        }
    }
}

// Fetch all videos for display
$videos = $pdo->query("SELECT * FROM class_videos ORDER BY created_at DESC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Yoga Videos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
.video-preview {
    max-width: 320px;
    max-height: 180px;
}
</style>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4"><?= $editing ? "Edit Yoga Video" : "Upload Yoga Video" ?></h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="edit_id" value="<?= htmlspecialchars($editId) ?>" />
    <div class="mb-3">
      <label class="form-label">Title *</label>
      <input type="text" name="title" class="form-control" required value="<?= $editing ? htmlspecialchars($video['title']) : '' ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">Instructor *</label>
      <input type="text" name="instructor" class="form-control" required value="<?= $editing ? htmlspecialchars($video['instructor']) : '' ?>" />
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"><?= $editing ? htmlspecialchars($video['description']) : '' ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Upload Video File <?= $editing ? '(Leave blank to keep current video)' : '*' ?></label>
      <input type="file" name="video_file" accept="video/*" class="form-control" />
    </div>
    <div class="mb-3">
      <label class="form-label">Or enter YouTube / Instagram Video URL <?= $editing ? '(Leave blank to keep current video)' : '' ?></label>
      <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." value="<?= $editing ? htmlspecialchars($video['video_link']) : '' ?>" />
    </div>
    <button type="submit" class="btn btn-success"><?= $editing ? "Update Video" : "Upload Video" ?></button>
    <?php if ($editing): ?>
        <a href="upload_videos.php" class="btn btn-secondary ms-2">Cancel Edit</a>
    <?php endif; ?>
  </form>

  <hr class="my-5" />

  <h3>All Videos</h3>

  <?php if (count($videos) === 0): ?>
    <p>No videos uploaded yet.</p>
  <?php else: ?>
    <div class="row gy-4">
      <?php foreach ($videos as $vid): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($vid['title']) ?></h5>
              <h6 class="card-subtitle mb-2 text-muted">Instructor: <?= htmlspecialchars($vid['instructor']) ?></h6>
              <p class="card-text"><?= nl2br(htmlspecialchars($vid['description'])) ?></p>

              <div class="mb-3">
                <?php if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|instagram\.com)\/.+$/i', $vid['video_link'])): ?>
                  <!-- Embed video from URL -->
                  <div class="ratio ratio-16x9">
                    <iframe src="<?= htmlspecialchars($vid['video_link']) ?>" frameborder="0" allowfullscreen></iframe>
                  </div>
                <?php else: ?>
                  <!-- Local video file -->
                  <video class="video-preview" controls>
                    <source src="<?= htmlspecialchars($vid['video_link']) ?>" type="video/mp4" />
                    Your browser does not support the video tag.
                  </video>
                <?php endif; ?>
              </div>

              <a href="?edit=<?= $vid['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
              <a href="?delete=<?= $vid['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this video?');">Delete</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
