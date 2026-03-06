<?php
$weather_host = "sql106.infinityfree.com"; // Check if this is your correct database server
$weather_user = "if0_37967376"; // Verify your database username
$weather_password = "Hoanggiahuy"; // Double-check your database password
$weather_database = "if0_37967376_weather"; // Verify your database name

// Create connection
$weather_conn = new mysqli($weather_host, $weather_user, $weather_password, $weather_database);
$conn = new mysqli($weather_host, $weather_user, $weather_password, $weather_database);

// Check connection
if ($weather_conn->connect_error) {
    die('❌ Connection failed to weather database: ' . $weather_conn->connect_error);
}
?>
