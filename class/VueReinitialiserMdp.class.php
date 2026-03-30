<?php
class VueReinitialiserMdp
{
    private $cnxDB;
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
        // Tes identifiants actuels
        $dsn  = "mysql:host=mysql-digbeu.alwaysdata.net;dbname=digbeu_iobc;charset=utf8mb4";
        $user = "digbeu_jeremy";
        $pass = "toto123&*";

        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function __toString()
    {
        // 1. Vérifier si le token est valide
        $stmt = $this->cnxDB->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW()");
        $stmt->execute([':token' => $this->token]);
        $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetRequest) {
            return "<div style='text-align:center; margin-top:50px;'><p style='color:red;'>Ce lien est invalide ou a expiré.</p><a href='index.php?forgot'>Demander un nouveau lien</a></div>";
        }

        $html = "";
        if (isset($_POST['submit_reset'])) {
            $html .= $this->traiterReinitialisation($resetRequest['email']);
        } else {
            $html .= $this->afficheFormulaire();
        }
        return $html;
    }

    private function afficheFormulaire()
    {
        return '
        <div class="reset-container" style="max-width: 400px; margin: 50px auto; font-family: Arial;">
            <form action="" method="post" class="reset-form">
                <h2>Nouveau mot de passe</h2>
                <label for="newPasswordField">Nouveau mot de passe :</label><br>
                <input type="password" id="newPasswordField" name="new_password" required style="width: 100%; padding: 8px; margin: 10px 0;">
                <br/>
                <input type="submit" value="Mettre à jour" name="submit_reset" style="padding: 10px 15px; cursor: pointer;">
            </form>
        </div>
        ';
    }

    private function traiterReinitialisation($email)
    {
        $new_password = $_POST['new_password'];
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        try {
            // Mise à jour du mot de passe
            $update = $this->cnxDB->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
            $update->execute([
                ':hash' => $password_hash,
                ':email' => $email
            ]);

            // Supprimer le token utilisé
            $del = $this->cnxDB->prepare("DELETE FROM password_resets WHERE email = :email");
            $del->execute([':email' => $email]);

            return "<div style='text-align:center; margin-top:50px;'><p style='color:green;'>Votre mot de passe a été mis à jour avec succès !</p><br><a href='index.php?login'>Se connecter</a></div>";

        } catch (PDOException $e) {
            return "<p>Erreur lors de la mise à jour : " . $e->getMessage() . "</p>";
        }
    }
}
?>