<?php
session_start();
require_once('config.php');

$client_id    = GOOGLE_CLIENT_ID;
$redirect_uri = GOOGLE_REDIRECT_URI;

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => 'email profile',
]);

header("Location: $url");
exit();
?>