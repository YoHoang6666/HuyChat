<?php
// Requires your existing DB connection
require 'weather_db_connect.php'; // must set $weather_conn (mysqli)

header('Content-Type: application/json; charset=utf-8');

// Decode JSON body
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success"=>false,"error"=>"Invalid JSON"]);
    exit;
}

$lat = (float)($data['latitude'] ?? 0);
$lon = (float)($data['longitude'] ?? 0);
$acc = isset($data['accuracy']) ? (float)$data['accuracy'] : null;
$ip  = $_SERVER['REMOTE_ADDR'] ?? "";

// Ensure table
$weather_conn->query("
CREATE TABLE IF NOT EXISTS locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  latitude DOUBLE NOT NULL,
  longitude DOUBLE NOT NULL,
  accuracy DOUBLE NULL,
  ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert
$stmt = $weather_conn->prepare(
  "INSERT INTO locations (latitude, longitude, accuracy, ip) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ddds", $lat, $lon, $acc, $ip);
if ($stmt->execute()) {
    echo json_encode(["success"=>true,"id"=>$stmt->insert_id]);
} else {
    echo json_encode(["success"=>false,"error"=>$stmt->error]);
}
