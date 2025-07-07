<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Fetch events with RSVP status
$stmt = $pdo->prepare("
    SELECT events.*, users.name AS creator_name,
           (SELECT COUNT(*) FROM rsvps WHERE rsvps.event_id = events.id) AS total_rsvps,
           (SELECT COUNT(*) FROM rsvps WHERE rsvps.event_id = events.id AND rsvps.user_id = ?) AS has_rsvped
    FROM events
    JOIN users ON events.user_id = users.id
    ORDER BY event_date ASC
");
$stmt->execute([$user_id]);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Evently</title>
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
        .event-banner {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
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
                <li class="nav-item"><a class="nav-link active" href="#events">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="create_event.php">Create</a></li>
                <li class="nav-item"><a class="nav-link" href="my_tickets.php">My Tickets</a></li>
            </ul>
            <span class="text-white me-3"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)</span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- 📋 Events -->
<div class="container py-5" id="events">
    <h2 class="mb-4">All Events</h2>

    <?php foreach ($events as $event): ?>
        <div class="card mb-4 shadow-sm">
            <?php if ($event['banner']): ?>
                <img src="../uploads/<?= htmlspecialchars($event['banner']) ?>" class="event-banner" alt="Banner">
            <?php endif; ?>

            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($event['subtitle']) ?></h6>
                <p class="mb-1">By: <?= htmlspecialchars($event['creator_name']) ?></p>
                <p class="mb-1">Date: <?= date("F j, Y, g:i a", strtotime($event['event_date'])) ?></p>
                <p class="mb-1">Location: <?= htmlspecialchars($event['location']) ?></p>
                <p class="mb-1">Price: ₱<?= number_format($event['price'], 2) ?></p>

                <!-- RSVP Progress -->
                <?php if ($event['max_attendees']): ?>
                    <div class="mb-2">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= ($event['total_rsvps'] / $event['max_attendees']) * 100 ?>%;">
                                <?= $event['total_rsvps'] ?> / <?= $event['max_attendees'] ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="mb-2">RSVPs: <?= $event['total_rsvps'] ?></p>
                <?php endif; ?>

                <!-- RSVP Status -->
                <?php if ($event['has_rsvped']): ?>
                    <span class="badge bg-success mb-2">RSVPed ✅</span>
                <?php elseif ($event['max_attendees'] && $event['total_rsvps'] >= $event['max_attendees']): ?>
                    <span class="badge bg-secondary mb-2">RSVP Full ❌</span>
                <?php else: ?>
                    <form method="POST" action="../actions/rsvp_action.php" class="d-inline">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">RSVP</button>
                    </form>
                <?php endif; ?>

                <!-- Edit/Delete Access -->
                <?php if ($event['user_id'] == $user_id || $user['role'] === 'admin'): ?>
                    <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-warning btn-sm ms-2">Edit</a>
                    <a href="../actions/delete_event_action.php?id=<?= $event['id'] ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                <?php endif; ?>

                <!-- ADMIN RSVP LIST -->
                <?php if ($user['role'] === 'admin'): ?>
                    <details class="mt-3">
                        <summary><strong>See RSVPs</strong></summary>
                        <?php
                            $rsvp_stmt = $pdo->prepare("
                                SELECT r.id AS rsvp_id, u.name, u.email, r.rsvp_date
                                FROM rsvps r
                                JOIN users u ON r.user_id = u.id
                                WHERE r.event_id = ?
                            ");
                            $rsvp_stmt->execute([$event['id']]);
                            $rsvps = $rsvp_stmt->fetchAll();
                        ?>

                        <?php if ($rsvps): ?>
                            <ul class="mt-2 list-group">
                                <?php foreach ($rsvps as $rsvp): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <?= htmlspecialchars($rsvp['name']) ?> (<?= htmlspecialchars($rsvp['email']) ?>)
                                            <small class="text-muted">— <?= date("F j, Y, g:i a", strtotime($rsvp['rsvp_date'])) ?></small>
                                        </div>
                                        <form method="POST" action="../actions/delete_rsvp_action.php" onsubmit="return confirm('Remove RSVP?')">
                                            <input type="hidden" name="rsvp_id" value="<?= $rsvp['rsvp_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="mt-2 text-muted">No RSVPs yet.</p>
                        <?php endif; ?>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
