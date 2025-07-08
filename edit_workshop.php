<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    die('Workshop ID is required.');
}

$workshop_id = $_GET['id'];

// Fetch workshop
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$workshop_id]);
$workshop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$workshop) {
    die('Workshop not found.');
}

// Fetch sessions
$stmt_sess = $pdo->prepare("SELECT * FROM sessions WHERE workshop_id = ?");
$stmt_sess->execute([$workshop_id]);
$sessions = $stmt_sess->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Workshop | Eka Tatva Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4 text-primary">Edit Workshop</h2>

        <form action="update_workshop.php" method="POST">
            <input type="hidden" name="id" value="<?= $workshop['id'] ?>">

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($workshop['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($workshop['subtitle']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Time</label>
                <input type="text" name="time" class="form-control" value="<?= htmlspecialchars($workshop['time']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Level</label>
                <input type="text" name="level" class="form-control" value="<?= htmlspecialchars($workshop['level']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Format</label>
                <input type="text" name="format" class="form-control" value="<?= htmlspecialchars($workshop['format']) ?>">
            </div>

            <h5 class="text-secondary mt-4">Sessions</h5>
            <?php foreach ($sessions as $index => $s): ?>
                <div class="border p-3 mb-3 rounded bg-white">
                    <input type="hidden" name="session_ids[]" value="<?= $s['id'] ?>">

                    <div class="mb-2">
                        <label>Session Count</label>
                        <input type="text" name="session_counts[]" class="form-control" value="<?= $s['session_count'] ?>">
                    </div>

                    <div class="mb-2">
                        <label>Price (₹)</label>
                        <input type="number" name="prices[]" class="form-control" value="<?= $s['price'] ?>">
                    </div>

                    <div class="mb-2">
                        <label>Zoom Link</label>
                        <input type="text" name="zoom_links[]" class="form-control" value="<?= $s['zoom_link'] ?>">
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary">Update Workshop</button>
            <a href="workshop.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
