<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../pages/login.php');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$is_admin = $user['role'] === 'admin';

$event_id = $_GET['id'] ?? null;

if ($event_id) {
    try {
        // Check ownership first
        $stmt = $pdo->prepare("SELECT user_id FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();

        if (!$event) {
            header('Location: ../pages/dashboard.php?error=Event+not+found');
            exit;
        }

        // Allow delete only if admin or event owner
        if ($is_admin || $event['user_id'] == $user_id) {
            // Delete RSVPs first (optional if you have ON DELETE CASCADE)
            $stmt = $pdo->prepare("DELETE FROM rsvps WHERE event_id = ?");
            $stmt->execute([$event_id]);

            // Delete the event
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$event_id]);

            header('Location: ../pages/dashboard.php?success=Event+deleted');
            exit;
        } else {
            header('Location: ../pages/dashboard.php?error=Unauthorized+access');
            exit;
        }

    } catch (PDOException $e) {
        die("Error deleting event: " . $e->getMessage());
    }
} else {
    header('Location: ../pages/dashboard.php?error=Invalid+event+ID');
    exit;
}
