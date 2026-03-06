<?php
// Site Configuration
define('SITE_NAME', 'HuyChat');
define('SITE_URL', 'http://www.huychat.cloud/');
define('CURRENCY', 'USD');
define('PAYPAL_EMAIL', 'huychatservices@gmail.com');
define('EMAIL', 'huychatservices@gmail.com');

// Session
session_start();

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
?>