<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'db_connect.php'; // Your PDO connection

$user_id = $_SESSION['user_id'];

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $class_id = (int)($_POST['class_id'] ?? 0);

    if ($class_id > 0) {
        if ($action === 'book') {
            // Insert booking if not exists
            $stmt = $pdo->prepare("INSERT IGNORE INTO bookings (user_id, class_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $class_id]);
        } elseif ($action === 'cancel') {
            // Delete booking
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ? AND class_id = ?");
            $stmt->execute([$user_id, $class_id]);
        } elseif ($action === 'complete') {
            // Mark completed
            $stmt = $pdo->prepare("UPDATE bookings SET completed = 1 WHERE user_id = ? AND class_id = ?");
            $stmt->execute([$user_id, $class_id]);
        }
    }
    // Redirect to avoid form resubmission
    header('Location: schedule.php');
    exit();
}

// Get first and last day of current month
$firstDay = date('Y-m-01');
$lastDay = date('Y-m-t');

// Fetch classes for current month, ordered by date and time
$stmt = $pdo->prepare("SELECT * FROM yoga_classes WHERE class_date BETWEEN ? AND ? ORDER BY class_date, class_time");
$stmt->execute([$firstDay, $lastDay]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's bookings for these classes
$classIds = array_column($classes, 'id');
$bookings = [];
if ($classIds) {
    $inQuery = implode(',', array_fill(0, count($classIds), '?'));
    $stmt = $pdo->prepare("SELECT class_id, completed FROM bookings WHERE user_id = ? AND class_id IN ($inQuery)");
    $stmt->execute(array_merge([$user_id], $classIds));
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
        $bookings[$b['class_id']] = $b['completed'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Schedule - Eka Tatva Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h1 class="mb-4">Schedule for <?= date('F Y') ?></h1>

    <?php if (empty($classes)): ?>
        <div class="alert alert-info">No classes scheduled for this month.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($classes as $class): 
                $booked = array_key_exists($class['id'], $bookings);
                $completed = $booked && $bookings[$class['id']] == 1;
            ?>
            <div class="list-group-item mb-3 shadow-sm bg-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($class['class_name']) ?></h5>
                        <small>
                            <?= date('D, M j, Y', strtotime($class['class_date'])) ?> at <?= date('h:i A', strtotime($class['class_time'])) ?> |
                            Instructor: <?= htmlspecialchars($class['instructor']) ?> |
                            Duration: <?= (int)$class['duration'] ?> minutes
                        </small>
                    </div>
                    <div>
                        <?php if ($booked): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Booking</button>
                            </form>
                        <?php else: ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                <input type="hidden" name="action" value="book">
                                <button type="submit" class="btn btn-primary btn-sm">Book</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="mt-2"><?= nl2br(htmlspecialchars($class['description'])) ?></p>

                <?php if (!empty($class['video_link'])): ?>
                    <p><a href="<?= htmlspecialchars($class['video_link']) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">Watch Video</a></p>
                <?php endif; ?>

                <?php if ($booked): ?>
                    <form method="post" class="mt-2">
                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                        <input type="hidden" name="action" value="complete">
                        <button type="submit" class="btn <?= $completed ? 'btn-success' : 'btn-outline-success' ?> btn-sm" <?= $completed ? 'disabled' : '' ?>>
                            <?= $completed ? 'Completed' : 'Mark as Completed' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="home.php" class="btn btn-link mt-4">Back to Dashboard</a>
</div>

</body>
</html>
