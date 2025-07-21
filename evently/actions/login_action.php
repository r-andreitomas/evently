<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    //CSRF Login Protection
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Check if user exists and password matches
    if ($user && password_verify($password, $user['password'])) {
        // Set session and redirect to dashboard
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'], // <--- ADD THIS LINE HERE
            'name' => $user['name'], // Keep this as is; it might be 'full_name'
            'email' => $user['email'],
            'role' => $user['role']
        ];
        header("Location: ../pages/dashboard.php");
        exit;
    } else {
        // Redirect back with error message
        header("Location: ../pages/login.php?error=Invalid email or password.");
        exit;
    }
}