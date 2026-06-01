<?php
require_once 'config/database.php';
require_once 'includes/csrf.php';

session_start();
$error = '';
$success = '';

// Generate CAPTCHA numbers - store in session so they persist
if (!isset($_SESSION['captcha_answer']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $num1 + $num2;
    $_SESSION['captcha_num1'] = $num1;
    $_SESSION['captcha_num2'] = $num2;
} else {
    $num1 = $_SESSION['captcha_num1'];
    $num2 = $_SESSION['captcha_num2'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token']);
    
    // Verify CAPTCHA
    if ($_POST['captcha'] != $_SESSION['captcha_answer']) {
        $error = "CAPTCHA verification failed. Please try again.";
        // Generate new CAPTCHA for retry
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $_SESSION['captcha_num1'] = $num1;
        $_SESSION['captcha_num2'] = $num2;
    } else {
        $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($password !== $confirmPassword) {
            $error = "Passwords do not match";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
            
            try {
                $stmt->execute([$name, $email, $hashedPassword]);
                $success = "Registration successful! Please login.";
                // Generate new CAPTCHA for next registration
                $num1 = rand(1, 10);
                $num2 = rand(1, 10);
                $_SESSION['captcha_answer'] = $num1 + $num2;
                $_SESSION['captcha_num1'] = $num1;
                $_SESSION['captcha_num2'] = $num2;
            } catch(PDOException $e) {
                $error = "Email already exists";
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>Register for Courier Service</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?= $success ?> <a href="login.php">Login here</a></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <?= csrfInputField() ?>
                            
                            <div class="mb-3">
                                <label>Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Password (min 8 chars)</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <!-- CAPTCHA Field - uses session values -->
                            <div class="mb-3">
                                <label>Verify you are human: <?= $_SESSION['captcha_num1'] ?> + <?= $_SESSION['captcha_num2'] ?> = ?</label>
                                <input type="text" name="captcha" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Register</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="login.php">Already have an account? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>