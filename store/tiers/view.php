<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/db_connect.php';
require_once __DIR__.'/../includes/tiers_functions.php';

// Check if tier ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . SITE_URL . "/tiers/");
    exit;
}

$tier_id = (int)$_GET['id'];

try {
    $tier = get_tier($tier_id);
    if (!$tier) {
        header("Location: " . SITE_URL . "/tiers/");
        exit;
    }
} catch (PDOException $e) {
    die("Error loading tier: " . $e->getMessage());
}

$page_title = $tier['name'];
require_once __DIR__.'/../templates/header.php';

// Determine tier color class
$tier_class = strtolower(explode(' ', $tier['name'])[0]);
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header tier-header tier-<?= $tier_class ?>">
                    <h1 class="h2 mb-0"><?= htmlspecialchars($tier['name']) ?></h1>
                </div>
                <div class="card-body text-center">
                    <img src="<?= SITE_URL ?>/assets/images/tiers/<?= htmlspecialchars($tier['image']) ?>" 
                         alt="<?= htmlspecialchars($tier['name']) ?>" 
                         class="img-fluid mb-4" style="max-height: 200px;">
                    
                    <h2 class="text-success mb-4">$<?= htmlspecialchars($tier['price']) ?> USD</h2>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= htmlspecialchars($tier['paypal_link']) ?>" 
                           class="btn btn-paypal btn-lg"
                           data-tier-id="<?= $tier['product_id'] ?>">
                           <i class="fab fa-paypal me-2"></i> Purchase Now
                        </a>
                        <a href="<?= SITE_URL ?>/tiers/" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to All Tiers
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h2 class="h4 mb-0">Tier Benefits</h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled tier-benefits">
                        <?php foreach (explode("\n", $tier['benefits']) as $benefit): 
                            if (trim($benefit)): ?>
                            <li class="mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success mt-1 me-3"></i>
                                    <span><?= htmlspecialchars(trim($benefit)) ?></span>
                                </div>
                            </li>
                        <?php endif;
                        endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    <h2 class="h4 mb-0">Frequently Asked Questions</h2>
                </div>
                <div class="card-body">
                    <div class="accordion" id="tierFAQ">
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    Is this a one-time payment?
                                </button>
                            </h3>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#tierFAQ">
                                <div class="accordion-body">
                                    Yes! All our tiers are <strong>one-time lifetime payments</strong> with no recurring fees.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    How do I access my benefits?
                                </button>
                            </h3>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#tierFAQ">
                                <div class="accordion-body">
                                    Benefits are automatically applied to your account immediately after payment confirmation.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                    Can I upgrade later?
                                </button>
                            </h3>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#tierFAQ">
                                <div class="accordion-body">
                                    Yes! You can upgrade anytime by purchasing a higher tier. The difference in price will be credited.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>