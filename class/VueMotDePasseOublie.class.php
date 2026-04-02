<?php
require_once(__DIR__ . '/../config.php');

class VueMotDePasseOublie
{
    private $cnxDB;

    public function __construct()
    {
        $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function __toString()
    {
        $html = "";
        if (isset($_POST['submit_forgot'])) {
            $html .= $this->traiterDemande();
        }
        $html .= $this->afficheFormulaire();
        return $html;
    }

    private function afficheFormulaire()
    {
        return '
        <div class="forgot-container">
            <form action="" method="post" class="forgot-form">
                <h2>Mot de passe oublié</h2>
                <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
                <label for="emailField">Email :</label>
                <input type="email" id="emailField" name="email" required>
                <br/>
                <input type="submit" value="Envoyer le lien" name="submit_forgot">
            </form>
            <p><a href="index.php?login">Retour à la connexion</a></p>
        </div>
        ';
    }

    private function traiterDemande()
    {
        $email = htmlspecialchars(trim($_POST['email']));

        // 1. Vérifier si l'email existe dans la table users
        $stmt = $this->cnxDB->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            // 2. Générer un token unique et une date d'expiration (ex: dans 1 heure)
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // 3. Supprimer les anciens tokens de cet utilisateur (nettoyage)
            $del = $this->cnxDB->prepare("DELETE FROM password_resets WHERE email = :email");
            $del->execute([':email' => $email]);

            // 4. Insérer le nouveau token
            $ins = $this->cnxDB->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
            $ins->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expires_at
            ]);

            // 5. Envoyer l'email
            // Adapte l'URL avec ton vrai nom de domaine AlwaysData !
            $lien = "https://81.194.40.29/~avf/index.php?reset&token=" . $token;
            $sujet = "Réinitialisation de votre mot de passe - IOBC";
            $message = "Bonjour,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe : \n" . $lien . "\n\nCe lien est valable 1 heure.";
            $headers = "From: noreply@iobc.com";

            mail($email, $sujet, $message, $headers);
        }

        // Message générique pour des raisons de sécurité (on ne dit pas si l'email existe ou non)
        return "<p style='color:green;'>Si cet email existe, un lien de réinitialisation vous a été envoyé.</p>";
    }
}
?>