<?php
session_start();

$client_id = "TON_CLIENT_ID";
$redirect_uri = "https://digbeu.alwaysdata.net/IOBC/google-callback.php";

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'email profile',
]);

header("Location: $url");
exit();