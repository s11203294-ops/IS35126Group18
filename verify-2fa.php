<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';
require_once 'includes/audit.php';

$error = '';

if (!isset($_SESSION['2fa_pending_user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token']);
    
    $userOtp = $_POST['otp'];
    
    // Demo 2FA: Fixed code is 123456
    if ($userOtp == '123456') {
        $userId = $_SESSION['2fa_pending_user_id'];
        
        $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        unset($_SESSION['2fa_pending_user_id']);
        unset($_SESSION['2fa_pending_email']);
        
        logAction($pdo, $user['id'], 'login_success', "2FA verified successfully");
        
        header('Location: dashboard/' . $user['role'] . '.php');
        exit();
    } else {
        $error = "Invalid verification code. Use code: 123456";
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Two-Factor Authentication</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info text-center">
                            <strong>Demo Mode</strong><br>
                            Use code: <code>123456</code>
                        </div>
                        <p class="text-muted small">A verification code has been sent to your email.<br>For demo purposes, use the code above.</p>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <?= csrfInputField() ?>
                            
                            <div class="mb-3">
                                <label>Verification Code</label>
                                <input type="text" name="otp" class="form-control text-center" maxlength="6" placeholder="123456" required>
                            </div>
                            
                            <button type="submit" class="btn btn-warning w-100">Verify</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>