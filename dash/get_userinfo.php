<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php-error.log");

session_start();
require 'config/userinfo_db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Session expired"]);
    exit();
}

$username = $_SESSION['username'];

$stmt = $userinfo_conn->prepare("SELECT email, image FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$default_image = "https://huychat.cloud/assets/images/account-default.svg";

$image_value = $row["image"];
$final_image = ($image_value === "0") ? $default_image : $image_value;

echo json_encode([
    "username" => $username,
    "email" => $row["email"],
    "image" => $final_image
]);
