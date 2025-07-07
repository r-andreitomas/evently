<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Fetch RSVPed events
$stmt = $pdo->prepare("
    SELECT events.*, users.name AS creator_name, rsvps.rsvp_date
    FROM rsvps
    JOIN events ON rsvps.event_id = events.id
    JOIN users ON events.user_id = users.id
    WHERE rsvps.user_id = ?
    ORDER BY events.event_date ASC
");
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Tickets - Evently</title>
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

<!-- 🔝 Navbar with "My Tickets" Active -->
<nav class="navbar navbar-expand-lg bg-primary navbar-dark py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand evently-logo" href="../index.php">Evently</a>

        <!-- Nav and User -->
        <div class="d-flex align-items-center">
            <ul class="navbar-nav me-3">
                <li class="nav-item"><a class="nav-link" href="dashboard.php#events">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="create_event.php">Create</a></li>
                <li class="nav-item"><a class="nav-link active" href="my_tickets.php">My Tickets</a></li>
            </ul>

            <?php if ($user): ?>
                <span class="text-white me-3"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)</span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light btn-sm me-2">Sign In</a>
                <a href="register.php" class="btn btn-light btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- 🎟 My Tickets Section -->
<div class="container py-5">
    <h2 class="mb-4">My Tickets</h2>

    <?php if ($tickets): ?>
        <?php foreach ($tickets as $event): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($event['subtitle']) ?></h6>
                    <p class="mb-1">By: <?= htmlspecialchars($event['creator_name']) ?></p>
                    <p class="mb-1">Date: <?= date("F j, Y, g:i a", strtotime($event['event_date'])) ?></p>
                    <p class="mb-1">Location: <?= htmlspecialchars($event['location']) ?></p>
                    <p class="mb-1">Price: ₱<?= number_format($event['price'], 2) ?></p>
                    <p class="mb-1 text-success">RSVP Date: <?= date("F j, Y, g:i a", strtotime($event['rsvp_date'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">You haven't RSVP’d to any events yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
