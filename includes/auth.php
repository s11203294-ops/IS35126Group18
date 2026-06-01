<?php
// Set cookie parameters BEFORE any session output
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);

// Start session if not active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['last_activity']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /courier-system/login.php');
        exit();
    }
    
    if (time() - $_SESSION['last_activity'] > 900) {
        session_destroy();
        header('Location: /courier-system/login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function checkLoginAttempts($pdo, $email) {
    $stmt = $pdo->prepare("SELECT login_attempts, locked_until FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // If user doesn't exist in database, allow (they will get "invalid email" later)
    if ($user === false) {
        return true;
    }
    
    // Check if account is locked
    if (!empty($user['locked_until'])) {
        $lockedUntil = new DateTime($user['locked_until']);
        $now = new DateTime();
        if ($lockedUntil > $now) {
            return false;
        }
    }
    return true;
}

function recordLoginAttempt($pdo, $email, $success) {
    // First check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // If user doesn't exist, don't record anything
    if ($user === false) {
        return;
    }
    
    if ($success) {
        $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE email = ?");
        $stmt->execute([$email]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE email = ?");
        $stmt->execute([$email]);
        
        $stmt = $pdo->prepare("SELECT login_attempts FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        
        // Check if we got a valid result
        if ($result !== false) {
            $attempts = $result['login_attempts'];
            
            if ($attempts >= 5) {
                $lockUntil = new DateTime('+15 minutes');
                $stmt = $pdo->prepare("UPDATE users SET locked_until = ? WHERE email = ?");
                $stmt->execute([$lockUntil->format('Y-m-d H:i:s'), $email]);
            }
        }
    }
}
?>