<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/rbac.php';

requireLogin();
requireRole(['customer']);

$stmt = $pdo->prepare("SELECT * FROM parcels WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$parcels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
        <p class="text-muted">Customer Dashboard - Track and submit your parcels</p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">📦 Submit New Parcel</h5>
                        <p class="card-text">Create a new shipment</p>
                        <a href="../parcel/submit.php" class="btn btn-primary">Submit Parcel</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info">🔍 Track Parcel</h5>
                        <p class="card-text">Check delivery status</p>
                        <a href="../parcel/track.php" class="btn btn-info">Track Now</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>My Parcels</h5>
            </div>
            <div class="card-body">
                <?php if(count($parcels) === 0): ?>
                    <p class="text-muted text-center">No parcels found. <a href="../parcel/submit.php">Submit your first parcel</a></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Description</th>
                                    <th>Pickup Address</th>
                                    <th>Delivery Address</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($parcels as $parcel): ?>
                                <tr>
                                    <td><a href="../parcel/track.php?tracking_number=<?= urlencode($parcel['tracking_number']) ?>"><?= htmlspecialchars($parcel['tracking_number']) ?></a></span></td>
                                    <td><?= htmlspecialchars(substr($parcel['description'], 0, 40)) ?>...</span></td>
                                    <td><?= htmlspecialchars(substr($parcel['pickup_address'], 0, 30)) ?>...</span></td>
                                    <td><?= htmlspecialchars(substr($parcel['delivery_address'], 0, 30)) ?>...</span></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $parcel['status'] == 'delivered' ? 'success' : 
                                            ($parcel['status'] == 'pending' ? 'warning' : 'primary') 
                                        ?>">
                                            <?= ucfirst(str_replace('_', ' ', $parcel['status'])) ?>
                                        </span>
                                    </span>
                                    <td><?= htmlspecialchars($parcel['created_at']) ?></span>
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