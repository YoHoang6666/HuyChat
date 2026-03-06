<?php
$convinfo_host = 'sql106.infinityfree.com';
$convinfo_user = 'if0_37967376';
$convinfo_password = 'Hoanggiahuy';
$convinfo_database = 'if0_37967376_convinfo';

$convinfo_conn = new mysqli($convinfo_host, $convinfo_user, $convinfo_password, $convinfo_database);

// Check connection
if ($convinfo_conn->connect_error) {
    die('❌ Connection failed to convinfo database: ' . $convinfo_conn->connect_error);
}
?>