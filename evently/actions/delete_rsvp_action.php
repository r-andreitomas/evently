<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../pages/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rsvp_id'])) {
    $rsvp_id = $_POST['rsvp_id'];

    $stmt = $pdo->prepare("DELETE FROM rsvps WHERE id = ?");
    $stmt->execute([$rsvp_id]);
}

header("Location: ../pages/dashboard.php?success=RSVP+deleted");
exit;
