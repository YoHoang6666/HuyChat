<?php
   require_once __DIR__.'/includes/config.php';
?>
</div> <!-- Close container -->
    </div> <!-- Optional wrapper div -->

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= SITE_NAME ?></h5>
                    <p>Upgrade your chat experience with premium tiers.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= SITE_URL ?>/tiers/" class="text-white">View Tiers</a></li>
                        <li><a href="<?= SITE_URL ?>/account/" class="text-white">My Account</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> <?= SITE_URL ?></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <small>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>