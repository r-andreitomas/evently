<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $price = $_POST['price'];

    $stmt = $pdo->prepare("UPDATE events SET title=?, subtitle=?, event_date=?, location=?, price=? WHERE id=? AND user_id=?");
    $stmt->execute([$title, $subtitle, $event_date, $location, $price, $event_id, $user_id]);

    header("Location: ../pages/dashboard.php");
    exit;
}
?>
