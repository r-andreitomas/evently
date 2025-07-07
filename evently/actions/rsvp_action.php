<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id  = $_SESSION['user']['id'];
    $event_id = $_POST['event_id'];

    // Check if already RSVPed
    $check = $pdo->prepare("SELECT * FROM rsvps WHERE user_id = ? AND event_id = ?");
    $check->execute([$user_id, $event_id]);
    if ($check->rowCount() > 0) {
        header("Location: ../pages/dashboard.php?error=already_rsvped");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO rsvps (event_id, user_id) VALUES (?, ?)");
    $stmt->execute([$event_id, $user_id]);

    header("Location: ../pages/dashboard.php?success=rsvped");
    exit;
}
?>
