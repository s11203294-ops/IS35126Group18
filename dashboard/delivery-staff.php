<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/rbac.php';
require_once '../includes/audit.php';

requireLogin();
requireRole(['delivery_staff', 'admin']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verifyCSRFToken($_POST['csrf_token']);
    
    $parcelId = $_POST['parcel_id'];
    $newStatus = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE parcels SET status = ?, delivered_at = (CASE WHEN ? = 'delivered' THEN NOW() ELSE NULL END) WHERE id = ?");
    
    if ($stmt->execute([$newStatus, $newStatus, $parcelId])) {
        logAction($pdo, $_SESSION['user_id'], 'status_updated', "Parcel ID $parcelId -> $newStatus");
        $success = "Status updated successfully";
    } else {
        $error = "Failed to update status";
    }
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as customer_name, c.email as customer_email 
    FROM parcels p 
    LEFT JOIN users c ON p.customer_id = c.id 
    WHERE p.status != 'delivered' 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$parcels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delivery Staff Dashboard - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Delivery Dashboard</h2>
        <p class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</p>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>Parcels to Process</h5>
            </div>
            <div class="card-body">
                <?php if(count($parcels) === 0): ?>
                    <p class="text-muted text-center">No parcels to process at this time.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Customer</th>
                                    <th>Description</th>
                                    <th>Pickup Address</th>
                                    <th>Delivery Address</th>
                                    <th>Current Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($parcels as $parcel): ?>
                                <tr>
                                    <td><?= htmlspecialchars($parcel['tracking_number']) ?></span></span>
                                    <td><?= htmlspecialchars($parcel['customer_name'] ?? 'Unknown') ?></span><br>
                                        <small class="text-muted"><?= htmlspecialchars($parcel['customer_email'] ?? '') ?></small>
                                    </span>
                                    <td><?= htmlspecialchars(substr($parcel['description'], 0, 40)) ?>...</span></span>
                                    <td><?= htmlspecialchars(substr($parcel['pickup_address'], 0, 30)) ?>...</span></span>
                                    <td><?= htmlspecialchars(substr($parcel['delivery_address'], 0, 30)) ?>...</span></span>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $parcel['status'] == 'delivered' ? 'success' : 
                                            ($parcel['status'] == 'pending' ? 'warning' : 'primary') 
                                        ?>">
                                            <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                                        </span>
                                    </span>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="parcel_id" value="<?= $parcel['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width: auto;">
                                                <option value="picked_up">📦 Picked Up</option>
                                                <option value="in_transit">🚚 In Transit</option>
                                                <option value="delivered">✅ Delivered</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    </span>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>