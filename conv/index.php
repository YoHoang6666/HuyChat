<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'config/convinfo_db_connect.php';
require 'config/conv_db_connect.php';
require 'config/userinfo_db_connect.php';
require 'config/config.php'

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
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    if ($_POST['message'] === 'send') {
    $message = trim($_POST['message']);

    $file_name = null;

    // Handle file upload
    if (!empty($_FILES['file']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // File uploaded successfully
        } else {
            $file_name = null; // Reset if upload fails
        }

    // Compose table name
    $table_name = "`" . $conv_token . "|" . $conv_id . "`";

    // Prepare insert query
    $stmt = $conv_conn->prepare("INSERT INTO $table_name (user_id, username, message, file) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $creater_id, $creater_username, $message, $file_name);
    $stmt->execute();
    $stmt->close();
}
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
        .chat-box { max-height: calc(100% - 50px); overflow-y: auto; border: 1px solid #444; padding: 10px; background: #333; }
        .chat-input { display: flex; padding: 10px; background: #222; margin-bottom: 0px; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #444; background: #111; color: white; }
        .chat-input button { padding: 10px; background: #007bff; border: none; color: white; cursor: pointer; }
        .chat-input .file { padding: 10px; }
        .content { margin-left: 220px; padding: 0; }
        .conv-btn { background: white; color: #007bff; border: 2px solid #007bff; border-radius: 10px; padding: 10px 15px; font-size: 16px; cursor: pointer; display: block; width: 100%; text-align: left; transition: background 0.3s, color 0.3s; }
        .conv-btn:hover { background: #e6f0ff; }
        .conv-btn.active { background: #007bff; color: white; }

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
        <div class="dropdown">
            <button class="dropdown-btn">☰</button>
            <div class="dropdown-content">
                <a href="#">Manage Members</a>
                <a href="#">Invite Members</a>
            </div>
        </div>
    </div>
    <div class="chat-box">
        <h2>Conversation</h2>

        <?php foreach ($messages as $msg): ?>
            <div class="message">
                <span><?= htmlspecialchars($msg['username']) ?>:</span>
                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <?php if ($msg['file']): ?>
                    <a href="../uploads/<?= htmlspecialchars($msg['file']) ?>" target="_blank">📎 Attachment</a>
                <?php endif; ?>
                <time><?= $msg['time'] ?></time>
            </div>
        <?php endforeach; ?>
        <div class="chat-container">
        <div class="chat-header" id="chat-header">Chat</div>
        <div class="chat-box" id="chat-box">
        <?php foreach ($messages as $msg): ?>
            <div class="message">
                <span><?= htmlspecialchars($msg['username']) ?>:</span>
                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <?php if ($msg['file']): ?>
                    <a href="../uploads/<?= htmlspecialchars($msg['file']) ?>" target="_blank">📎 Attachment</a>
                <?php endif; ?>
                <time><?= $msg['time'] ?></time>
            </div>
        <?php endforeach; ?>
        </div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Type your message..." required>
            <button>
                <input type="file" class="file" name="file">
            </button>
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <script>
        let selectedToken = localStorage.getItem("selectedToken") || null;

        $(document).ready(function() {
            $(".conv-btn").click(function() {
                $(".conv-btn").removeClass("active"); // Remove active class from all
                $(this).addClass("active"); // Add active class to the clicked button

                selectedToken = $(this).data("token");
                localStorage.setItem("selectedToken", selectedToken);
                fetchMessages();
            });

            // Restore the selected conversation after reload
            let savedToken = localStorage.getItem("selectedToken");
            if (savedToken) {
                $(".conv-btn").each(function() {
                    if ($(this).data("token") === savedToken) {
                        $(this).addClass("active");
                    }
                });
                fetchMessages();
            }
        });


        function fetchMessages() {
            if (!selectedToken) return;
            $.post("index.php", { action: "fetch", token: selectedToken }, function(data) {
                $("#chat-box").html(data);
            });
        }

        $(document).ready(function() {
            $(".conv-btn").click(function() {
                selectedToken = $(this).data("token");
                localStorage.setItem("selectedToken", selectedToken);
                $("#selected-token").text(selectedToken);
                fetchMessages();
            });

            if (selectedToken) {
                $("#selected-token").text(selectedToken);
                fetchMessages();
            }
        });

        function sendSelectedTokenToPhp() {
            if (!selectedToken) return;
            $.post("index.php", { token: selectedToken }, function(response) {
                console.log(response);
            });
        }

        function sendMessage() {
            sendSelectedTokenToPhp()
            let message = $("#message-input").val();
            if (message.trim() === "" || !selectedToken) return;
        
            $.post("index.php", { action: "send", token: selectedToken, message: message }, function(response) {
                try {
                    let jsonResponse = JSON.parse(response);
                    if (jsonResponse.error) {
                        alert(jsonResponse.error);
                    } else {
                        $("#message-input").val(""); // Clear input field after sending
                        fetchMessages(); // Refresh chat after sending
                    }
                } catch (e) {
                    console.error("Invalid response:", response);
                }
            }).fail(function() {
                console.error("Failed to send message.");
            });
        }


    </script>
</body>
</html>