<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
session_start();
define("CHARGE_AUTOLOAD", true);

require_once("inc/poo.inc.php");

// 🔹 Traitement du formulaire de login en POST (prioritaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
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
                
            case "accueil":
                // Page d'accueil : déconnecté OU connecté
                if (isset($_SESSION['user_id'])) {
                    echo new VueAccueilConnecte($_SESSION['user_id']);
                } else {
                    echo new VueAcceuilDeconnecte();
                }
                break;
                
            case "carte":
                $carte = new VueCarteInteractive();
                echo $carte;
                break;
                
            default:
                // Paramètre inconnu → on retourne à l'accueil déconnecté
                echo new VueAcceuilDeconnecte();
        }
    }
} else {
    // 🔹🔹🔹 PAGE PAR DÉFAUT : VueAcceuilDeconnecte 🔹🔹🔹
    echo new VueAcceuilDeconnecte();
}
?>