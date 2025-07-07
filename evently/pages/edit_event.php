<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

$event_id = $_GET['id'] ?? null;
if (!$event_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch the existing event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Event not found or you don't have permission to edit this event.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $max_attendees = $_POST['max_attendees'] ?: null;

    $banner_name = $event['banner'];

    if (!empty($_FILES['banner']['name'])) {
        $upload_dir = '../uploads/';
        $banner_name = time() . '_' . basename($_FILES['banner']['name']);
        $target_path = $upload_dir . $banner_name;

        if (!move_uploaded_file($_FILES['banner']['tmp_name'], $target_path)) {
            $error = "Failed to upload new banner.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare("
            UPDATE events
            SET title = ?, subtitle = ?, event_date = ?, location = ?, price = ?, max_attendees = ?, banner = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $title,
            $subtitle,
            $event_date,
            $location,
            $price,
            $max_attendees,
            $banner_name,
            $event_id,
            $user_id
        ]);

        header("Location: dashboard.php?success=Event+updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dongle:wght@700&family=Montserrat&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Montserrat', sans-serif;
        }
        .evently-logo {
            font-family: 'Dongle', sans-serif;
            font-size: 42px;
            line-height: 1;
        }
    </style>
</head>
<body>

<!-- 🔝 Navbar -->
<nav class="navbar navbar-expand-lg bg-primary navbar-dark py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand evently-logo" href="../index.php">Evently</a>
        <div class="d-flex align-items-center">
            <ul class="navbar-nav me-3">
                <li class="nav-item"><a class="nav-link" href="dashboard.php#events">Events</a></li>
                <li class="nav-item"><a class="nav-link active" href="create_event.php">Create</a></li>
                <li class="nav-item"><a class="nav-link" href="my_tickets.php">My Tickets</a></li>
            </ul>
            <span class="text-white me-3"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)</span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- ✏️ Edit Form -->
<div class="container py-5">
    <h2 class="mb-4">Edit Event</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow" style="max-width: 700px;">
        <div class="mb-3">
            <label for="title" class="form-label">Event Title *</label>
            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="subtitle" class="form-label">Subtitle</label>
            <input type="text" class="form-control" name="subtitle" value="<?= htmlspecialchars($event['subtitle']) ?>">
        </div>

        <div class="mb-3">
            <label for="event_date" class="form-label">Date & Time *</label>
            <input type="datetime-local" class="form-control" name="event_date" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" required>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location *</label>
            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($event['location']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price (₱)</label>
            <input type="number" step="0.01" min="0" class="form-control" name="price" value="<?= htmlspecialchars($event['price']) ?>">
        </div>

        <div class="mb-3">
            <label for="max_attendees" class="form-label">Max Attendees</label>
            <input type="number" min="0" class="form-control" name="max_attendees" value="<?= htmlspecialchars($event['max_attendees']) ?>">
        </div>

        <div class="mb-3">
            <label for="banner" class="form-label">Change Banner (optional)</label>
            <input type="file" class="form-control" name="banner" accept="image/*">
            <?php if ($event['banner']): ?>
                <small class="text-muted">Current: <?= htmlspecialchars($event['banner']) ?></small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Update Event</button>
    </form>
</div>

</body>
</html>
