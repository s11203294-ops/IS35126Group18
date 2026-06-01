<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/rbac.php';
require_once '../includes/audit.php';

requireLogin();
requireRole(['customer']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRFToken($_POST['csrf_token']);
    
    $trackingNumber = 'COURIER' . date('Ymd') . rand(1000, 9999);
    
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $pickupAddress = htmlspecialchars($_POST['pickup_address'], ENT_QUOTES, 'UTF-8');
    $deliveryAddress = htmlspecialchars($_POST['delivery_address'], ENT_QUOTES, 'UTF-8');
    
    $stmt = $pdo->prepare("INSERT INTO parcels (tracking_number, customer_id, description, pickup_address, delivery_address, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    
    if ($stmt->execute([$trackingNumber, $_SESSION['user_id'], $description, $pickupAddress, $deliveryAddress])) {
        logAction($pdo, $_SESSION['user_id'], 'parcel_submitted', "Tracking number: $trackingNumber");
        $success = "Parcel submitted successfully!";
    } else {
        $error = "Failed to submit parcel";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Parcel - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>📦 Submit New Parcel</h4>
                    </div>
                    <div class="card-body">
                        <?php if($success): ?>
                            <div class="alert alert-success">
                                <strong>✅ <?= $success ?></strong><br>
                                Your tracking number is: <code><?= $trackingNumber ?? 'N/A' ?></code><br>
                                <a href="track.php?tracking_number=<?= urlencode($trackingNumber ?? '') ?>" class="mt-2 btn btn-sm btn-info">Track your parcel</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <?= csrfInputField() ?>
                            
                            <div class="mb-3">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="3" required placeholder="e.g., Documents, Electronics, Clothing, Gifts"></textarea>
                                <small class="text-muted">Describe the contents of your parcel</small>
                            </div>
                            
                            <div class="mb-3">
                                <label>Pickup Address <span class="text-danger">*</span></label>
                                <textarea name="pickup_address" class="form-control" rows="2" required placeholder="Full address with contact number"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label>Delivery Address <span class="text-danger">*</span></label>
                                <textarea name="delivery_address" class="form-control" rows="2" required placeholder="Full address with recipient name and contact"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Parcel</button>
                            <a href="track.php" class="btn btn-secondary">Track Existing Parcel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>