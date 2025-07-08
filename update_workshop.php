<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $time = $_POST['time'];
    $level = $_POST['level'];
    $format = $_POST['format'];

    // Update the workshop
    $stmt = $pdo->prepare("UPDATE workshops SET title = ?, subtitle = ?, time = ?, level = ?, format = ? WHERE id = ?");
    $stmt->execute([$title, $subtitle, $time, $level, $format, $id]);

    // Update each session
    if (!empty($_POST['session_ids'])) {
        $session_ids = $_POST['session_ids'];
        $session_counts = $_POST['session_counts'];
        $prices = $_POST['prices'];
        $zoom_links = $_POST['zoom_links'];

        for ($i = 0; $i < count($session_ids); $i++) {
            $sid = $session_ids[$i];
            $count = $session_counts[$i];
            $price = $prices[$i];
            $zoom = $zoom_links[$i];

            $stmt_s = $pdo->prepare("UPDATE sessions SET session_count = ?, price = ?, zoom_link = ? WHERE id = ?");
            $stmt_s->execute([$count, $price, $zoom, $sid]);
        }
    }

    // Redirect back to workshop list
    header("Location: workshop.php?success=1");
    exit();
} else {
    echo "Invalid Request.";
}
?>
