<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Courier Tracking System - IS35126Group18</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5 text-center">
        <h1>Welcome to IS35126Group18 Courier System</h1>
        <p class="lead">Secure parcel tracking with RBAC, 2FA, CSRF, and enterprise-grade security</p>
        
        <div class="row mt-5">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">2FA Protected</h5>
                        <p class="card-text">Two-Factor Authentication</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Google Login</h5>
                        <p class="card-text">OAuth Integration</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">RBAC</h5>
                        <p class="card-text">3 Role Levels</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">CAPTCHA</h5>
                        <p class="card-text">Bot Protection</p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="btn btn-primary btn-lg me-2">Login</a>
            <a href="register.php" class="btn btn-secondary btn-lg">Register</a>
        <?php else: ?>
            <a href="dashboard/<?= $_SESSION['role'] ?>.php" class="btn btn-success btn-lg">Go to Dashboard</a>
        <?php endif; ?>
        
        <hr class="my-5">
        
        <div class="row">
            <div class="col-md-12">
                <h3>Security Features Implemented</h3>
                <ul class="text-start d-inline-block">
                    <li>✓ SQL Injection Prevention (Prepared Statements)</li>
                    <li>✓ XSS Protection (htmlspecialchars)</li>
                    <li>✓ CSRF Tokens on All Forms</li>
                    <li>✓ Two-Factor Authentication (2FA)</li>
                    <li>✓ Google OAuth Login (Demo)</li>
                    <li>✓ CAPTCHA on Registration</li>
                    <li>✓ Role-Based Access Control (Admin, Staff, Customer)</li>
                    <li>✓ Login Attempt Lockout (5 attempts)</li>
                    <li>✓ Secure Session Management</li>
                    <li>✓ Audit Logging</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>