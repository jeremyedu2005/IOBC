<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
session_start();

define("CHARGE_AUTOLOAD", true);

// Load autoloader
require_once __DIR__ . '/inc/poo.inc.php';

// 🔒 SECURITY SETUP
Security::setupSecureSession();
Security::setSecurityHeaders();

// Multilingue : inclure la gestion des langues
require_once __DIR__ . '/lang/lang.php';
require_once __DIR__ . '/lang/translate.php';
$lang = getCurrentLang();
$tr = loadLang($lang);

// 🔹 Ensure uploads directory exists with proper permissions
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
// Ensure directory is writable
if (!is_writable($uploadDir)) {
    @chmod($uploadDir, 0755);
}

// 🔹 Connexion PDO - utilisée par tous les Vue classes
require_once("config.php");
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

// 🔹 Traitement du login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $login = new VueLogin();
    echo $login;
    exit;
}

// 🔹 Gestion des actions GET
if (!empty($_GET)) {
    foreach ($_GET as $cle => $value) {
        switch ($cle) {

            case "inscription":
                echo new VueFormulaire();
                break;

            case "PDOTEST_traitement":
                $traitement = new VuePDOTEST_traitement();
                $traitement->ajouterUtilisateur();
                break;

            case "login":
                echo new VueLogin();
                break;

            case "profile":
            case "profil":
                if (isset($_SESSION['user_id'])) {
                    echo new VueProfilPersonnel();
                } else {
                    $username = $_GET['username'] ?? $_GET['user'] ?? null;
                    if ($username) {
                        echo new VueProfilPublic($username);
                    } else {
                        echo "<p style='padding:50px; text-align:center; color:#666;'>Veuillez spécifier un utilisateur.</p>";
                    }
                }
                break;

            case "modifprofil":
                echo new VueModifProfil();
                break;

            case "agenda":
                echo new VueAgenda();
                break;

            case "agenda_events":
                $agenda = new VueAgenda();
                echo $agenda->getEventsJson();
                exit; // On arrête tout pour ne renvoyer que le JSON
                break;

            case "forgot":
                echo new VueMotDePasseOublie();
                break;

            case "reset":
                if (!empty($_GET['token'])) {
                    echo new VueReinitialiserMdp($_GET['token']);
                } else {
                    echo "<p>Jeton manquant.</p>";
                }
                break;
            
            case "communautes":
                echo new VueCommunautes();
                break;

            case "accueil":
                if (isset($_SESSION['user_id'])) {
                    echo new VueAccueilConnecte();
                } else {
                    echo new VueAcceuilDeconnecte();
                }
                break;
            
            case "creer-post":
                echo new VueCreerPost();
                break;

            case "messages":
                if (isset($_SESSION['user_id'])) {
                    echo new VueInbox($pdo, $_SESSION['user_id']);
                } else {
                    echo new VueLogin();
                }
                break;

            case "conversation":
                if (isset($_SESSION['user_id'])) {
                    echo new VueConversation($pdo, $_SESSION['user_id']);
                } else {
                    echo new VueLogin();
                }
                break;

            case "followers":
                $followers_view = new VueFollowers($pdo, $_SESSION['user_id'] ?? null);
                echo $followers_view;
                break;

            case "following":
                $following_view = new VueFollowing($pdo, $_SESSION['user_id'] ?? null);
                echo $following_view;
                break;

            case "compose":
                if (isset($_SESSION['user_id'])) {
                    echo new VueComposer($pdo, $_SESSION['user_id']);
                } else {
                    echo new VueLogin();
                }
                break;

            case "discover":
                if (isset($_SESSION['user_id'])) {
                    echo new VueDiscover();
                } else {
                    echo new VueLogin();
                }
                break;

            case "saved":
                if (isset($_SESSION['user_id'])) {
                    echo new VueSavedPosts();
                } else {
                    echo new VueLogin();
                }
                break;

            case "carte":
                echo new VueCarteInteractive();
                break;

            case "post":
                $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                if ($post_id > 0) {
                    echo new VuePostDetail($post_id);
                } else {
                    echo "<p style='text-align:center; padding:50px;'>Post ID missing</p>";
                }
                break;

            case "chat":
                if (isset($_GET['id'])) {
                    echo new VueCommunautesChat($_GET['id']);
                } else {
                    echo "<p>ID de communauté manquant.</p>";
                }
                break;

            
            case "chat_messages":
                if (isset($_GET['id'])) {
                    $chat = new VueCommunautesChat($_GET['id']);
                    // On n'affiche QUE les messages, sans le HTML autour
                    echo $chat->afficherMessagesUniquement(); 
                }
                exit; // Très important : on arrête PHP ici pour ne renvoyer que le texte des messages
                break;
            
            case "deconnexion":
            case "logout":
                session_destroy();
                header("Location: index.php?accueil");
                exit;

            default:
                echo new VueAcceuilDeconnecte();
        }
        break; // On sort après la première action trouvée
    }
} else {
    if (isset($_SESSION['user_id'])) {
        echo new VueAccueilConnecte();
    } else {
        echo new VueAcceuilDeconnecte();
    }
}
?>