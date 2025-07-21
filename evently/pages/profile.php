<?php
require_once '../includes/session.php';
require_once '../config/database.php';

// Re-enable the redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'] ?? null;

$user_data = [];
$error_message = '';
$success_message = '';

// Check if user_id is available before attempting database query
if ($user_id === null) {
    $error_message = "Error: User ID not found in session. Please log in again.";
    // No redirect here, let the page display the error message.
} else {
    try {
        $stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            $error_message = "Error: User profile not found in the database for ID: " . htmlspecialchars($user_id);
        }
    } catch (PDOException $e) {
        error_log("Database error fetching user profile: " . $e->getMessage());
        $error_message = "Database Error: Could not load profile data. " . htmlspecialchars($e->getMessage());
    }
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
    <title>User Profile - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Dongle:wght@700&family=Montserrat&display=swap" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/uicons-solid-straight/css/uicons-solid-straight.css" rel="stylesheet">

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

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }

        .navbar-nav .nav-link.active {
            color: #fff;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .profile-icon {
            font-size: 60px;
            color: black;
            background-color: white;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid black;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }


        .profile-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #eee;
        }

        .profile-info-item:last-child {
            border-bottom: none;
        }

        .profile-info-label {
            font-weight: bold;
            color: #555;
            flex-basis: 30%;
            text-align: left;
        }

        .profile-info-value {
            color: #333;
            flex-basis: 65%;
            text-align: right;
        }

        .profile-actions {
            margin-top: 30px;
            text-align: center;
        }

        .profile-actions .btn {
            margin: 0 10px;
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
        <div class="card p-4 shadow-sm">
            <h2 class="profile-header">User Profile</h2>

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

            <div style="text-align: center; margin-bottom: 30px;">
                <i class="fi fi-ss-user profile-icon"></i>
            </div>

            <div class="profile-info-item">
                <span class="profile-info-label">Display Name:</span>
                <span class="profile-info-value"><?php echo htmlspecialchars($user_data['name'] ?? 'N/A'); ?></span>
            </div>

            <div class="profile-info-item">
                <span class="profile-info-label">Email:</span>
                <span class="profile-info-value"><?php echo htmlspecialchars($user_data['email'] ?? 'N/A'); ?></span>
            </div>
            <div class="profile-info-item">
                <span class="profile-info-label">Role:</span>
                <span class="profile-info-value"><?php echo nl2br(htmlspecialchars($user_data['role'] ?? 'No bio provided.')); ?></span>
            </div>

            <div class="profile-actions">
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                <a href="dashboard.php" class="btn btn-secondary">Back to Events</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>