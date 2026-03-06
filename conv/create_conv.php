<?php
session_start();
require 'config/convinfo_db_connect.php';
require 'config/conv_db_connect.php';
require 'config/userinfo_db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit();
}

$creater_id = $_SESSION['user_id'];
$creater_username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    if (empty($user_id)) {
        echo "<script>alert('❌ User ID cannot be empty.'); window.location.href='create_conv';</script>";
        exit();
    }

    $stmt = $convinfo_conn->prepare("
    SELECT COUNT(*) AS count FROM convinfo 
    WHERE (creater_id = ? AND user_id = ?) OR (creater_id = ? AND user_id = ?)
");
$stmt->bind_param("iiii", $creater_id, $user_id, $user_id, $creater_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['count'] > 0) {  
    echo "<script>alert('❌ Conversation already exists.'); window.location.href='create_conv.php';</script>";
    exit();
}


    // Get the user's username from the database
    $stmt = $userinfo_conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user_username = $row['username'];
    } else {
        echo "<script>alert('❌ User not found.'); window.location.href='create_conv';</script>";
        exit();
    }
    $stmt->close();

    // Generate a unique conversation token
    $conv_token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

    // Insert conversation into `convinfo`
    $stmt = $convinfo_conn->prepare("
        INSERT INTO convinfo (conv_token, creater_id, creater_username, user_id, user_username) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sisis", $conv_token, $creater_id, $creater_username, $user_id, $user_username);
    $stmt->execute();
    $stmt->close();

    // Get the auto-incremented conversation ID
    $conv_id = $convinfo_conn->insert_id;

    // Create a valid table name for conversation messages
    $conv_table = "`" . $conv_id . "|" . $conv_token . "`";

    // Create table for messages
    $create_conv_table_sql = "
        CREATE TABLE $conv_table (
            msg_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            file VARCHAR(255) DEFAULT NULL,
            time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            edited BOOLEAN DEFAULT FALSE
        )
    ";
    $conv_conn->query($create_conv_table_sql);

    echo "<script>alert('✅ Conversation created successfully.'); window.location.href='index';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a conversation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f9fafc; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, button { display: block; width: 100%; margin-bottom: 10px; padding: 8px; }
        button { background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Please insert the user ID of the person you want to message:</h2>
        <label>User ID</label>
        <input type="text" name="user_id" required>
        <button type="submit">Create Conversation</button>
    </form>
</body>
</html>
