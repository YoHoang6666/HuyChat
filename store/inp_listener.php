<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/db_connect.php';
require_once __DIR__.'/includes/tiers_functions.php';

// Read POST data
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();

foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Build verification request
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Post back to PayPal
$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
$res = curl_exec($ch);
curl_close($ch);

// Process verified IPN
if (strcmp($res, "VERIFIED") == 0 && $_POST['payment_status'] == 'Completed') {
    $pdo = Database::getInstance();
    
    // Extract custom data (user_id|product_id)
    $custom = explode('|', $_POST['custom']);
    $user_id = (int)$custom[0];
    $product_id = (int)$custom[1];
    
    // Record purchase
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, txn_id, amount, currency) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $product_id,
        $_POST['txn_id'],
        $_POST['mc_gross'],
        $_POST['mc_currency']
    ]);
    
    // Grant tier access
    $stmt = $pdo->prepare("INSERT INTO user_tiers (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
    
    // TODO: Send confirmation email
}

header("HTTP/1.1 200 OK");
?>