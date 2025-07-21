<?php
require_once '../includes/session.php'; 
require_once '../config/database.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'] ?? null; 

if ($user_id === null) {
    $_SESSION['error_message'] = "User ID not found in session. Please log in again.";
    header("Location: login.php");
    exit();
}

$my_reservations = [];
$error_message = '';
$success_message = '';

try {
    $stmt = $pdo->prepare("
        SELECT events.*, rsvps.id AS rsvp_id
        FROM events
        JOIN rsvps ON events.id = rsvps.event_id
        WHERE rsvps.user_id = ?
        ORDER BY events.event_date ASC
    ");
    $stmt->execute([$user_id]);
    $my_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error fetching user's reservations: " . $e->getMessage());
    $error_message = "Database Error: Could not load your reservations. " . htmlspecialchars($e->getMessage());
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$display_username_in_header = $_SESSION['user']['name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Dongle:wght@700&family=Montserrat&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Montserrat', sans-serif;
        }
        .event-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .event-card-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .event-card-body {
            padding: 15px;
        }
        .event-card-title {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .event-card-subtitle {
            font-size: 1rem;
            color: #f8f9fa;
        }
        .event-info {
            margin-bottom: 10px;
        }
        .event-info strong {
            color: #555;
        }
        .event-actions {
            text-align: right;
        }
        .message-container {
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            color: green;
            background-color: #e6ffe6;
            border: 1px solid #a3e9a3;
            padding: 10px;
            border-radius: 5px;
        }
        .error-message {
            color: red;
            background-color: #ffe6e6;
            border: 1px solid #e9a3a3;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <h2>My Tickets</h2>

        <?php if ($success_message): ?>
            <div class="message-container success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message-container error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($my_reservations)): ?>
            <p>You have not made any reservations yet.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($my_reservations as $event): ?>
                    <div class="col-md-6">
                        <div class="card event-card">
                            <div class="event-card-header">
                                <div>
                                    <h5 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="event-card-subtitle"><?php echo htmlspecialchars($event['subtitle']); ?></p>
                                </div>
                                <span class="badge bg-light text-dark">Reserved</span> </div>
                            <div class="card-body event-card-body">
                                <div class="event-info">
                                    <strong>Date & Time:</strong> <?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?><br>
                                    <strong>Price:</strong> &#8369;<?php echo number_format($event['price'], 2); ?><br>
                                    <strong>Created By:</strong> <?php echo htmlspecialchars($event['creator_name'] ?? 'N/A'); ?><br>
                                </div>
                                <div class="event-actions">
                                    <a href="../actions/delete_rsvp_action.php?id=<?php echo htmlspecialchars($event['rsvp_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel Reservation</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>