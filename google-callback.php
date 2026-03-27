<?php
session_start();

require_once('config.php');

// CONFIG GOOGLE
$client_id     = GOOGLE_CLIENT_ID; // sécurisation de la clé API
$client_secret = GOOGLE_CLIENT_SECRET; // sécurisation du mot de passe du client
$redirect_uri  = GOOGLE_REDIRECT_URI;

// CONFIG BDD via config.php
$pdo = new PDO(
    "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Vérification code
if (!isset($_GET['code'])) {
    die("Erreur : code manquant !");
}

$code = $_GET['code'];

// Échanger code contre token
$token = file_get_contents("https://oauth2.googleapis.com/token", false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'content' => http_build_query([
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri,
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));

$data = json_decode($token, true);

if (!isset($data['access_token'])) {
    die("Erreur token Google");
}

// Récupérer infos utilisateur
$user  = file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $data['access_token']);
$user  = json_decode($user, true);
$email = $user['email'];
$name  = $user['name'];

// Vérifier si user existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existingUser) {
    $stmt = $pdo->prepare("
        INSERT INTO users 
        (username, display_name, email, password_hash, birth_date, created_at)
        VALUES 
        (:username, :display_name, :email, :password_hash, :birth_date, NOW())
    ");
    $stmt->execute([
        ':username'     => $email,
        ':display_name' => $name,
        ':email'        => $email,
        ':password_hash'=> '',
        ':birth_date'   => '2000-01-01'
    ]);
    $user_id = $pdo->lastInsertId();
} else {
    $user_id = $existingUser['id'];
}

// SESSION
$_SESSION['user_id']    = $user_id;
$_SESSION['user_email'] = $email;
$_SESSION['user_name']  = $name;

// REDIRECTION
header("Location: index.php?accueil");
exit();
?>