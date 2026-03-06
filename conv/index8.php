<?php
session_start();
require 'conv_db_connect.php';
require 'config/config.php'

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$conv_id = $_GET['conv_id'] ?? null;
$conv_token = $_GET['conv_token'] ?? null;
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
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
    }

    // Compose table name
    $table_name = "`" . $conv_token . "|" . $conv_id . "`";

    // Prepare insert query
    $stmt = $conv_conn->prepare("INSERT INTO $table_name (user_id, username, message, file) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $message, $file_name);
    $stmt->execute();
    $stmt->close();
}

// Fetch messages
$messages = [];
if ($conv_id && $conv_token) {
    $table_name = "`" . $conv_token . "|" . $conv_id . "`";
    $result = $conv_conn->query("SELECT * FROM $table_name ORDER BY time ASC");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conversation</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f1f1f1; }
        .chat-box { background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; }
        .message { margin-bottom: 15px; }
        .message span { font-weight: bold; }
        .message time { font-size: 0.8em; color: gray; }
        form { margin-top: 20px; }
        input, textarea, button { width: 100%; margin-bottom: 10px; padding: 10px; }
    </style>
</head>
<body>
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

        <form method="POST" enctype="multipart/form-data">
            <textarea name="message" placeholder="Write a message..." required></textarea>
            <input type="file" name="file">
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
