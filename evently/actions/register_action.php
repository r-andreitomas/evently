<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rawPassword = $_POST['password'];
    $role     = $_POST['role'];
    $regexPasswordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    $errors = [];


    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    }

    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Invalid Name";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email.";
    }


    if (!preg_match($regexPasswordPattern, $rawPassword)) {
        $errors[] = "Invalid password. Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_inputs'] = $_POST;
        header("Location: ../pages/register.php");
        exit;
    }



    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);

        header("Location: ../pages/login.php?success=Account created! You may now log in.");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate email
            header("Location: ../pages/register.php?error=Email already in use.");
        } else {
            header("Location: ../pages/register.php?error=Something went wrong.");
        }
        exit;
    }
}
