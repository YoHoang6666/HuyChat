<?php
// Site Configuration
define('SITE_NAME', 'Chat Tiers Store');
define('SITE_URL', 'https://huychat.site/store');
define('CURRENCY', 'USD');
define('PAYPAL_EMAIL', 'huychatservices@gmail.com');

// Session
session_start();

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
?>