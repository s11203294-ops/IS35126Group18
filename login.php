<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/audit.php';

// No session_start() here - auth.php handles it

if (isLoggedIn()) {
    header('Location: dashboard/' . $_SESSION['role'] . '.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token']);
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (!checkLoginAttempts($pdo, $email)) {
        $error = "Account locked due to too many failed attempts. Try again later.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Store user in temp session for 2FA
            $_SESSION['2fa_pending_user_id'] = $user['id'];
            $_SESSION['2fa_pending_email'] = $user['email'];
            recordLoginAttempt($pdo, $email, true);
            
            header('Location: verify-2fa.php');
            exit();
        } else {
            recordLoginAttempt($pdo, $email, false);
            $error = "Invalid email or password";
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Courier Tracking System - Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <?= csrfInputField() ?>
                            
                            <div class="mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <hr>
                        
                        <!-- Google Login Button -->
                        <a href="google-login.php" class="btn btn-danger w-100">
                            <svg width="16" height="16" viewBox="0 0 16 16" style="margin-right: 8px; display: inline; vertical-align: middle;">
                                <path fill="white" d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                            </svg>
                            Login with Google
                        </a>
                        
                        <div class="mt-3 text-center">
                            <a href="register.php">No account? Register here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>