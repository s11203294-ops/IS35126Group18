<?php
function requireRole($allowedRoles) {
    if (!isset($_SESSION['role'])) {
        header('Location: /courier-system/login.php');
        exit();
    }
    
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        http_response_code(403);
        die("<h2>Access Denied</h2><p>You don't have permission to view this page.</p><a href='/courier-system/dashboard/" . $_SESSION['role'] . ".php'>Back to Dashboard</a>");
    }
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>