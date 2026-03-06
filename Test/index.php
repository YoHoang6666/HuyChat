<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require "userinfo_db_connect.php";
//if (!isset($_SESSION['user_id'])) {
//    header('Location: login');
//    exit();
//}

$user_id = 22;//$_SESSION['user_id'];

// --- Fetch username of user ---
$stmt = $userinfo_conn->prepare("
    SELECT username
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$username;
$username = $row['username'];
$stmt->close();
// --- Fetch all available tiers from products ---
$stmt = $userinfo_conn->prepare("
    SELECT name, slogan, benefits, price, image, nickname, paypal_link
    FROM products
    WHERE category = 'chat_tiers'
");
$stmt->execute();
$result = $stmt->get_result();

$tiers = [];
while ($row = $result->fetch_assoc()) {
    $tiers[] = [
        'name'     => $row['name'],
        'slogan'   => $row['slogan'],
        'benefits' => $row['benefits'],
        'price'    => $row['price'],
        'image'    => $row['image'],
        'payment'  => $row['paypal_link'],
        'nickname' => $row['nickname']
    ];
}
$stmt->close();

// --- Fetch user's purchased tiers ---
$stmt = $userinfo_conn->prepare("
    SELECT Silver, Gold, Emerald, Ruby, Diamond, Platinum, Sponsor
    FROM tiers
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_tiers = $result->fetch_assoc() ?? [];
$stmt->close();

// --- Define readable labels ---
$labels = ['Silver', 'Gold', 'Emerald', 'Ruby', 'Diamond', 'Platinum', 'Sponsor'];

foreach ($labels as $label) {
    $user_tiers[$label] = (!empty($user_tiers[$label]) && $user_tiers[$label] > 0)
        ? 'BOUGHT'
        : 'BUY';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <button class="logo" onclick="location.href='https://huychat.site'">
            <div class="img-container">
                <img alt="hcht" src="hcht-black.png">
            </div>
            <div class="slogan"><p>store</p></div>
        </button>
        <p><?php echo $username?></p>
        <?php foreach ($tiers as $tier): ?>
        <h1><?= htmlspecialchars($tier['nickname']) ?></h1>
        <?php endforeach; ?>
    </div>

    <div class="rank-con">
        <?php foreach ($tiers as $tier): ?>
            <div class="con">
                <h2><?= htmlspecialchars($tier['name']) ?></h2>
                <img src="<?= htmlspecialchars($tier['image']) ?>" alt="">
                <h3 class="slogan1"><?= htmlspecialchars($tier['slogan']) ?></h3>
                <h4>$<?= htmlspecialchars($tier['price']) ?></h4>
                <button onclick="location.href='<?= htmlspecialchars($tier['payment']) ?>'">
                    <?= $user_tiers[$tier['nickname']] ?>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>