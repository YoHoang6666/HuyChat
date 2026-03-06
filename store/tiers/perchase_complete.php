<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/tiers_functions.php';

$page_title = "Purchase Complete";
require_once __DIR__.'/templates/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the latest order for this user
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT o.*, p.name as product_name, p.benefits 
                       FROM orders o
                       JOIN tier_products p ON o.product_id = p.product_id
                       WHERE o.user_id = ?
                       ORDER BY o.order_at DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-check-circle me-2"></i> Purchase Complete</h3>
                </div>
                <div class="card-body">
                    <?php if ($order): ?>
                        <div class="alert alert-success">
                            <h4 class="alert-heading">Thank you for your purchase!</h4>
                            <p>You now have lifetime access to <strong><?= htmlspecialchars($order['product_name']) ?></strong>.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Order Details</h5>
                                <ul class="list-group mb-4">
                                    <li class="list-group-item">
                                        <strong>Product:</strong> <?= htmlspecialchars($order['product_name']) ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Amount:</strong> $<?= htmlspecialchars($order['amount']) ?> <?= htmlspecialchars($order['currency']) ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Date:</strong> <?= date('F j, Y g:i a', strtotime($order['order_at'])) ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-success"><?= htmlspecialchars($order['payment_status']) ?></span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>What's Next?</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p>Your account has been upgraded automatically. You can now:</p>
                                        <ul>
                                            <li>Access all <?= htmlspecialchars($order['product_name']) ?> features</li>
                                            <li>Enjoy your new benefits immediately</li>
                                            <li>Manage your account from your profile</li>
                                        </ul>
                                        <a href="/account/" class="btn btn-primary mt-2">
                                            <i class="fas fa-user"></i> Go to My Account
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Your New Benefits</h5>
                            <div class="card">
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <?php foreach (explode("\n", $order['benefits']) as $benefit): ?>
                                            <?php if (trim($benefit)): ?>
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
                                                <?= htmlspecialchars(trim($benefit)) ?>
                                            </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <h4 class="alert-heading">No Recent Purchases Found</h4>
                            <p>We couldn't find any recent purchases for your account.</p>
                            <hr>
                            <p class="mb-0">If you just completed a purchase, please wait a few minutes and refresh this page.</p>
                        </div>
                        <a href="/tiers/" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Tiers
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted">
                    <small>Transaction ID: <?= $order ? htmlspecialchars($order['txn_id']) : 'N/A' ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/templates/footer.php'; ?>