<?php
$conv_host = 'sql106.infinityfree.com';
$conv_user = 'if0_37967376';
$conv_password = 'Hoanggiahuy';
$conv_database = 'if0_37967376_conv';

$conv_conn = new mysqli($conv_host, $conv_user, $conv_password, $conv_database);

// Check connection
if ($conv_conn->connect_error) {
    die('❌ Connection failed to conv database: ' . $conv_conn->connect_error);
}
?>