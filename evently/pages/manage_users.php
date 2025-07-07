<?php
require_once '../config/database.php';
session_start();

// Access control: Only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Fetch all users except the currently logged-in admin
$current_user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ?");
$stmt->execute([$current_user_id]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 2rem;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
        }
    </style>
</head>
<body>

<h2 class="mb-4">User Management</h2>

<a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
                <td>
                    <a href="../actions/delete_user_action.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
