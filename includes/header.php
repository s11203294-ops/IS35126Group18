<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/courier-system/">IS35126Group18 - Courier System</a>
        <div class="navbar-nav ms-auto">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="nav-item nav-link text-light">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
                <a class="nav-link" href="/courier-system/dashboard/<?= $_SESSION['role'] ?>.php">Dashboard</a>
                <a class="nav-link" href="/courier-system/logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-link" href="/courier-system/login.php">Login</a>
                <a class="nav-link" href="/courier-system/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
</body>
</html>