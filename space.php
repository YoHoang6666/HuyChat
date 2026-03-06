<?php
session_start();
require 'config/userinfo_db_connect.php';
require 'config/config.php';

// Check if user session exists
if (!isset($_SESSION['user_id'])) {
    // Check for 'remember_me' cookie
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];

        $stmt = $conn->prepare("SELECT id, username FROM users WHERE remember_token = ? AND remember_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        } else {
            // Invalid or expired token; clear the cookie
            setcookie('remember_me', '', time() - 3600, "/", "", true, true);
            header('Location: login');
            exit();
        }
    } else {
        // No session and no valid cookie, redirect to login
        header('Location: login');
        exit();
    }
}

// Ensure $user is set to avoid undefined errors in HTML
$user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuyChat Space</title>
    <link rel="stylesheet" href="chat.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #fff;
            display: flex;
            height: 100vh;
        }
        #sidebar {
            width: 300px;
            background: #2c3e50;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            overflow-y: auto;
        }
        #sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .chat-item {
            background: #34495e;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .chat-item:hover {
            background: #1abc9c;
        }
        .chat-item.unread::after {
            content: '•';
            color: #e74c3c;
            float: right;
        }
        #main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        #main-content h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        #main-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .chat-options {
            display: flex;
            gap: 20px;
        }
        .chat-options button {
            background: #6a11cb;
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .chat-options button:hover {
            background: #2575fc;
        }
        .toggle-global-chat {
            margin-top: 20px;
        }
        .toggle-global-chat button {
            background: #e67e22;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .toggle-global-chat button:hover {
            background: #d35400;
        }
        .profile-dropdown {
            position: absolute;
            top: 10px;
            right: 20px;
        }
        .profile-btn {
            background: none;
            border: none;
            cursor: pointer;
        }
        .profile-btn img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            background: #2c3e50;
            right: 0;
            margin-top: 5px;
            border-radius: 5px;
            overflow: hidden;
            z-index: 1000;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #fff;
        }
        .dropdown-menu a:hover {
            background: #1abc9c;
        }
        .dropdown-menu .upgrade {
            display: none;
        }
        .diamond-tier .dropdown-menu .upgrade {
            display: none !important;
        }
        .profile-dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <h2>Recent Chats</h2>
        <div class="chat-item unread">1-1 Chat (2)</div>
        <div class="chat-item">Server Chat</div>
        <div class="chat-item">Global Chat</div>
        <div class="toggle-global-chat">
            <button onclick="toggleGlobalChat()">Toggle Global Chat</button>
        </div>
    </div>
    <div id="main-content">
        <div>
            <h1>Welcome to HuyChat Space</h1>
            <p>Choose your chat area:</p>
            <div class="chat-options">
                <button onclick="window.location.href='conv/'">Conversation</button>
                <button onclick="window.location.href='server/'">Server Chat</button>
                <button onclick="window.location.href='globalchat/'">Global Chat</button>
            </div>
        </div>
    </div>
    <div class="profile-dropdown">
        <button class="profile-btn"><?= htmlspecialchars($_SESSION['username']); ?></button>
        <div class="dropdown-menu">
            <a href="profile">Profile</a>
            <a href="logout">Logout</a>
            <a href="upgrade.php" class="upgrade">Upgrade</a>
        </div>
    </div>
    <script>
        function toggleGlobalChat() {
            const globalChat = document.querySelector('.chat-item:nth-child(3)');
            if (globalChat.style.display === 'none') {
                globalChat.style.display = 'block';
            } else {
                globalChat.style.display = 'none';
            }
        }
    </script>
</body>
</html>
