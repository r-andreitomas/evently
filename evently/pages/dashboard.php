<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Fetch events with Reserve status
$stmt = $pdo->prepare("
    SELECT events.*, users.name AS creator_name,
           (SELECT COUNT(*) FROM rsvps WHERE rsvps.event_id = events.id) AS total_Reserve,
           (SELECT COUNT(*) FROM rsvps WHERE rsvps.event_id = events.id AND rsvps.user_id = ?) AS has_Reserve
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

<?php include '../includes/header.php'; ?>

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

                <!-- Reserve Progress -->
                <?php if ($event['max_attendees']): ?>
                    <div class="mb-2">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= ($event['total_Reserve'] / $event['max_attendees']) * 100 ?>%;">
                                <?= $event['total_Reserve'] ?> / <?= $event['max_attendees'] ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="mb-2">Reserve: <?= $event['total_Reserve'] ?></p>
                <?php endif; ?>

                <!-- Reserve Status -->
                <?php if ($event['has_Reserve']): ?>
                    <span class="badge bg-success mb-2">Reserve ✅</span>
                <?php elseif ($event['max_attendees'] && $event['total_Reserve'] >= $event['max_attendees']): ?>
                    <span class="badge bg-secondary mb-2">Reserve Full ❌</span>
                <?php else: ?>
                    <form method="POST" action="../actions/reserve_action.php" class="d-inline">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Reserve</button>
                    </form>
                <?php endif; ?>

                <!-- Edit/Delete Access -->
                <?php if ($event['user_id'] == $user_id || $user['role'] === 'admin'): ?>
                    <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-warning btn-sm ms-2">Edit</a>
                    <a href="../actions/delete_event_action.php?id=<?= $event['id'] ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                <?php endif; ?>

                <!-- ADMIN Reserve LIST -->
                <?php if ($user['role'] === 'admin'): ?>
                    <details class="mt-3">
                        <summary><strong>See Reserve</strong></summary>
                        <?php
                            $Reserve_stmt = $pdo->prepare("
                                SELECT r.id AS Reserve_id, u.name, u.email, r.Reserve_date
                                FROM Reserve r
                                JOIN users u ON r.user_id = u.id
                                WHERE r.event_id = ?
                            ");
                            $Reserve_stmt->execute([$event['id']]);
                            $Reserve = $Reserve_stmt->fetchAll();
                        ?>

                        <?php if ($Reserve): ?>
                            <ul class="mt-2 list-group">
                                <?php foreach ($Reserve as $Reserve): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <?= htmlspecialchars($Reserve['name']) ?> (<?= htmlspecialchars($Reserve['email']) ?>)
                                            <small class="text-muted">— <?= date("F j, Y, g:i a", strtotime($Reserve['Reserve_date'])) ?></small>
                                        </div>
                                        <form method="POST" action="../actions/delete_reserve_action.php" onsubmit="return confirm('Remove Reservation?')">
                                            <input type="hidden" name="Reserve_id" value="<?= $Reserve['Reserve_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="mt-2 text-muted">No Reservation Yet.</p>
                        <?php endif; ?>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>