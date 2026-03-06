<?php
session_start();
require 'config/serverinfo_db_connect.php';
require 'config/server_db_connect.php';
require 'config/userinfo_db_connect.php';
require 'config/config.php'

if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit();
}

$user_id = $_SESSION['user_id'];
$servers = [];

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

$selected_token = $_GET['tkn'] ?? null;
$messages = [];
$selected_server_name = '';
if ($selected_token) {
    foreach ($servers as $server) {
        if ($server['server_token'] === $selected_token) {
            $selected_server_name = $server['server_name'];
            $server_table = "`{$server['server_name']}|{$server['server_token']}|{$server['server_id']}`";
            
            $stmt = $server_conn->prepare("SELECT username, message, time FROM $server_table ORDER BY time ASC");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            $stmt->close();
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'fetch') {
        $selected_token = $_POST['token'];
        $messages = [];
        foreach ($servers as $server) {
            if ($server['server_token'] === $selected_token) {
                $server_table = "`{$server['server_name']}|{$server['server_token']}|{$server['server_id']}`";
                $stmt = $server_conn->prepare("SELECT username, message, time FROM $server_table ORDER BY time ASC");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='message'><b>{$row['username']}:</b> {$row['message']} <small>{$row['time']}</small></div>";
                }
                $stmt->close();
                exit;
            }
        }
    }

    if ($_POST['action'] === 'send') {
        $selected_token = $_POST['token'];
        $message = $_POST['message'];
        $username = $_SESSION['username'];

        foreach ($servers as $server) {
            if ($server['server_token'] === $selected_token) {
                $server_table = "`{$server['server_name']}|{$server['server_token']}|{$server['server_id']}`";
                $stmt = $server_conn->prepare("INSERT INTO $server_table (username, message, time) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $username, $message);
                $stmt->execute();
                $stmt->close();
                exit;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sidebar .server-icon { width: 30px; height: 30px; border-radius: 50%; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #222; color: #fff; }
        .chat-container { display: flex; flex-direction: column; height: calc(97vh - 50px); width: calc(100% - 220px); margin-left: 220px; border-radius: 0; overflow: hidden; background: #f8f8f8; }
        .chat-header { background: #007bff; color: white; padding: 15px; text-align: center; font-weight: bold; font-size: 18px; }
        .chat-box { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #333; color: white; }
        .message { max-width: 70%; padding: 10px; border-radius: 8px; word-wrap: break-word; }
        .message.user { align-self: flex-end; background: #007bff; color: white; }
        .message.other { align-self: flex-start; background: #e4e6eb; }
        .chat-input button:hover { background: #0056b3; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #222; color: #fff; }
        .sidebar { width: 200px; height: 100vh; background: #333; padding: 10px; position: fixed; left: 0; top: 0; }
        .sidebar h2 { text-align: center; }
        .sidebar button { padding: 10px; cursor: pointer; border-radius: 5px; display: flex; align-items: center; gap: 20px; width: 150px}
        .sidebar button:hover, .sidebar .selected { background: #222; }
        .top-bar { height: 50px; background: #111; padding: 10px; display: flex; align-items: center; justify-content: space-between; margin-left: 200px; }
        .dropdown { position: relative; }
        .dropdown-btn { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
        .dropdown-content { display: none; position: absolute; background: #333; min-width: 160px; z-index: 1; right: 0; }
        .dropdown-content a { color: #fff; padding: 10px; display: block; text-decoration: none; }
        .dropdown-content a:hover { background: #444; }
        .show { display: block; }
        .content { margin-left: 220px; padding: 20px; }
        .chat-box { max-height: calc(100% - 50px); overflow-y: auto; border: 1px solid #444; padding: 10px; background: #333; }
        .chat-input { display: flex; padding: 10px; background: #222; margin-bottom: 0px; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #444; background: #111; color: white; }
        .chat-input button { padding: 10px; background: #007bff; border: none; color: white; cursor: pointer; }
        .content { margin-left: 220px; padding: 0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Servers</h2>
        <?php foreach ($servers as $server): ?>
        <button href="javascript:void(0)" onclick="loadServer('<?= $server['server_token'] ?>')">         
            <img src="<?= $server['server_icon'] ?>" alt="Icon" class="server-icon">
            <?= htmlspecialchars($server['server_name']) ?>
        </button>
        <?php endforeach; ?>
        <?php if (empty($servers)): ?>
            <p>You don't have any servers yet.</p>
        <?php endif; ?>
    </div>

        <div class="top-bar">
        <div class="dropdown">
            <button class="dropdown-btn">☰</button>
            <div class="dropdown-content">
                <a href="#">Manage Members</a>
                <a href="#">Invite Members</a>
            </div>
        </div>
    </div>
    
    <div class="chat-container">
        <div class="chat-header" id="chat-header">Chat</div>
        <div class="chat-box" id="chat-box"></div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Type your message..." required>
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
// Ensure this is declared only once at the top
let selectedToken = "<?= $selected_token ?>";

function loadServer(token) {
    if (selectedToken === token) return; // Prevent reloading the same server
    selectedToken = token;
    
    $("#chat-header").text("Chat - " + token);
    $("#chat-box").html(""); // Clear previous messages
    fetchMessages();
}

function fetchMessages() {
    if (!selectedToken) return; // Avoid running if no server is selected
    
    $.post("index.php", { action: "fetch", token: selectedToken }, function(data) {
        $("#chat-box").html(data);
    }).fail(function() {
        console.error("Failed to fetch messages.");
    });
}

function sendMessage() {
    let message = $("#message-input").val();
    if (message.trim() === "") return;
    
    $.post("index.php", { action: "send", token: selectedToken, message: message }, function() {
        $("#message-input").val("");
        fetchMessages();
    }).fail(function() {
        console.error("Failed to send message.");
    });
}

// Ensure only one interval runs
let fetchInterval = setInterval(fetchMessages, 3000);

// Prevent multiple event listeners
$(document).off("click", ".chat-container").on("click", ".chat-container", function (e) {
    e.stopPropagation(); // Prevent duplicate triggering
});

    </script>
</body>
</html>
