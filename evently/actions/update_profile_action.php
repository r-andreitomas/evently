<?php
require_once '../includes/session.php';
require_once '../config/database.php';

if (!isLoggedIn()) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // FIXED: Added CSRF token check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: ../pages/edit_profile.php");
        exit();
    }
    $user_id = $_SESSION['user']['id'];
    
    $name = trim($_POST['name'] ?? ''); 
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $current_profile_picture_from_form = trim($_POST['current_profile_picture'] ?? 'default.jpg'); // From hidden input

    $errors = [];

    // Basic validation
    if (empty($name)) { 
        $errors[] = "Display Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists for another account.";
        }
    } catch (PDOException $e) {
        error_log("Database error checking duplicates for profile update: " . $e->getMessage());
        $errors[] = "An error occurred while validating your data.";
    }

    $profile_picture_name = $current_profile_picture_from_form; 

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $target_dir = "../uploads/"; 
        $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_file_name = uniqid('profile_') . '.' . $imageFileType;
        $target_file = $target_dir . $new_file_name;
        $uploadOk = 1;

        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            $errors[] = "File is not an image.";
            $uploadOk = 0;
        }

        if ($file["size"] > 5000000) {
            $errors[] = "Sorry, your file is too large (max 5MB).";
            $uploadOk = 0;
        }

        $allowed_formats = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_formats)) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            // Errors already added
        } else {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $profile_picture_name = $new_file_name;
                if ($current_profile_picture_from_form != 'default.jpg' && file_exists($target_dir . $current_profile_picture_from_form)) {
                    unlink($target_dir . $current_profile_picture_from_form);
                }
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE users SET name = ?, email = ?, full_name = ?, bio = ?, profile_picture = ? WHERE id = ?";
            $params = [$name, $email, $full_name, $bio, $profile_picture_name, $user_id];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['full_name'] = $full_name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['bio'] = $bio;
            $_SESSION['user']['profile_picture'] = $profile_picture_name; 
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: ../pages/profile.php");
            exit();

        } catch (PDOException $e) {
            error_log("Database error updating user profile: " . $e->getMessage());
            if ($e->getCode() == 23000) { 
                $_SESSION['error_message'] = "The email address is already in use by another account.";
            } else {
                $_SESSION['error_message'] = "An error occurred while updating your profile.";
            }
            header("Location: ../pages/edit_profile.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: ../pages/edit_profile.php");
        exit();
    }

} else {
    header("Location: ../pages/dashboard.php");
    exit();
}
?>