<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/db_connect.php';
require_once __DIR__.'/../includes/tiers_functions.php';

$page_title = "Chat Tiers";
require_once __DIR__.'/../templates/header.php';

try {
    $tiers = get_all_tiers();
} catch (PDOException $e) {
    die("Error loading tiers: " . $e->getMessage());
}
?>

<div class="row">
    <?php foreach ($tiers as $tier): ?>
    <div class="col-md-4 mb-4">
        <div class="card tier-card h-100">
            <div class="card-header tier-header bg-primary">
                <h3><?= htmlspecialchars($tier['name']) ?></h3>
                <h4>$<?= htmlspecialchars($tier['price']) ?></h4>
            </div>
            <div class="card-body">
                <img src="<?= SITE_URL ?>/assets/images/tiers/<?= htmlspecialchars($tier['image']) ?>" 
                     alt="<?= htmlspecialchars($tier['name']) ?>" 
                     class="img-fluid mb-3">
                
                <ul class="list-unstyled">
                    <?php foreach (array_slice(explode("\n", $tier['benefits']), 0, 5) as $benefit): ?>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <?= htmlspecialchars(trim($benefit)) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="<?= htmlspecialchars($tier['paypal_link']) ?>" 
                   class="btn btn-paypal w-100"
                   data-tier-id="<?= $tier['product_id'] ?>">
                   <i class="fab fa-paypal me-2"></i> Purchase Now
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?><?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/db_connect.php';
require_once __DIR__.'/../includes/tiers_functions.php';

$page_title = "Chat Tiers";
require_once __DIR__.'/../templates/header.php';

try {
    $tiers = get_all_tiers();
} catch (PDOException $e) {
    die("Error loading tiers: " . $e->getMessage());
}
?>

<div class="row">
    <?php foreach ($tiers as $tier): ?>
    <div class="col-md-4 mb-4">
        <div class="card tier-card h-100">
            <div class="card-header tier-header bg-primary">
                <h3><?= htmlspecialchars($tier['name']) ?></h3>
                <h4>$<?= htmlspecialchars($tier['price']) ?></h4>
            </div>
            <div class="card-body">
                <img src="<?= SITE_URL ?>/assets/images/tiers/<?= htmlspecialchars($tier['image']) ?>" 
                     alt="<?= htmlspecialchars($tier['name']) ?>" 
                     class="img-fluid mb-3">
                
                <ul class="list-unstyled">
                    <?php foreach (array_slice(explode("\n", $tier['benefits']), 0, 5) as $benefit): ?>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <?= htmlspecialchars(trim($benefit)) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="<?= htmlspecialchars($tier['paypal_link']) ?>" 
                   class="btn btn-paypal w-100"
                   data-tier-id="<?= $tier['product_id'] ?>">
                   <i class="fab fa-paypal me-2"></i> Purchase Now
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>