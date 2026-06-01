<?php
session_start();
require_once 'config/database.php';
require_once 'includes/audit.php';

// Demo Google Login - Simulates OAuth
$error = '';

if (isset($_GET['demo'])) {
    $email = $_GET['email'];
    $name = $_GET['name'];
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, 'customer')");
        $stmt->execute([$name, $email]);
        $userId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
    
    // Store for 2FA
    $_SESSION['2fa_pending_user_id'] = $user['id'];
    $_SESSION['2fa_pending_email'] = $user['email'];
    
    logAction($pdo, $user['id'], 'google_login', "Google login initiated");
    
    header('Location: verify-2fa.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Google Login - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Google Login (Demo Mode)</h4>
                    </div>
                    <div class="card-body text-center">
                        <p>Select a demo account to login with Google OAuth simulation:</p>
                        
                        <div class="d-grid gap-3">
                            <a href="?demo=1&email=admin@courier.com&name=Admin+User" class="btn btn-danger btn-lg">
                                <svg width="20" height="20" viewBox="0 0 16 16" style="margin-right: 10px;">
                                    <path fill="white" d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                                </svg>
                                Login as Admin
                            </a>
                            <a href="?demo=1&email=delivery@courier.com&name=John+Delivery" class="btn btn-warning btn-lg">
                                Login as Delivery Staff
                            </a>
                            <a href="?demo=1&email=customer@courier.com&name=Jane+Customer" class="btn btn-success btn-lg">
                                Login as Customer
                            </a>
                            <a href="?demo=1&email=newuser@gmail.com&name=New+User" class="btn btn-primary btn-lg">
                                Login as New Customer
                            </a>
                        </div>
                        
                        <hr>
                        <p class="text-muted small mt-3">
                            <strong>Note:</strong> This is a demo simulation of Google OAuth.<br>
                            In production, this would redirect to Google's OAuth server.
                        </p>
                        <a href="login.php" class="btn btn-link">← Back to Normal Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>