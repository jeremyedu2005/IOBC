<?php
class VuePDOTEST_traitement
{
    const DB_HOST = 'digbeu_iobc';                   // Nom de la base de données
    const HOST    = 'mysql-digbeu.alwaysdata.net';                    // Hôte AlwaysData
    const USER    = 'digbeu_jeremy';                 // Nom d'utilisateur
    const PASS    = 'toto123&*';                     // Mot de passe AlwaysData
    const DSN     = "mysql:host=".self::HOST.";dbname=".self::DB_HOST.";charset=utf8mb4";
 
    protected $cnxDB;
 
    public function __construct()
    {
        try {
            $this->cnxDB = new PDO(self::DSN, self::USER, self::PASS);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
 
    public function __toString()
    {
        return 'VuePDOTEST_traitement instance';
    }
 
    public function ajouterUtilisateur()
    {
        if (isset($_POST['ok'])) { // Si le bouton "S'inscrire" a été cliqué
 
            // Récupération et sécurisation des données du formulaire
            $username     = htmlspecialchars(trim($_POST['username']));
            $display_name = htmlspecialchars(trim($_POST['display_name']));
            $email        = htmlspecialchars(trim($_POST['email']));
            $password     = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashage du mot de passe
            $birth_date   = $_POST['birth_date'];
 
            // Vérification que les champs ne sont pas vides
            if (empty($username) || empty($display_name) || empty($email) || empty($birth_date)) {
                echo "<p>Tous les champs sont obligatoires.</p>";
                return;
            }
 
            try {
                // Préparation de la requête INSERT adaptée à la table users
                $requete = $this->cnxDB->prepare("
                    INSERT INTO users (username, display_name, email, password_hash, birth_date)
                    VALUES (:username, :display_name, :email, :password_hash, :birth_date)
                ");
 
                // Exécution avec les données récupérées
                $requete->execute([
                    ':username'      => $username,
                    ':display_name'  => $display_name,
                    ':email'         => $email,
                    ':password_hash' => $password,
                    ':birth_date'    => $birth_date
                ]);
 
                // Redirection vers la page de connexion après inscription réussie
                header("Location: index.php?login");
                exit();
 
            } catch (PDOException $e) {
                die("Erreur lors de l'inscription : " . $e->getMessage());
            }
        }
    }
}

