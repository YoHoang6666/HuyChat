<?php
// weather.php
require '../config/weather_db_connect.php';
header('Content-Type: application/json');

// Get user IP
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Get location from IP using ipapi.co
$locationJson = file_get_contents("https://ipapi.co/{$ip}/json/");
$locationData = json_decode($locationJson, true);

$city = $locationData['city'] ?? 'Unknown';
$country = $locationData['country_name'] ?? 'Unknown';

// Get weather from OpenWeatherMap
$apiKey = 'YOUR_OPENWEATHERMAP_API_KEY';
$weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric";

$weatherResponse = file_get_contents($weatherUrl);
$weatherData = json_decode($weatherResponse, true);
$weather = $weatherData['weather'][0]['description'] ?? 'Unavailable';

// Store in DB
$stmt = $weather_conn->prepare("INSERT INTO weather_logs (ip, city, country, weather_description) VALUES (?, ?, ?, ?)");
$stmt->execute([$ip, $city, $country, $weather]);

// Return response
echo json_encode([
  'ip' => $ip,
  'city' => $city,
  'country' => $country,
  'weather' => $weather
]);
