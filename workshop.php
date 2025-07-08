<?php
include 'db_connect.php';

$errors = [];
$success = "";

// Handle add/edit/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_workshop') {
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $time = trim($_POST['time'] ?? '');
            $level = trim($_POST['level'] ?? '');
            $format = trim($_POST['format'] ?? '');
            $image = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $img_tmp = $_FILES['image']['tmp_name'];
                $img_name = uniqid('workshop_') . '_' . basename($_FILES['image']['name']);
                $target = 'uploads/' . $img_name;

                if (move_uploaded_file($img_tmp, $target)) {
                    $image = $img_name;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }

            $stmt = $pdo->prepare("INSERT INTO workshops (title, subtitle, time, level, format, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $time, $level, $format, $image]);
            $success = "Workshop added successfully.";
        }

        if ($action === 'edit_workshop') {
            $id = $_POST['id'] ?? 0;
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $time = trim($_POST['time'] ?? '');
            $level = trim($_POST['level'] ?? '');
            $format = trim($_POST['format'] ?? '');

            $stmt = $pdo->prepare("UPDATE workshops SET title = ?, subtitle = ?, time = ?, level = ?, format = ? WHERE id = ?");
            $stmt->execute([$title, $subtitle, $time, $level, $format, $id]);
            $success = "Workshop updated.";
        }

        if ($action === 'delete_workshop') {
            $id = $_POST['id'] ?? 0;
            $pdo->prepare("DELETE FROM sessions WHERE workshop_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM workshops WHERE id = ?")->execute([$id]);
            $success = "Workshop deleted.";
        }

        if ($action === 'add_session') {
            $workshop_id = $_POST['workshop_id'] ?? 0;
            $session_count = $_POST['session_count'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $zoom_link = trim($_POST['zoom_link'] ?? '');

            $stmt = $pdo->prepare("INSERT INTO sessions (workshop_id, session_count, price, zoom_link) VALUES (?, ?, ?, ?)");
            $stmt->execute([$workshop_id, $session_count, $price, $zoom_link]);
            $success = "Session added.";
        }

        if ($action === 'edit_session') {
            $id = $_POST['session_id'] ?? 0;
            $session_count = $_POST['session_count'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $zoom_link = trim($_POST['zoom_link'] ?? '');

            $stmt = $pdo->prepare("UPDATE sessions SET session_count = ?, price = ?, zoom_link = ? WHERE id = ?");
            $stmt->execute([$session_count, $price, $zoom_link, $id]);
            $success = "Session updated.";
        }

        if ($action === 'delete_session') {
            $id = $_POST['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Session deleted.";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}

$workshops = [];
$stmt = $pdo->query("SELECT * FROM workshops ORDER BY id DESC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $w) {
    $w_id = $w['id'];
    $stmt_sessions = $pdo->prepare("SELECT * FROM sessions WHERE workshop_id = ?");
    $stmt_sessions->execute([$w_id]);
    $w['sessions'] = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);
    $workshops[] = $w;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Workshops</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Admin Panel: Workshops</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endforeach; ?>

    <form method="POST" class="mb-4 p-3 border rounded bg-light" enctype="multipart/form-data">
        <h4>Add Workshop</h4>
        <input type="hidden" name="action" value="add_workshop" />
        <div class="row g-2">
            <div class="col-md-2"><input name="title" class="form-control" placeholder="Title" required></div>
            <div class="col-md-2"><input name="subtitle" class="form-control" placeholder="Subtitle"></div>
            <div class="col-md-2"><input name="time" class="form-control" placeholder="Time"></div>
            <div class="col-md-2"><input name="level" class="form-control" placeholder="Level"></div>
            <div class="col-md-2"><input name="format" class="form-control" placeholder="Format"></div>
            <div class="col-md-2"><input type="file" name="image" class="form-control" accept="image/*"></div>
            <div class="col-md-12 mt-2"><button class="btn btn-primary w-100">Add</button></div>
        </div>
    </form>

    <?php foreach ($workshops as $workshop): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= htmlspecialchars($workshop['title']) ?></strong>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="collapse" data-bs-target="#editWorkshop<?= $workshop['id'] ?>">Edit</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete_workshop" />
                        <input type="hidden" name="id" value="<?= $workshop['id'] ?>" />
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this workshop?')">Delete</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($workshop['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($workshop['image']) ?>" class="img-fluid mb-3 rounded" style="max-height:200px;">
                <?php endif; ?>

                <p><strong>Subtitle:</strong> <?= $workshop['subtitle'] ?> | <strong>Time:</strong> <?= $workshop['time'] ?> | <strong>Level:</strong> <?= $workshop['level'] ?> | <strong>Format:</strong> <?= $workshop['format'] ?></p>

                <div class="collapse mb-3" id="editWorkshop<?= $workshop['id'] ?>">
                    <form method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="action" value="edit_workshop" />
                        <input type="hidden" name="id" value="<?= $workshop['id'] ?>" />
                        <div class="row g-2">
                            <div class="col-md-2"><input name="title" class="form-control" value="<?= $workshop['title'] ?>" required></div>
                            <div class="col-md-2"><input name="subtitle" class="form-control" value="<?= $workshop['subtitle'] ?>"></div>
                            <div class="col-md-2"><input name="time" class="form-control" value="<?= $workshop['time'] ?>"></div>
                            <div class="col-md-2"><input name="level" class="form-control" value="<?= $workshop['level'] ?>"></div>
                            <div class="col-md-2"><input name="format" class="form-control" value="<?= $workshop['format'] ?>"></div>
                            <div class="col-md-2"><button class="btn btn-success w-100">Update</button></div>
                        </div>
                    </form>
                </div>

                <form method="POST" class="row g-2 mb-3">
                    <input type="hidden" name="action" value="add_session" />
                    <input type="hidden" name="workshop_id" value="<?= $workshop['id'] ?>" />
                    <div class="col-md-2"><input name="session_count" class="form-control" placeholder="Sessions" required></div>
                    <div class="col-md-2"><input name="price" class="form-control" placeholder="Price" required></div>
                    <div class="col-md-4"><input name="zoom_link" class="form-control" placeholder="Zoom Link" required></div>
                    <div class="col-md-2"><button class="btn btn-success w-100">Add Session</button></div>
                </form>

                <ul class="list-group">
                    <?php foreach ($workshop['sessions'] as $session): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Sessions: <?= $session['session_count'] ?> | Price: ₹<?= $session['price'] ?> | <a href="<?= $session['zoom_link'] ?>" target="_blank">Zoom</a>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="collapse" data-bs-target="#editSession<?= $session['id'] ?>">Edit</button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete_session" />
                                    <input type="hidden" name="id" value="<?= $session['id'] ?>" />
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this session?')">Delete</button>
                                </form>
                            </div>
                        </li>

                        <div class="collapse mt-2" id="editSession<?= $session['id'] ?>">
                            <form method="POST" class="p-3 bg-light rounded border">
                                <input type="hidden" name="action" value="edit_session" />
                                <input type="hidden" name="session_id" value="<?= $session['id'] ?>" />
                                <div class="row g-2">
                                    <div class="col-md-3"><input name="session_count" class="form-control" value="<?= $session['session_count'] ?>" placeholder="Sessions" required></div>
                                    <div class="col-md-3"><input name="price" class="form-control" value="<?= $session['price'] ?>" placeholder="Price" required></div>
                                    <div class="col-md-4"><input name="zoom_link" class="form-control" value="<?= $session['zoom_link'] ?>" placeholder="Zoom Link" required></div>
                                    <div class="col-md-2"><button class="btn btn-success w-100">Update</button></div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
