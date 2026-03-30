<?php
// VueAcceuilDeconnecte.class.php
// Page d'accueil pour les visiteurs NON connectés

class VueAcceuilConnecte
{
    
    public function __construct()
    {
        // Constructeur de la classe VueAccueilConnecte
    }
 
    public function __toString()
    {
        return '
        <h1>Bienvenue sur notre site !</h1>
        <p>Vous êtes connecté en tant que ' . htmlspecialchars($_SESSION['username']) . '.</p>
        <p><a href="index.php?deconnexion">Se déconnecter</a></p>';
}
    }    ?>  
