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

// Fetch conversations
$stmt = $convinfo_conn->prepare("
    SELECT conv_id, conv_token, creater_username, user_username FROM convinfo 
    WHERE creater_id = ? OR user_id = ?
");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #222; color: #fff; }
        .sidebar { width: 200px; height: 100vh; background: #333; padding: 10px; position: fixed; left: 0; top: 0; }
        .sidebar h2 { text-align: center; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { padding: 10px; cursor: pointer; border-radius: 5px; display: flex; align-items: center; gap: 10px; }
        .sidebar li:hover, .sidebar .selected { background: #444; }
        .top-bar { height: 50px; background: #111; padding: 10px; display: flex; align-items: center; justify-content: space-between; margin-left: 200px; }
        .chat-container { margin-left: 220px; padding: 20px; }
        .chat-box { max-height: 400px; overflow-y: auto; border: 1px solid #444; padding: 10px; background: #333; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Conversations</h2>
        <?php foreach ($conversations as $conv) { ?>
            <button class="conv-btn" data-token="<?= htmlspecialchars($conv['conv_token']); ?>">
                <?= htmlspecialchars($conv['text_with']); ?>
            </button>
        <?php } ?>
    </div>

    <div class="top-bar">
        <span>Selected Token: <span id="selected-token">None</span></span>
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
        let selectedToken = null;

        $(document).ready(function() {
            $(".conv-btn").click(function() {
                selectedToken = $(this).data("token");
                $("#selected-token").text(selectedToken);
                console.log("Selected Token:", selectedToken);
            });
        });

        function sendMessage() {
            let message = $("#message-input").val();
            if (message.trim() === "" || !selectedToken) return;

            $.post("index.php", { action: "send", token: selectedToken, message: message }, function() {
                $("#message-input").val("");
            }).fail(function() {
                console.error("Failed to send message.");
            });
        }
    </script>
</body>
</html>
