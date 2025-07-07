<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);

        header("Location: ../pages/register.php?success=Account created! You may now log in.");
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
?>
