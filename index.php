<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
session_start();
define("CHARGE_AUTOLOAD", true);

require_once("inc/poo.inc.php");

if (isset($_GET) && (!empty($_GET))) {
    foreach ($_GET as $cle => $value) {
        switch ($cle) {

            case "inscription":
                $formulaire = new VueFormulaire();
                echo $formulaire;
                break;

            case "PDOTEST_traitement":
                $traitement = new VuePDOTEST_traitement();
                $traitement->ajouterUtilisateur();
                break;

            case "login":
                $login= new VueLogin();
                echo $login;
                break;
                
                
            case "forgot":
                $forgot = new VueMotDePasseOublie();
                echo $forgot;
                break;

            case "reset":
                // On s'assure que le token est présent dans l'URL
                if (isset($_GET['token']) && !empty($_GET['token'])) {
                    $reset = new VueReinitialiserMdp($_GET['token']);
                    echo $reset;
                } else {
                    echo "<p>Jeton manquant.</p>";
                }
                break;
            }
            
            
            
        
    }
} else {
    // Par défaut → page de login
    $login = new VueLogin();
    echo $login;
}
?>