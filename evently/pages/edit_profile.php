<?php
require_once '../includes/session.php'; 
require_once '../config/database.php'; 

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$user_data = [];
$error_message = '';
$success_message = '';

try {
    $stmt = $pdo->prepare("SELECT name, email, full_name, bio, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        $_SESSION['error_message'] = "User not found for editing.";
        header("Location: profile.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error fetching user for edit profile: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not load profile data for editing due to a database error.";
    header("Location: profile.php");
    exit();
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
    <title>Edit Profile - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
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
        .navbar-nav .nav-link { 
            color: rgba(255, 255, 255, 0.75); 
        }
        .navbar-nav .nav-link.active {
            color: #fff; 
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: normal;
            color: #333;
            display: block;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group input[type="file"] {
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .current-profile-pic-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .current-profile-pic-container span {
            margin-right: 10px;
            font-weight: bold;
            color: #555;
        }
        .current-profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #eee;
        }

        .form-actions {
            margin-top: 30px;
            text-align: right;
        }
        .form-actions .btn {
            margin-left: 10px;
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
            border: 1px solid #e9a3e9a3;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    
    <?php include '../includes/header.php'; ?>    

    <div class="container py-5"> <div class="card p-4 shadow-sm"> <h2>Edit Profile</h2>

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

            <form action="../actions/update_profile_action.php" method="POST" enctype="multipart/form-data">
                <?php if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($user_data['profile_picture'] ?? 'default.jpg'); ?>">


                <div class="form-group">
                    <label for="name">Display Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="bio">Bio:</label>
                    <textarea id="bio" name="bio"><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    <?php if (!empty($user_data['profile_picture']) && $user_data['profile_picture'] != 'default.jpg'): ?>
                        <div class="current-profile-pic-container">
                            <span>Current:</span>
                            <img src="../uploads/<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="Current Profile Picture" class="current-profile-pic">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-actions">
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>