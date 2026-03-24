<?php
/*
class PDOTEST_traitement 
{
    const DB_HOST = 'a2425_raodson_12300671sae203';// Nom de la base de données
    const HOST = "localhost";// Nom de l'hôte
    const USER = "raodson";// Nom d'utilisateur
    const PASS = "4eab505";// Mot de passe(vide)
    const DSN = "mysql:host=".self::HOST.";dbname=".self::DB_HOST; // DSN de la base de données
<!--<
    protected $cnxDB; // Instance de la connexion PDO
    public function __construct() // Constructeur de la classe
    {
        // Connexion à la base de données
        try {
            $this->cnxDB = new PDO(self::DSN, self::USER, self::PASS);// Création de l'instance PDO
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// Mode d'erreur PDO
        } catch (PDOException $e) {// Erreur de connexion
            die ("Erreur de connexion : " . $e->getMessage());// Affichage de l'erreur
        }
    }

    public function __toString() { // Méthode magique pour afficher l'objet
        return 'PDOTEST_traitement instance';// Affichage de l'objet
    }

    public function ajouterUtilisateur()// Méthode pour ajouter un utilisateur
    {
        if (isset($_POST'ok')) { //si l'utilisateur a cliqué sur le bouton "ok"
            $pseudo = $_POST'pseudo'; // son prénom est enregistré dans la table utilsateur
            $prenom = $_POST'prenom';// son nom est enregistré dans la table utilsateur
            $nom = $_POST'nom';// son pseudo est enregistré dans la table utilsateur
            $mot_de_passe = password_hash($_POST'password', PASSWORD_DEFAULT);// son mot de passe est enregistré dans la table utilsateur
            $email = $_POST'email'; // son email est enregistré dans la table utilsateur

          
            try {
                $requete = $this->cnxDB->prepare("INSERT INTO Utilisateur (pseudo, prenom, nom, mot_de_passe, email) VALUES (:pseudo, :nom, :prenom, :mot_de_passe, :email)"); //préparation de la requête
                // Insertion des données dans la base de données
                $requete->execute(//
                    array(//
                        ':pseudo' => $pseudo,//
                        ':prenom' => $prenom,
                        ':nom' => $nom,
                        ':mot_de_passe' => $mot_de_passe,
                        ':email' => $email
                    )
                );
                echo '<h1>Inscription réussie</h1>';// Message de succès qui apparaitra une fois que les renseignement seront enregistré dans la base de données.
            } catch (PDOException $e) {
                die("Erreur lors de l'insertion : " . $e->getMessage()); //message d'erreur d'insertion
            }
        }
    }
}


*/ //ceci est un fichier de la SAE 203 pour traiter le PDO le fichier est correcte 
// il faudra le modifier en fonction de notre BD

