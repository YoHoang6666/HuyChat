<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../config/userinfo_db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../account/login');
    exit();
}

$user_id = $_SESSION['user_id'];

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

/* === Fetch all tiers from products table === */
$stmt = $userinfo_conn->prepare("SELECT name, slogan, benefits, price, image, paypal_link, nickname FROM products WHERE category = 'chat_tier' ");
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
        'nickname' => strtolower(trim($row['nickname'])), // ensure lowercase + no stray quotes
    ];
}
$stmt->close();

/* === Fetch user tiers from DB === */
$stmt = $userinfo_conn->prepare("SELECT Silver, Gold, Emerald, Ruby, Diamond, Platinum, Sponsor FROM tiers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user_tiers = [];
while ($row = $result->fetch_assoc()) {
    $user_tiers = [
        'silver'   => $row['Silver'],
        'gold'     => $row['Gold'],
        'emerald'  => $row['Emerald'],
        'ruby'     => $row['Ruby'],
        'diamond'  => $row['Diamond'],
        'platinum' => $row['Platinum'],
        'sponsor'  => $row['Sponsor'],
    ];
}
$stmt->close();

/* === Normalize user tier values === */
foreach ($tiers as $tier) {
    $nickname = strtolower($tier['nickname']);
    $value    = $user_tiers[$nickname] ?? 0; // prevent undefined index
    $user_tiers[$nickname] = $value == 0 ? "BUY" : "BOUGHT";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuyChat - Store for Tiers and Utilities</title>
    <style>
        @media (min-width: 1200px) {
            .ranks-container {
                grid-template-columns: repeat(4, 1fr); /* max 4 per row */
            }
        }
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Roboto',sans-serif; background:#ffffff; color:#ffffff; line-height:1.6; margin: 0}
        .container { max-width: 100%; margin:0 auto; padding:20px;}
        .navbar{
        	position: relative;
        	top: -20px;
            left: -20px;
        	height: 70px;
        	width: 105%;
        	background-color: #000000;
        	align-items: center;
        }
        .navbar .img-container{
          	position: relative;
          	top: 12.5px;
         	left: 12.5px;
          	background-color: #FFFFFF;
          	height: 45px;
          	width: 45px;
          	border-radius: 22.5px;
        }
        .navbar .img-container img{
          	width: 45px;
        }
        .navbar .slogan{
        	font-family: "Montserrat", sans-serif;
        	font-optical-sizing: auto;
        	color: white;
          	font-size: 30px;
          	position: relative;
        	top: -25px;
        	left: 10px;
        }
        .navbar .logo{
        	position: relative;
        	top: 0;
        	left: 0;
           background-color: black;
           width: 200px;
           height: 70px;
        	cursor: pointer;
        	border: 0px;
        }
        .navbar .rank{
        	background-color: black;
        	height: 70px;
        	width: 90px;
        	color: White;
        	border: 0px;
        	position: relative;
        	font-family: "Montserrat", sans-serif;
        	font-size: 30px;
        }
        .nav ul { display:flex; list-style:none; }
        nav ul li { margin-left:20px; }
        nav ul li a { color:#fff; text-decoration:none; font-weight:500; transition:color 0.3s; }
        nav ul li a:hover { color:#FF5722; }
        .ranks-container { display:grid; grid-template-columns: repeat(auto-fit,minmax(250px,1fr)); gap:25px; margin-top:30px; justify-items: center}
        .rank-card { background:#fff; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); margin-top: 25px; width: 250px ; display: flex; flex-direction: column; justify-content: space-between; padding-bottom: 5px;}
        .rank-title { font-size:20px; margin-bottom:10px; color:#000000; background-color: #f5c055; padding: 15px;}
        .rank-desc { margin-bottom:15px; color:#000; margin-left: 20px; margin-right: 20px}
        .rank-img { margin-left: 10px; margin-right: 10px; width: 230px}
        .price-a{ display: flex; margin-top: auto;     /* pushes this block to the bottom */justify-content: space-between;align-items: center;padding: 0 10px; border-top: 1.5px solid #eee}
        .bought { background:#2E7D32; color:#fff; }
        .upgrade { background:#1565C0; color:#fff; }
        .rank-price { font-weight:bold; padding: 8px 16px; color: #000000;}
        .btn { display:inline-block; padding:8px 16px; background:#fff; color:#f5c055; text-decoration:none; border-radius:4px;}
        .btn:hover { text-decoration: 1px }
        /* different borders */
        footer { text-align:center; padding:20px 0; margin-top:50px; border-top:1px solid #333; color:#777; }
        @media (max-width:768px) {
            header { flex-direction:column; align-items:flex-start; }
            nav ul { margin-top:15px; }
            nav ul li { margin-left:0; margin-right:15px; }
            .ranks-container { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="navbar">
        <button class="logo" onclick="location.href='https://huychat.site'">
            <div class="img-container">
                <img alt="hcht" src="../assets/images/favicon.ico">
            </div>
            <div class="slogan"><p>store</p></div>
        </button>
        <p><?php echo $username ?></p>
    </div>
    <main>
        <div class="ranks-container">
            <?php foreach ($tiers as $tier): ?>
                <?php $tier_nick = strtolower($tier['nickname']); ?>
                <div class="rank-card">
                    <h2 class="rank-title"><?php echo htmlspecialchars($tier['name']); ?></h2>
                    <img class="rank-img" src="../assets/images/tiers/<?php echo htmlspecialchars($tier['image'])?>">
                    <p class="rank-desc"><?php echo htmlspecialchars($tier['slogan']); ?></p>
                    <div class="price-a">
                    <?php if ($user_tiers[$tier_nick] === "BUY"): ?>
                        <a href="<?php echo htmlspecialchars($tier['payment']); ?>" class="btn">BUY</a>
                    <?php else: ?>
                        <a>BOUGHT</a>
                    <?php endif; ?>
                    <div class="rank-price">$<?php echo htmlspecialchars($tier['price']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <footer><p>&copy; 2025 HuyChat - All rights reserved</p></footer>
</div>
</body>
</html>