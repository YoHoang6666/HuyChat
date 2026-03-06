<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuyChat Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>">
                <i class="fas fa-crown me-2"></i><?= SITE_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/tiers/">Tiers</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/account/">
                                <i class="fas fa-user-circle"></i> My Account
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/account/login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-4">