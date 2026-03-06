<?php
$server_host = 'sql106.infinityfree.com';
$server_user = 'if0_37967376';
$server_password = 'Hoanggiahuy';
$server_database = 'if0_37967376_server';

$server_conn = new mysqli($server_host, $server_user, $server_password, $server_database);

// Check connection
if ($server_conn->connect_error) {
    die('❌ Connection failed to server database: ' . $server_conn->connect_error);
}
?>