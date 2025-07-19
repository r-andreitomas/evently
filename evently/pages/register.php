<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['old_inputs'] ?? [];
$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
unset($_SESSION['form_errors'], $_SESSION['old_inputs']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Evently</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom right, #00b894, #0984e3);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            width: 100%;
            max-width: 450px;
            border-radius: 16px;
        }
    </style>
</head>
<body>

<div class="card shadow p-4">
    <h3 class="text-center mb-3">Create an Account</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="../actions/register_action.php">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" required placeholder="e.g., John Doe"
                   value="<?= htmlspecialchars($old['name'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required placeholder="e.g., john@email.com"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required placeholder="Choose a secure password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">Show</button>
            </div>
        </div>

        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-select" required>
                <option value="user" <?= (isset($old['role']) && $old['role'] === 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (isset($old['role']) && $old['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="d-grid mb-2">
            <button type="submit" class="btn btn-success">Register</button>
        </div>

        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<script>
document.getElementById("togglePassword").addEventListener("click", function () {
    const password = document.getElementById("password");
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    this.textContent = type === "password" ? "Show" : "Hide";
});
</script>

</body>
</html>