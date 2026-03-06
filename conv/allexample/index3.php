<?php
session_start();
require 'convinfo_db_connect.php';
require 'conv_db_connect.php';
require 'userinfo_db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$creater_id = $_SESSION['user_id'];
$creater_username = $_SESSION['username'];

$stmt = $convinfo_conn->prepare("SELECT conv_id, conv_token, creater_username, user_username FROM convinfo WHERE creater_id = ? OR user_id = ?");
$stmt->bind_param("ii", $creater_id, $creater_id);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = [
        'conv_id' => $row['conv_id'],
        'conv_token' => $row['conv_token'],
        'text_with' => ($row['creater_username'] != $creater_username) ? $row['creater_username'] : $row['user_username']
    ];
}
$stmt->close();

if (isset($_GET['setToken'])) {
    if (session_status() === PHP_SESSION_NONE) { // Prevent duplicate session_start()
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        header('Content-Type: application/json');
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    $_SESSION['selectedToken'] = $_GET['setToken'];

    header('Content-Type: application/json');
    echo json_encode(["selectedToken" => $_SESSION['selectedToken']]);
    exit;
}


$selectedToken = $_SESSION['selectedToken'] ?? "None";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 50px); /* Fills the space below top-bar */
    width: calc(100% - 220px); /* Adjusts for sidebar width */
    margin-left: 220px;
    border-radius: 0;
    overflow: hidden;
    background: #f8f8f8;
}

.chat-header {
    background: #007bff;
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: bold;
    font-size: 18px;
}

.chat-box {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #333;
    color: white;
}
        .message { max-width: 70%; padding: 10px; border-radius: 8px; word-wrap: break-word; }
        .message.user { align-self: flex-end; background: #007bff; color: white; }
        .message.other { align-self: flex-start; background: #e4e6eb; }
.chat-input button:hover {
    background: #0056b3;
}
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #222; color: #fff; }
        .sidebar { width: 200px; height: 100vh; background: #333; padding: 10px; position: fixed; left: 0; top: 0; }
        .sidebar h2 { text-align: center; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { padding: 10px; cursor: pointer; border-radius: 5px; display: flex; align-items: center; gap: 10px; }
        .sidebar li:hover, .sidebar .selected { background: #444; }
        .top-bar { height: 50px; background: #111; padding: 10px; display: flex; align-items: center; justify-content: space-between; margin-left: 200px; }
        .dropdown { position: relative; }
        .dropdown-btn { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
        .dropdown-content { display: none; position: absolute; background: #333; min-width: 160px; z-index: 1; right: 0; }
        .dropdown-content a { color: #fff; padding: 10px; display: block; text-decoration: none; }
        .dropdown-content a:hover { background: #444; }
        .show { display: block; }
        .content { margin-left: 220px; padding: 20px; }
        .chat-box { max-height: 400px; overflow-y: auto; border: 1px solid #444; padding: 10px; background: #333; }
.chat-input {
    display: flex;
    padding: 10px;
    background: #222;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border: 1px solid #444;
    background: #111;
    color: white;
}
.chat-input button {
    padding: 10px;
    background: #007bff;
    border: none;
    color: white;
    cursor: pointer;
}
.content {
    margin-left: 220px;
    padding: 0;
}
    </style>
</head>
<body>
    <div class="sidebar">
        <p><b id="selectedToken"><?= htmlspecialchars($selectedToken); ?></b></p>
        <?php foreach ($conversations as $conv) { ?>
            <a href="javascript:void(0)" onclick="setToken('<?= $conv['conv_id']; ?>')">
                <?= htmlspecialchars($conv['text_with']); ?>
            </a><br>
        <?php } ?>
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
        function setToken(token) {
            fetch('index.php?setToken=' + token)
            .then(response => response.json())
            .then(data => {
                let selectedToken = document.getElementById("selectedToken");
                if (selectedToken) {
                    selectedToken.innerText = data.selectedToken;
                }
            })
            .catch(error => console.error("Error:", error));
        }
        
        // Ensure this is declared only once at the top

function fetchMessages() {
    if (!selectedToken) return; // Avoid running if no conv is selected
    
    $.post("index.php", { action: "fetch", token: selectedToken }, function(data) {
        $("#chat-box").html(data);
    }).fail(function() {
        console.error("Failed to fetch messages.");
    });
}

function sendMessage() {
    let message = $("#message-input").val();
    if (message.trim() === "") return;
    
    $.post("index3.php", { action: "send", token: selectedToken, message: message }, function() {
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
