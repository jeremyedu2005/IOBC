<?php
session_start();

$client_id = "TON_CLIENT_ID";
$client_secret = "TON_SECRET";
$redirect_uri = "https://digbeu.alwaysdata.net/IOBC/google-callback.php";

$code = $_GET['code'];

// récupérer token
$token = file_get_contents("https://oauth2.googleapis.com/token", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded",
        'content' => http_build_query([
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
        ]),
    ],
]));

$data = json_decode($token, true);

// récupérer infos utilisateur
$user = file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $data['access_token']);
$user = json_decode($user, true);

// 🔥 IMPORTANT : connexion
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name']  = $user['name'];

// 👉 redirection
header("Location: index.php?accueil");
exit();