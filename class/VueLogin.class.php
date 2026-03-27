<?php
class VueLogin
{
    private $cnxDB;
 
    public function __construct()
    {
        $dsn  = "mysql:host=mysql-digbeu.alwaysdata.net;dbname=digbeu_iobc;charset=utf8mb4";
        $user = "";//utilse ton mot de passe
        $pass = ""; // ← remplace par ton vrai mot de passe
 
        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
 
    public function __toString()
    {
        return $this->afficheFormulaire();
    }
 
    private function afficheFormulaire()
    {
        // Si le formulaire a été soumis, on traite les données
        if (isset($_POST['login'])) {
            $this->traiterDonnees();
        }
 
        return '
        <div class="login-container">
            <form action="" method="post" class="login-form">
                <h2>Connexion</h2>
 
                <label for="identifiantField">Pseudo ou Email :</label>
                <input type="text" id="identifiantField" name="identifiant"
                       placeholder="Votre pseudo ou adresse email" required>
                <br/>
 
                <label for="passwordField">Mot de passe :</label>
                <input type="password" id="passwordField" name="password"
                       placeholder="Votre mot de passe" required>
                <br/>
 
                <input type="submit" value="Se connecter" name="login">
            </form>
            <p>Pas de compte ? <a href="index.php?inscription">Inscrivez-vous ici</a></p>
        </div>
        <hr>
<p>Ou</p>

<a href="google-login.php" style="
display:inline-block;
padding:10px;
background:#fff;
border:1px solid #ccc;
text-decoration:none;
">
    Continuer avec Google
</a>
        ';
    }
 
    private function traiterDonnees()
    {
        $identifiant = htmlspecialchars(trim($_POST['identifiant'])); // pseudo ou email
        $password    = $_POST['password'];
 
        // Vérification que les champs ne sont pas vides
        if (empty($identifiant) || empty($password)) {
            echo "<p>Tous les champs sont obligatoires.</p>";
            return;
        }
 
        try {
            // Recherche par username OU par email
            $stmt = $this->cnxDB->prepare("
                SELECT id, username, password_hash 
                FROM users 
                WHERE username = :identifiant 
                OR email = :identifiant
            ");
            $stmt->execute([':identifiant' => $identifiant]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
            if ($user) {
                // Vérification du mot de passe hashé
                if (password_verify($password, $user['password_hash'])) {
                    // Stockage en session
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: index.php?accueil");
                    exit();
                } else {
                    echo "<p>Mot de passe incorrect.</p>";
                }
            } else {
                echo "<p>Pseudo ou email introuvable.</p>";
            }
 
        } catch (PDOException $e) {
            die("Erreur lors de la connexion : " . $e->getMessage());
        }
    }
}
?>