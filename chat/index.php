<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "../config/config.php";
require "../config/userinfo_db_connect.php";
require "../config/conv_db_connect.php";
require "../config/convinfo_db_connect.php";
require "../config/server_db_connect.php";
require "../config/serverinfo_db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../account/login');
    exit();
}

$user_id = $_SESSION['user_id'];
$servers = [];


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

// --- Fetch all tiers from products table ---
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

// --- Fetch user tiers from DB ---
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

// --- Normalize user tier values ---
foreach ($tiers as $tier) {
    $nickname = strtolower($tier['nickname']);
    $value    = $user_tiers[$nickname] ?? 0; // prevent undefined index
    $user_tiers[$nickname] = $value == 0 ? "BUY" : "BOUGHT";
}

// --- Fetch conversations ---
$stmt = $convinfo_conn->prepare("SELECT conv_id, conv_token, creater_username, user_username FROM convinfo WHERE creater_id = ? OR user_id = ?");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// --- Fetch servers ---
$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME != 'serverinfo'";
$result = $serverinfo_conn->query($query);

while ($row = $result->fetch_assoc()) {
    $table_name = $row['TABLE_NAME'];
    
    $stmt = $serverinfo_conn->prepare("SELECT * FROM `$table_name` WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        list($server_name, $server_token, $server_id) = explode('|', $table_name);
        
        $stmt_info = $serverinfo_conn->prepare("SELECT server_icon FROM serverinfo WHERE server_id = ?");
        $stmt_info->bind_param("i", $server_id);
        $stmt_info->execute();
        $server_info = $stmt_info->get_result()->fetch_assoc();
        
        $servers[] = [
            'server_name' => $server_name,
            'server_token' => $server_token,
            'server_id' => $server_id,
            'server_icon' => $server_info['server_icon'] ?? 'default_icon.png'
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <button class="logo" onclick="location.href='https://huychat.site'">
            <div class="img-container">
                <img alt="hcht" src="../assets/images/favicon.ico">
            </div>
        </button>
        <div class="profile">
            <button onclick="profileDropdown()" class="dropbtn"><?php echo $username ?></button>
                <div id="profileDropdown" class="dropdown-content">
                    <a href="../account/profile">Profile</a>
                    <a href="#">Settings</a>
                    <a href="../account/logout">Logout</a>
                </div>
        </div>
    </div>
    <script>
        // Toggle dropdown
    function profileDropdown() {
      document.getElementById("profileDropdown").classList.toggle("show");
    }

    // Close if user clicks outside
    window.onclick = function(event) {
      if (!event.target.matches('.dropbtn')) {
        let dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
          let openDropdown = dropdowns[i];
          if (openDropdown.classList.contains('show')) {
            openDropdown.classList.remove('show');
          }
        }
      }
    }
    </script>
</body>
</html>