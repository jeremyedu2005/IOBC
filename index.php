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
            }
            
            
            
        
    }
} else {
    echo '<a href="index.php?inscription">S\'inscrire</a>';
}
?>

