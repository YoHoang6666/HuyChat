<?php
session_start();
require 'config/serverinfo_db_connect.php';
require 'config/server_db_connect.php';
require 'config/config.php'
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server_name = trim($_POST['server_name']);
    if (empty($server_name)) {
        echo "<script>alert('❌ Server name cannot be empty.'); window.location.href='create_server.php';</script>";
        exit();
    }

    // Handle Server Icon Upload
    $icon_path = null;
    if (!empty($_FILES['server_icon']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $icon_filename = uniqid('icon_') . '_' . basename($_FILES['server_icon']['name']);
        $icon_path = $upload_dir . $icon_filename;

        if (!move_uploaded_file($_FILES['server_icon']['tmp_name'], $icon_path)) {
            echo "<script>alert('❌ Failed to upload server icon.'); window.location.href='create_server.php';</script>";
            exit();
        }
    }

    // Generate token
    $server_token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

    // Create server in serverinfo table
    $stmt = $serverinfo_conn->prepare("INSERT INTO serverinfo (server_name, server_token, server_icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $server_name, $server_token, $icon_path);
    $stmt->execute();
    $server_id = $stmt->insert_id; // Get the new server's ID
    $stmt->close();

    // Create a new table for messages in the server database
    $server_table = "`" . $server_name . "|" . $server_token . "|" . $server_id . "`";
    $create_server_table_sql = "
        CREATE TABLE $server_table (
            msg_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            username VARCHAR(255),
            message TEXT,
            file VARCHAR(255) DEFAULT NULL,
            time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            edited BOOLEAN DEFAULT FALSE
        )
    ";
    $server_conn->query($create_server_table_sql);

    // Create a new table for members in the server database
    $create_serverinfo_table_sql = "
        CREATE TABLE $server_table (
            user_id INT,
            username VARCHAR(255),
            tier VARCHAR(50),
            admin BOOLEAN DEFAULT FALSE
        )
    ";
    $serverinfo_conn->query($create_serverinfo_table_sql);

    // Add the creator as a member in the serverinfo table
    $stmt = $serverinfo_conn->prepare("
        INSERT INTO `$server_name|$server_token|$server_id` (user_id, username, tier, admin) 
        VALUES (?, ?, 'Owner', 1)
    ");
    $stmt->bind_param("is", $user_id, $username);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ Server created successfully.'); window.location.href='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Server</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f9fafc; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, button { display: block; width: 100%; margin-bottom: 10px; padding: 8px; }
        button { background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <h2>Create a New Server</h2>
        <label>Server Name:</label>
        <input type="text" name="server_name" required>
        
        <label>Server Icon (Not Required, but reommended):</label>
        <input type="file" name="server_icon" accept="image/*">

        <button type="submit">Create Server</button>
    </form>
</body>
</html>