<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = $_POST['title'];
    $subtitle      = $_POST['subtitle'];
    $event_date    = $_POST['event_date'];
    $location      = $_POST['location'];
    $price         = $_POST['price'];
    $max_attendees = $_POST['max_attendees'];

    if ($title && $event_date && $location) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO events (title, subtitle, event_date, location, price, max_attendees, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $subtitle,
                $event_date,
                $location,
                $price,
                $max_attendees ?: null,
                $user['id']
            ]);

            header("Location: dashboard.php?success=Event+created+successfully");
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dongle:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Montserrat', sans-serif;
        }
        .form-container {
            max-width: 600px;
        }
        .evently-logo {
            font-family: 'Dongle', sans-serif;
            font-size: 42px;
            line-height: 1;
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- 📝 Form -->
<div class="container py-5">
    <h2 class="mb-4">Create New Event</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-container bg-white p-4 shadow rounded">
        <div class="mb-3">
            <label for="title" class="form-label">Event Title *</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="mb-3">
            <label for="subtitle" class="form-label">Subtitle</label>
            <input type="text" class="form-control" id="subtitle" name="subtitle">
        </div>

        <div class="mb-3">
            <label for="event_date" class="form-label">Date & Time *</label>
            <input type="datetime-local" class="form-control" id="event_date" name="event_date" required>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location *</label>
            <input type="text" class="form-control" id="location" name="location" required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price (₱)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="0.00">
        </div>

        <div class="mb-3">
            <label for="max_attendees" class="form-label">Max Attendees</label>
            <input type="number" class="form-control" id="max_attendees" name="max_attendees" min="1">
        </div>

        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>
</div>

</body>
</html>