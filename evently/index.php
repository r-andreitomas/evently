<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Evently</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap + Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dongle:wght@700&family=Montserrat&display=swap" rel="stylesheet">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
        }

        .carousel,
        .carousel-inner,
        .carousel-item {
            height: 100vh;
        }

        .carousel-item img {
            object-fit: cover;
            height: 100vh;
            width: 100%;
            filter: brightness(60%);
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .hero-overlay h1 {
            font-size: 4rem;
            font-weight: bold;
        }

        .evently-logo {
            font-family: 'Dongle', sans-serif;
            font-size: 42px;
        }

        .navbar-dark .nav-link {
            color: white !important;
        }

        .btn-primary {
            font-size: 1.2rem;
            padding: 0.6rem 1.8rem;
        }
    </style>
</head>
<body>

<!-- 🔝 Navbar -->
<nav class="navbar navbar-expand-lg bg-transparent navbar-dark position-absolute w-100 py-3" style="z-index: 20;">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand evently-logo" href="index.php">Evently</a>
        <div>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="pages/dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <?php else: ?>
                <a href="pages/login.php" class="btn btn-outline-light btn-sm me-2">Sign In</a>
                <a href="pages/register.php" class="btn btn-light btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- 🌌 Carousel Background -->
<div id="eventlyCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="assets/bg1.jpg" class="d-block w-100" alt="Slide 1">
        </div>
        <div class="carousel-item">
            <img src="assets/bg2.jpg" class="d-block w-100" alt="Slide 2">
        </div>
        <div class="carousel-item">
            <img src="assets/bg3.jpg" class="d-block w-100" alt="Slide 3">
        </div>
    </div>
</div>

<!-- 🌟 Hero Content Overlay -->
<div class="hero-overlay">
    <h1>Create, Connect, Celebrate</h1>
    <p class="lead mb-4">Plan and join events seamlessly with Evently.</p>
    <a href="pages/dashboard.php" class="btn btn-primary">Get Started</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>