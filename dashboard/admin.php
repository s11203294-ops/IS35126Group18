<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/rbac.php';
require_once '../includes/audit.php';

requireLogin();
requireRole(['admin']);

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];
    
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);
    logAction($pdo, $_SESSION['user_id'], 'role_changed', "User $userId to role $newRole");
    $success = "Role updated successfully";
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$parcels = $pdo->query("SELECT p.*, u.name as customer_name FROM parcels p LEFT JOIN users u ON p.customer_id = u.id ORDER BY p.created_at DESC LIMIT 20")->fetchAll();
$auditLogs = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 50")->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) as total_parcels, SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered FROM parcels")->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        <p class="text-muted">System Management Panel</p>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h2><?= count($users) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Parcels</h5>
                        <h2><?= $stats['total_parcels'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Delivered</h5>
                        <h2><?= $stats['delivered'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <h2><?= ($stats['total_parcels'] ?? 0) - ($stats['delivered'] ?? 0) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Management -->
        <div class="card mt-4">
            <div class="card-header bg-danger text-white">
                <h5>👥 User Management (RBAC)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Change Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></span></span>
                                <td><?= htmlspecialchars($user['name']) ?></span></span>
                                <td><?= htmlspecialchars($user['email']) ?></span></span>
                                <td>
                                    <span class="badge bg-<?= 
                                        $user['role'] == 'admin' ? 'danger' : 
                                        ($user['role'] == 'delivery_staff' ? 'warning' : 'secondary') 
                                    ?>">
                                        <?= $user['role'] ?>
                                    </span>
                                </span>
                                <td>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role" class="form-select form-select-sm" style="width: auto;">
                                            <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                                            <option value="delivery_staff" <?= $user['role'] == 'delivery_staff' ? 'selected' : '' ?>>Delivery Staff</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_role" class="btn btn-sm btn-warning">Update</button>
                                    </form>
                                </span>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Parcels -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5>📦 Recent Parcels</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Tracking #</th><th>Customer</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($parcels as $parcel): ?>
                            <tr>
                                <td><?= htmlspecialchars($parcel['tracking_number']) ?></span></span>
                                <td><?= htmlspecialchars($parcel['customer_name'] ?? 'Unknown') ?></span></span>
                                <td><?= $parcel['status'] ?></span></span>
                                <td><?= $parcel['created_at'] ?></span></span>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Audit Logs -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h5>📋 Audit Logs (Security Monitoring)</h5>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User ID</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($auditLogs as $log): ?>
                            <tr>
                                <td><?= $log['created_at'] ?></span></span>
                                <td><?= $log['user_id'] ?></span></span>
                                <td><?= htmlspecialchars($log['action']) ?></span></span>
                                <td><?= htmlspecialchars($log['details'] ?? '') ?></span></span>
                                <td><?= $log['ip_address'] ?></span></span>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>