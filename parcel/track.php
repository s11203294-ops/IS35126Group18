<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/rbac.php';

requireLogin();
requireRole(['customer', 'delivery_staff', 'admin']);

$trackingInfo = null;
$error = '';

if (isset($_GET['tracking_number'])) {
    $trackingNumber = $_GET['tracking_number'];
    
    $stmt = $pdo->prepare("
    SELECT p.*, 
           c.name as customer_name, 
           c.email as customer_email
    FROM parcels p
    LEFT JOIN users c ON p.customer_id = c.id
    WHERE p.tracking_number = ?
");
    $stmt->execute([$trackingNumber]);
    $trackingInfo = $stmt->fetch();
    
    if (!$trackingInfo) {
        $error = "Tracking number not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track Parcel - Courier System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-step {
            display: flex;
            margin-bottom: 20px;
        }
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .timeline-icon.completed { background-color: #28a745; color: white; }
        .timeline-icon.active { background-color: #007bff; color: white; }
        .timeline-icon.pending { background-color: #6c757d; color: white; }
        .timeline-content { flex: 1; }
        .timeline-content.completed { color: #28a745; }
        .timeline-content.active { color: #007bff; font-weight: bold; }
        .timeline-content.pending { color: #6c757d; }
        .status-badge { padding: 8px 15px; border-radius: 20px; font-weight: bold; }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-picked_up { background-color: #17a2b8; color: white; }
        .status-in_transit { background-color: #007bff; color: white; }
        .status-delivered { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4>🔍 Track Your Parcel</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="tracking_number" class="form-control" placeholder="Enter tracking number (e.g., COURIER202606011234)" value="<?= htmlspecialchars($_GET['tracking_number'] ?? '') ?>">
                                <button type="submit" class="btn btn-info">Track</button>
                            </div>
                        </form>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if($trackingInfo): ?>
                            <div class="alert alert-success">
                                <strong>📋 Tracking Number:</strong> <?= htmlspecialchars($trackingInfo['tracking_number']) ?>
                            </div>
                            
                            <!-- Timeline -->
                            <div class="timeline">
                                <?php
                                $statuses = ['pending', 'picked_up', 'in_transit', 'delivered'];
                                $currentStatus = $trackingInfo['status'];
                                $currentIndex = array_search($currentStatus, $statuses);
                                $statusLabels = ['Pending', 'Picked Up', 'In Transit', 'Delivered'];
                                
                                for($i = 0; $i < count($statuses); $i++):
                                    $stepStatus = $i < $currentIndex ? 'completed' : ($i == $currentIndex ? 'active' : 'pending');
                                ?>
                                <div class="timeline-step">
                                    <div class="timeline-icon <?= $stepStatus ?>">
                                        <?php if($stepStatus == 'completed'): ?>✓
                                        <?php elseif($stepStatus == 'active'): ?>●
                                        <?php else: ?>○<?php endif; ?>
                                    </div>
                                    <div class="timeline-content <?= $stepStatus ?>">
                                        <strong><?= $statusLabels[$i] ?></strong>
                                        <?php if($statuses[$i] == 'delivered' && $trackingInfo['delivered_at']): ?>
                                            <br><small><?= date('F j, Y g:i A', strtotime($trackingInfo['delivered_at'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Parcel Details -->
                            <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h6>Parcel Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">Status</th>
                                            <td><span class="status-badge status-<?= $trackingInfo['status'] ?>"><?= ucfirst(str_replace('_', ' ', $trackingInfo['status'])) ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td><?= nl2br(htmlspecialchars($trackingInfo['description'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Pickup Address</th>
                                            <td><?= nl2br(htmlspecialchars($trackingInfo['pickup_address'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Delivery Address</th>
                                            <td><?= nl2br(htmlspecialchars($trackingInfo['delivery_address'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td><?= htmlspecialchars($trackingInfo['customer_name']) ?> (<?= htmlspecialchars($trackingInfo['customer_email']) ?>)</td>
                                        </tr>
                                        <?php if($trackingInfo['delivery_person_name']): ?>
                                        <tr>
                                            <th>Delivery Staff</th>
                                            <td><?= htmlspecialchars($trackingInfo['delivery_person_name']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Submitted On</th>
                                            <td><?= htmlspecialchars($trackingInfo['created_at']) ?></td>
                                        </tr>
                                        <?php if($trackingInfo['delivered_at']): ?>
                                        <tr>
                                            <th>Delivered On</th>
                                            <td><?= htmlspecialchars($trackingInfo['delivered_at']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>