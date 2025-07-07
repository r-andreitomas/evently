<?php
require_once '../config/database.php';
session_start();

// Only admin can delete users
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$current_admin_id = $_SESSION['user']['id'];
$user_id_to_delete = $_GET['id'] ?? null;

if (!$user_id_to_delete || $user_id_to_delete == $current_admin_id) {
    // Prevent deleting yourself or invalid ID
    header("Location: ../pages/manage_users.php");
    exit;
}

// Delete the user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id_to_delete]);

header("Location: ../pages/manage_users.php");
exit;
