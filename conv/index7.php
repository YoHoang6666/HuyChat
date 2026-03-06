<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'convinfo_db_connect.php';
require 'conv_db_connect.php';
require 'userinfo_db_connect.php';

if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(["error" => "Session expired"]);
        exit();
    }
    header('Location: login');
    exit();
}

$creater_id = $_SESSION['user_id'];
$creater_username = $_SESSION['username'];

// Fetch conversations
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if ($_POST['action'] === 'fetch') {
        $selected_token = $_POST['token'];
        $messages = [];
        foreach ($conversations as $conv) {
            if ($conv['conv_token'] === $selected_token) {
                $conv_table = "`{$conv['conv_id']}|{$conv['conv_token']}`";
                $stmt = $conv_conn->prepare("SELECT username, message, time FROM $conv_table ORDER BY time ASC");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $messages[] = [
                        "username" => $row['username'],
                        "message" => $row['message'],
                        "time" => $row['time']
                    ];
                }
                $stmt->close();
                echo json_encode($messages);
                exit;
            }
        }
        echo json_encode([]);
        exit;
    }

    if ($_POST['action'] === 'send') {
        $selected_token = $_POST['token'];
        $message = $_POST['message'];
        $username = $_SESSION['username'];

        foreach ($conversations as $conv) {
            if ($conv['conv_token'] === $selected_token) {
                $conv_table = "`{$conv['conv_id']}|{$conv['conv_token']}`";
                $stmt = $conv_conn->prepare("INSERT INTO $conv_table (username, message, time) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $creater_username, $message);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["success" => true]);
                exit;
            }
        }
        echo json_encode(["error" => "Conversation not found"]);
        exit;
    }
}
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
        .chat-container { display: flex; flex-direction: column; height: 97vh; width: 100%; background: #f8f8f8; }
        .chat-header { background: #007bff; color: white; padding: 15px; text-align: center; font-weight: bold; font-size: 18px; }
        .chat-box { flex: 1; padding: 15px; overflow-y: auto; background: #333; color: white; }
        .message { max-width: 70%; padding: 10px; border-radius: 8px; word-wrap: break-word; }
        .message.user { align-self: flex-end; background: #007bff; color: white; }
        .message.other { align-self: flex-start; background: #e4e6eb; }
        .chat-input { display: flex; padding: 10px; background: #222; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #444; background: #111; color: white; }
        .chat-input button { padding: 10px; background: #007bff; border: none; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header" id="chat-header">Chat</div>
        <div class="chat-box" id="chat-box"></div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Type your message..." required>
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <script>
        let selectedToken = localStorage.getItem("selectedToken") || null;

        function fetchMessages() {
            if (!selectedToken) return;
            $.post("index.php", { action: "fetch", token: selectedToken }, function(data) {
                $("#chat-box").html("");
                data.forEach(msg => {
                    let messageClass = msg.username === "<?= $creater_username ?>" ? "user" : "other";
                    $("#chat-box").append(`<div class='message ${messageClass}'><b>${msg.username}:</b> ${msg.message} <small>${msg.time}</small></div>`);
                });
            }, "json");
        }

        function sendMessage() {
            let message = $("#message-input").val();
            if (message.trim() === "" || !selectedToken) return;
            
            $.post("index.php", { action: "send", token: selectedToken, message: message }, function(response) {
                if (response.success) {
                    $("#message-input").val("");
                    fetchMessages();
                } else {
                    alert(response.error);
                }
            }, "json").fail(function() {
                console.error("Failed to send message.");
            });
        }
    </script>
</body>
</html>
