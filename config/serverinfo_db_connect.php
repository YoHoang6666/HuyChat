<?php
$serverinfo_host = 'sql106.infinityfree.com';
$serverinfo_user = 'if0_37967376';
$serverinfo_password = 'Hoanggiahuy';
$serverinfo_database = 'if0_37967376_serverinfo';

$serverinfo_conn = new mysqli($serverinfo_host, $serverinfo_user, $serverinfo_password, $serverinfo_database);

// Check connection
if ($serverinfo_conn->connect_error) {
    die('❌ Connection failed to serverinfo database: ' . $serverinfo_conn->connect_error);
}
?>