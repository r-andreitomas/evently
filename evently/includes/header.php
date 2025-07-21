<?php
$user = $_SESSION['user'] ?? ['username' => 'Guest', 'role' => 'guest'];
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<!-- 🔝 Navbar -->
<nav class="navbar navbar-expand-lg bg-primary navbar-dark py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand evently-logo" href="../index.php">Evently</a>
        <div class="d-flex align-items-center">
            <ul class="navbar-nav me-3">
                <!-- Corrected Events link and dynamic active class -->
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'dashboard.php' ? 'active' : '') ?>" href="dashboard.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'create_event.php' ? 'active' : '') ?>" href="create_event.php">Create</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'my_tickets.php' ? 'active' : '') ?>" href="my_tickets.php">My Tickets</a>
                </li>
            </ul>
            <!-- Profile Link -->
            <a href="profile.php" class="text-white me-3" style="text-decoration: none;">
                <?= htmlspecialchars($user['name'] ?? $user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)
            </a>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>