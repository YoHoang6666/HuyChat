<?php
$userinfo_host = "sql106.infinityfree.com"; // Check if this is your correct database server
$userinfo_user = "if0_37967376"; // Verify your database username
$userinfo_password = "Hoanggiahuy"; // Double-check your database password
$userinfo_database = "if0_37967376_userinfo"; // Verify your database name

// Create connection
$userinfo_conn = new mysqli($userinfo_host, $userinfo_user, $userinfo_password, $userinfo_database);
$conn = new mysqli($userinfo_host, $userinfo_user, $userinfo_password, $userinfo_database);

// Check connection
if ($userinfo_conn->connect_error) {
    die('❌ Connection failed to userinfo database: ' . $userinfo_conn->connect_error);
}
?>
