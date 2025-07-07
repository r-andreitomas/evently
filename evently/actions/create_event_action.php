<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id       = $_SESSION['user']['id'];
    $title         = $_POST['title'];
    $subtitle      = $_POST['subtitle'];
    $event_date    = $_POST['event_date'];
    $location      = $_POST['location'];
    $price         = $_POST['price'];
    $max_attendees = $_POST['max_attendees'];

    if ($title && $event_date && $location) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO events (user_id, title, subtitle, event_date, location, price, max_attendees)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $title,
                $subtitle,
                $event_date,
                $location,
                $price,
                $max_attendees ?: null
            ]);

            header("Location: ../pages/dashboard.php?success=Event+created+successfully");
            exit;
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    } else {
        die("Please fill in all required fields.");
    }
}
?>
