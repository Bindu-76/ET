<?php
include 'db_connect.php';

// Fetch all workshops and their sessions
$workshops = [];
$stmt = $pdo->query("SELECT * FROM workshops ORDER BY id DESC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $w) {
    $w_id = $w['id'];
    $stmt_sessions = $pdo->prepare("SELECT * FROM sessions WHERE workshop_id = ?");
    $stmt_sessions->execute([$w_id]);
    $sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);
    $w['sessions'] = $sessions;
    $workshops[] = $w;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshops | Eka Tatva Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4 text-center text-primary">Explore Our Yoga Workshops</h1>

        <?php if (count($workshops) === 0): ?>
            <p class="text-muted">No workshops available at the moment. Please check back later!</p>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($workshops as $w): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-primary">
                        <div class="card-body">
                            <h4 class="card-title text-primary"><?= htmlspecialchars($w['title']) ?></h4>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($w['subtitle']) ?></h6>
                            <p class="mb-1"><strong>Time:</strong> <?= htmlspecialchars($w['time']) ?></p>
                            <p class="mb-1"><strong>Level:</strong> <?= htmlspecialchars($w['level']) ?></p>
                            <p class="mb-3"><strong>Format:</strong> <?= htmlspecialchars($w['format']) ?></p>

                            <?php if (count($w['sessions']) > 0): ?>
                                <h6 class="text-secondary">Sessions:</h6>
                                <ul class="list-group mb-3">
                                    <?php foreach ($w['sessions'] as $s): ?>
                                        <li class="list-group-item">
                                            <strong>Sessions:</strong> <?= $s['session_count'] ?> |
                                            <strong>Price:</strong> ₹<?= $s['price'] ?> <br>
                                            <a href="<?= htmlspecialchars($s['zoom_link']) ?>" target="_blank" class="btn btn-sm btn-outline-success mt-2">Join via Zoom</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">No sessions available yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center text-muted py-4">
        &copy; <?= date('Y') ?> Eka Tatva Wellness. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>